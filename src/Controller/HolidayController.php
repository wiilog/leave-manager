<?php


namespace App\Controller;

use App\Entity\Holiday;
use App\Entity\Statut;
use App\Entity\User;
use App\Entity\Access;
use App\Repository\HolidayRepository;
use App\Repository\StatutRepository;
use App\Repository\UserRepository;
use App\Service\AccessService;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class HolidayController
 * @package App\Controller
 * @Route("/conge")
 */
class HolidayController extends AbstractController
{
    /**
     * @var HolidayRepository
     */
    private $holidayRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var AccessService
     */
    private $accessService;

    /**
     * @var StatutRepository
     */
    private $statutRepository;

    /**
     * @var MailerService
     */
    private $mailerService;

    /**
     * HolidayController constructor.
     * @param AccessService $accessService
     * @param HolidayRepository $holidayRepository
     * @param UserRepository $userRepository
     * @param StatutRepository $statutRepository
     */
    public function __construct(EntityManagerInterface $manager, AccessService $accessService, MailerService $mailerService)
    {
        $this->accessService = $accessService;
        $this->mailerService = $mailerService;
        $this->holidayRepository = $manager->getRepository(Holiday::class);
        $this->userRepository = $manager->getRepository(User::class);
        $this->statutRepository = $manager->getRepository(Statut::class);
    }

    /**
     * @Route("/", name="list_holiday", options={"expose"=true}, methods="GET|POST")
     */
    public function listHoliday()
    {
        $holidays = $this->holidayRepository->findAll();
        $users = $this->userRepository->findAll();
        $currentUser = $this->getUser();
        /** @var User $currentUser */

        return $this->render("holiday/listHoliday.html.twig",
            [
                'holidays' => $holidays,
                'cpCurrentUser' => $currentUser->getCP(),
                'rttCurrentUser' => $currentUser->getRTT() ?? 0,
                'idCurrentUser' => $currentUser->getId(),
                'statuts' => $this->statutRepository->getByCategorie(Holiday::CATEGORIE),
                'users' => $users,
            ]);
    }

    /**
     * @Route("/ajoute", name="add_holiday", options={"expose"=true}, methods="GET|POST")
     */
    public function addHoliday(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        if($data['firstDate'] !== '' && $this->checkHoliday($data)) {
            $holiday = new Holiday();
			$newFirstDate = \DateTime::createFromFormat('Y-m-d', $data['firstDate']);
			$newSecondDate = \DateTime::createFromFormat('Y-m-d', $data['secondDate'] !== '' ? $data['secondDate'] : $data['firstDate']);

            $holiday
				->setSs($data['ss'] !== '' ? max(0, $data['ss']) : '0')
                ->setCp($data['cp'] !== '' ? max(0, $data['cp']) : '0')
                ->setRtt($data['rtt'] !== '' ? max(0, $data['rtt']) : '0')
                ->setStartDate($newFirstDate)
                ->setEndDate($newSecondDate)
                ->setStatus(Holiday::STATUS_A_VALIDER)
                ->setRequestDate(new \DateTime())
                ->setDescription($data['description'])
                ->setRequester($this->getUser());

            /**
             * @var User $user
             */
            $user = $this->getUser();

            $user->incrementStockHolidays($holiday);

            $em = $this->getDoctrine()->getManager();
            $em->persist($holiday);
            $em->flush();

            $valideur = $holiday->getRequester()->getValidators();
            foreach ($valideur as $valid) {
                $this->mailerService->sendMail(
                    'CONGÉS // Nouvelle demande !',
                    $this->renderView('mails/demandeHoliday.html.twig', [
                        'conge' => $holiday
                    ]),
                   $valid->getEmail()
                );
            }

            return new JsonResponse(['cp' => $user->getCP(), 'rtt' => $user->getRTT() ?? 0]);
        }
        return new JsonResponse();
    }

    /**
     * @Route ("/supprimer", name="delete_holiday", options={"expose"=true}, methods="GET|POST")
     */
    public function deleteHoliday(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $holidayToDelete = $this->holidayRepository->find($data['id']);

        if ($this->canDelete($holidayToDelete)) {
            if ($holidayToDelete->getStatus() !== Holiday::STATUS_A_VALIDER
                || $holidayToDelete->getRequester() !== $this->getUser()) {
                return new JsonResponse(false);
            }
            /**
             * @var User $user
             */
			$user = $this->getUser();
			$user->decrementStockHolidays($holidayToDelete);
        	$em = $this->getDoctrine()->getManager();
			$em->remove($holidayToDelete);
			$em->flush();
		}

		return new JsonResponse(['cp' => $user->getCP(), 'rtt' => $user->getRTT() ?? 0]);
    }

    /**
     * @Route ("/dataTable", name="print_data_table", options={"expose"=true}, methods="GET|POST")
     */
    public function holidayDataTable(Request $request)
    {
        if ($this->accessService->hasAccess($this->getUser(), Access::DISPLAY_ALL_HOLIDAYS)) {
            $allHoliday = $this->holidayRepository->findAll();
        } else {
            $allHoliday = $this->holidayRepository->findByUserOrValidator($this->getUser());
        }

        $rows = [];
        foreach ($allHoliday as $holiday) {
            $rows[] =
                [
                    "Demandeur" => $holiday->getRequester()->__toString(),
                    "Date de demande" => $holiday->getRequestDate()->format('d/m/Y'),
                    "Description" => $holiday->getDescription(),
                    "Début" => $holiday->getStartDate() ? $holiday->getStartDate()->format('d/m/Y') : '-',
                    "Fin" => $holiday->getEndDate() ? $holiday->getEndDate()->format('d/m/Y') : '-',
                    "Nombre jours" => $holiday->getCp() + ($holiday->getRtt() ?? 0) + $holiday->getSs(),
                    "Statut" => $holiday->getStatus(),
                    "Date de validation" => $holiday->getValidationDate() ? $holiday->getValidationDate()->format('d/m/Y') : '',
                    "Validateur" => $holiday->getValidator() ? $holiday->getValidator()->__toString() : '',
                    'Action' => $this->renderView('holiday/actionHoliday.html.twig', [
                        'id' => $holiday->getId(),
                        'status' => $holiday->getStatus(),
                        'canUpdate' => $this->canUpdate($holiday),
                        'canDelete' => $this->canDelete($holiday),
                        'userId' => $holiday->getRequester()->getId()
                    ]),
                ];
        }
        $data['data'] = $rows;
        return new JsonResponse($data);
    }

    /**
     * @Route ("/print_info", name="print_info_holiday", options={"expose"=true}, methods="GET|POST")
     */
    public function printInfoHoliday(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $holidayToPrint = $this->holidayRepository->find($data['id']);
        $canValidate = $this->canValidate($holidayToPrint);
        $canUpdate = $this->getUser() == $holidayToPrint->getRequester();
        $requester = $holidayToPrint->getRequester();
        $template = $this->renderView('holiday/contentEditHoliday.html.twig', [
            'holiday' => $holidayToPrint,
            'canUpdate' => $canUpdate,
            'validator' => $canValidate,
			'cpCurrentUser' => $requester->getCP(),
			'rttCurrentUser' => $requester->getRTT() ?? 0,
        ]);
        return new JsonResponse($template);
    }

    /**
     * @Route ("/modifier", name="edit_holiday", options={"expose"=true}, methods="GET|POST")
     */
    public function editHoliday(Request $request)
    {

        $data = json_decode($request->getContent(), true);
        $holidayToEdit = $this->holidayRepository->find($data['id']);

        if ($this->canUpdate($holidayToEdit)) {
			$newFirstDate = \DateTime::createFromFormat('Y-m-d', $data['firstDate']);
			$newSecondDate = $data['secondDate'] !== '' ? \DateTime::createFromFormat('Y-m-d', $data['secondDate']) : null;
			$holidayToEdit
				->setSs($data['ss'] !== '' ? max(0, $data['ss']) : '0')
				->setCp($data['cp'] !== '' ? max(0, $data['cp']) : '0')
				->setRtt($data['rtt'] !== '' ? max(0, $data['rtt']) : '0')
				->setStartDate($newFirstDate)
				->setEndDate($newSecondDate)
				->setStatus(Holiday::STATUS_A_VALIDER)
				->setRequestDate(new \DateTime())
				->setDescription($data['description']);
			$em = $this->getDoctrine()->getManager();
			$em->persist($holidayToEdit);
			$em->flush();
		}

		/** @var User $user */
		$user = $this->getUser();
		return new JsonResponse(['cp' => $user->getCP(), 'rtt' => $user->getRTT() ?? 0]);
    }

    /**
     * @Route ("/valider", name="validate_holiday", options={"expose"=true}, methods="GET|POST")
     */
    public function validateHoliday(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $holidayToValid = $this->holidayRepository->find($data['id']);
        $holidayToValid->setStatus(Holiday::STATUS_VALIDE)
            ->setReason($data['reason'])
            ->setValidationDate(new \DateTime())
            ->setValidator($this->getUser());
        $em = $this->getDoctrine()->getManager();
        $em->persist($holidayToValid);
        $em->flush();

        $this->mailerService->sendMail(
            'CONGÉS // Congé Accepté',
            $this->renderView('mails/validateHolidays.html.twig', [
                'conge' => $holidayToValid
            ]),
            $holidayToValid->getRequester()->getEmail()
        );

        return new JsonResponse();
    }

    /**
     * @Route ("/refuser", name="refuse_holiday", options={"expose"=true}, methods="GET|POST")
     */
    public function refuseHoliday(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $holidayToRefus = $this->holidayRepository->find($data['id']);
        $holidayToRefus->setStatus(Holiday::STATUS_REFUSE)
            ->setReason($data['reason'])
            ->setValidationDate(new \DateTime())
            ->setValidator($this->getUser());
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $user->decrementStockHolidays($holidayToRefus);
        $em = $this->getDoctrine()->getManager();
        $em->persist($holidayToRefus);
        $em->flush();

        $this->mailerService->sendMail(
            'CONGÉS // Congé Refusé',
            $this->renderView('mails/refuseHolidays.html.twig', [
                'conge' => $holidayToRefus
            ]),
            $holidayToRefus->getRequester()->getEmail()
        );

        return new JsonResponse();
    }

    /**
     * @param Holiday $holiday
     * @return bool
     */
    private function canUpdate(Holiday $holiday): bool
    {
        $canUpdate = false;
        $currentUser = $this->getUser();

        // soit l'utilisateur courant est un validateur
        $listValidator = $holiday->getRequester()->getValidators();
        if ($listValidator->contains($currentUser)) {
            $canUpdate = true;
        }

        // soit l'utilisateur courant est le demandeur
        $holidayRequester = $holiday->getRequester();
        if ($currentUser == $holidayRequester) {
            $canUpdate = true;
        }

        // et le statut doit être à valider
        if ($holiday->getStatus() !== Holiday::STATUS_A_VALIDER) {
            $canUpdate = false;
        }

        return $canUpdate;
    }

    /**
     * @param Holiday $holiday
     * @return bool
     */
    private function canDelete(Holiday $holiday): bool
    {
        // l'utilisateur courant doit être le demandeur
        $holidayRequester = $holiday->getRequester();
        if ($this->getUser() == $holidayRequester) {
            $isUserRequester = true;
        } else {
            $isUserRequester = false;
        }

        // le statut doit être à valider
        if ($holiday->getStatus() == Holiday::STATUS_A_VALIDER) {
            $isStatusValid = true;
        } else {
            $isStatusValid = false;
        }

        return $isUserRequester && $isStatusValid;
    }

    /**
     * @param Holiday $holiday
     * @return bool
     */
    private function canValidate(Holiday $holiday): bool
    {
        $listValidator = $holiday->getRequester()->getValidators();
        $canUpdate = false;
        foreach ($listValidator as $validator) {
            $currentUser = $this->getUser();
            if ($currentUser == $validator) {
                $canUpdate = true;
            }
        }
        return $canUpdate;
    }


    private function checkHoliday($data)
    {
        $user = $this->getUser();

		$cp = floatval($data['cp']);
		$rtt = floatval($data['rtt']);
		$ss = floatval($data['ss']);

        $userCP = $user->getCP();
        $userRTT = $user->getRTT() ?? 0;
        $datetime1 = date_create($data['firstDate']);
        $datetime2 = date_create(empty($data['secondDate']) ? $data['firstDate'] : $data['secondDate']);
        $nbrOfDay = 0;

        if ($datetime2 == '' || $datetime2 == $datetime1)
        {
            $nbrOfDay = ($cp + $rtt + $ss) == 0.5 ? 0.5 : 1 ;
        }
        else {
            $interval = $datetime1->diff($datetime2);
            for ($i = 0; $i <= $interval->d; $i++) {
                $weekday = $datetime1->format('w');
                $year = $datetime1->format("Y");
                $easterDate  = easter_date($year);
                $easterDay   = date('j', $easterDate);
                $easterDayPaque   = date('j', $easterDay) + 1;
                $easterDayLundiPaque   = date('j', $easterDay) + 39;
                $easterDayPentecote   = date('j', $easterDay) + 50;
                $easterMonth = date('n', $easterDate);
                $easterYear   = date('Y', $easterDate);
                $publicHoliday = array(
                    \DateTime::createFromFormat('Y-m-d', $year . '-01-01')->format('Y-m-d'),
                    \DateTime::createFromFormat('Y-m-d', $year . '-05-08')->format('Y-m-d'),
                    \DateTime::createFromFormat('Y-m-d', $year . '-05-01')->format('Y-m-d'),
                    \DateTime::createFromFormat('Y-m-d', $year . '-07-14')->format('Y-m-d'),
                    \DateTime::createFromFormat('Y-m-d', $year . '-08-15')->format('Y-m-d'),
                    \DateTime::createFromFormat('Y-m-d', $year . '-11-01')->format('Y-m-d'),
                    \DateTime::createFromFormat('Y-m-d', $year . '-11-11')->format('Y-m-d'),
                    \DateTime::createFromFormat('Y-m-d', $year . '-12-25')->format('Y-m-d'),

                    \DateTime::createFromFormat('Y-m-d', $easterYear . '-' . $easterMonth . '-' . $easterDayPaque)->format('Y-m-d'),
                    \DateTime::createFromFormat('Y-m-d', $easterYear . '-' . $easterMonth . '-' . $easterDayLundiPaque)->format('Y-m-d'),
                    \DateTime::createFromFormat('Y-m-d', $easterYear . '-' . $easterMonth . '-' . $easterDayPentecote)->format('Y-m-d'),
                );
                if ($weekday !== "6" && $weekday !== "0" && !in_array($datetime1->format('Y-m-d'), $publicHoliday)) {
                    $nbrOfDay++;
                }
                $datetime1->modify('+1 day');
            }
        }

        return !(($cp > $userCP || $rtt > $userRTT) || ($cp + $ss + $rtt != $nbrOfDay));
    }
}
