<?php

namespace App\Controller;

use App\Entity\Firm;
use App\Entity\User;
use App\Entity\Access;

use App\Repository\UserRepository;
use App\Repository\FirmRepository;
use App\Repository\AccessRepository;


use App\Service\AccessService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Twig\TwigFunction;


/**
 * @Route("/utilisateurs")
 */
class UserController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var FirmRepository
     */
    private $firmRepository;

    /**
     * @var AccessRepository
     */
    private $accessRepository;

	/**
	 * @var AccessService
	 */
    private $accessService;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    private $em;

    public function __construct(AccessService $accessService, UserPasswordEncoderInterface $encoder, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->userRepository = $em->getRepository(User::class);
        $this->firmRepository = $em->getRepository(Firm::class);
        $this->accessRepository = $em->getRepository(Access::class);
        $this->accessService = $accessService;
        $this->encoder = $encoder;
    }

	/**
     * @Route("/", name="users")
     */
    public function index()
    {
    	if ($this->accessService->hasAccess($this->getUser(), Access::MANAGE_USERS)) {
			$allUsers = $this->userRepository->findAll();
			return $this->render('users/listUsers.html.twig', [
				'users' => $allUsers
			]);
		} else {
    		return $this->redirectToRoute('access_denied');
		}

    }

    /**
     * @Route("/creer", name="new_user", options={"expose"=true}, methods="GET|POST")
     */
    public function newUser(Request $request): Response
    {
        if ($request->isXmlHttpRequest() && $data = json_decode($request->getContent(), true)) {
            $user = $this->userRepository->findOneByEmail($data['email']);

            if(!$user){
            $entityManager = $this->em;
            $newUser = new User();
				$password = $this->encoder->encodePassword($newUser, $data['password']);
				$newUser
					->setName($data['nom'])
					->setFirstname($data['prenom'])
					->setPassword($password)
					->setEmail($data['email'])
					->setUsername($data['email'])
					->setStatus($data['statusNew'])
					->setFirm($this->firmRepository->findByLabel(Firm::LABEL_WIILOG));

            $entityManager->persist($newUser);
            $entityManager->flush();

            if($data['statusNew']){
                $firm = $this->firmRepository->findByLabel(Firm::LABEL_WIILOG);
                $firm->setEmployeeNumber($firm->getEmployeeNumber()+1);

                if($data['manageUsers']){
                    $newUser->addAccess($this->accessRepository->findByCode(Access::MANAGE_USERS));
                }
                if($data['displayHolidays']){
                    $newUser->addAccess($this->accessRepository->findByCode(Access::DISPLAY_ALL_HOLIDAYS));
                }

                if(isset($data['validatorsNew'])){
                    foreach($data['validatorsNew'] as $validatorId){
                        $employee = $this->userRepository->find($validatorId);
                        $newUser->addValidator($employee);
                    }
                }
            }
            $entityManager->persist($newUser);
            $entityManager->flush();

            $json = $this->renderView('users/modalValidateNewUser.html.twig', ['user' => $newUser]);
            } else {
                $json = ['errorMsg' => "L'utilisateur possédant cette adresse e-mail existe déjà."];
            }

            return new JsonResponse($json);
        }
        throw new NotFoundHttpException("404");
    }

    /**
     * @Route("/liste", name="user_api", options={"expose"=true}, methods="GET|POST")
     */
    public function userApi(Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            $listUsers = $this->userRepository->findAll();
            $rows = [];

            foreach($listUsers as $user){

                $listUserValidated = [];
                foreach($user->getValidators() as $validator){
                    $listUserValidated[] = implode(' ', $this->userRepository->getNameAndFirstnameByUser($validator));
                }

               $rows[] =
                [
                    "Nom" => $user->getName(),
                    "Prénom" => $user->getFirstname(),
                    "Email" => $user->getEmail(),
                    "Statut" => $user->getStatus() ? 'Actif' : 'Inactif',
                    "Validateurs" => implode(' / ', $listUserValidated),
                    "Dernière connexion" => $user->getLastConnexion() ? $user->getLastConnexion()->format("d/m/Y") : '',
                    'cp' => $user->getCP(),
                    'rtt' => $user->getRTT() ?? 0,
                    'Action' => $this->renderView('users/actionUser.html.twig', [
                    	'userId' =>$user->getId()
                    ]),
                ];
            }
            $data['data'] = $rows;
            return new JsonResponse($data);
        }
        throw new NotFoundHttpException("404");
    }

    /**
     * @Route("/supprimer-utilisateur", name="delete_user", options={"expose"=true}, methods="GET|POST")
     */
    public function deleteUser(Request $request): Response
    {
        if ($request->isXmlHttpRequest() && $userId = json_decode($request->getContent(), true)){
            $user = $this->userRepository->find($userId);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();

            return new JsonResponse();
        }
        throw new NotFoundHttpException("404");
    }

    /**
     * @Route("/api-modifier", name="user_api_edit", options={"expose"=true},  methods="GET|POST")
     */
    public function editApi(Request $request): Response
    {
        if ($request->isXmlHttpRequest() && $data = json_decode($request->getContent(), true)) {

            $allUser = $this->userRepository->findAll();
            $user = $this->userRepository->find($data);

            $accessesUser = [];
            foreach($user->getAccesses() as $access){
                $accessesUser[] = $access->getCode();
            }

            $validatorOf = [];
            foreach($user->getValidators() as $validator){
                $validatorOf[] = $validator->getId();
            }

            $html = $this->renderView('users/modalEditUserContent.html.twig', [
                'user' => $user,
                'listAccess' => $accessesUser,
                'allUser' => $allUser
            ]);

            return new JsonResponse(['html' => $html, 'listValidateur' => $validatorOf]);
        }
        throw new NotFoundHttpException('404');
    }

    /**
     * @Route("/modifier-utilisateur", name="edit_user", options={"expose"=true},  methods="GET|POST")
     */
    public function editUser(Request $request): Response
    {
        if ($request->isXmlHttpRequest() && $data = json_decode($request->getContent(), true)) {
            $em = $this->getDoctrine()->getManager();
            $user = $this->userRepository->find($data['user']);

            if(isset($data['nom'])){
                $user->setName($data['nom']);
            }
            if(isset($data['prenom'])){
                $user->setFirstname($data['prenom']);
            }
            if(isset($data['email'])){
                $userAlreadyExist = $this->userRepository->findOneByEmail($data['email']);

                if($user !== $userAlreadyExist && $userAlreadyExist){
                    return new JsonResponse(['errorMsg' => "L'utilisateur possédant cette adresse e-mail existe déjà."]);
                } else {
                    $user->setEmail($data['email']);
                }
            }
            if(isset($data['password']) && $data['password'] != ""){
                $password = $this->encoder->encodePassword($user, $data['password']);
                $user->setPassword($password);
            }
            $user->setStatus($data['statusEdit']);
            if(!$data['statusEdit']){
                $firm = $this->firmRepository->findByLabel(Firm::LABEL_WIILOG);
                $firm->setEmployeeNumber($firm->getEmployeeNumber()-1);

				foreach($user->getValidators() as $validated){
					$user->removeValidator($validated);
				}

				foreach($user->getAccesses() as $access){
					$user->removeAccess($access);
				}
            } else {

                $listExistingValidators = $user->getValidators();
                foreach ($listExistingValidators as $validator) {
                    $user->removeValidator($validator);
                }
                if (isset($data['validatorsEdit'])) {
                    foreach ($data['validatorsEdit'] as $validator) {
                        $user->addValidator($this->userRepository->find($validator));
                    }
                }

                if ($data['manageUsers']) {
                    $user->addAccess($this->accessRepository->findByCode(Access::MANAGE_USERS));
                } elseif (!$data['manageUsers']) {
                    $user->removeAccess($this->accessRepository->findByCode(Access::MANAGE_USERS));
                }
                if ($data['displayHolidays']) {
                    $user->addAccess($this->accessRepository->findByCode(Access::DISPLAY_ALL_HOLIDAYS));
                } elseif (!$data['displayHolidays']) {
                    $user->removeAccess($this->accessRepository->findByCode(Access::DISPLAY_ALL_HOLIDAYS));
                }
            }
            if (isset($data['cp']) && $data['cp'] >= 0)
            {
                $user->setCP(floatval($data['cp']));
            }
            if (isset($data['rtt']) && $data['rtt'] >= 0)
            {
                $user->setRTT(floatval($data['rtt']));
            }
            $em->persist($user);
            $em->flush();
            return new JsonResponse('USER_EDIT');
        }
        throw new NotFoundHttpException('404');
    }

    /**
     * @Route("/autocomplete", name="get_demandeur", options={"expose"=true})
     */
    public function getDemandeur(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            $search = $request->query->get('term');

            $demandeur = $this->userRepository->getIdAndNomBySearch($search);
            return new JsonResponse(['results' => $demandeur]);
        }
        throw new NotFoundHttpException("404");
    }

    /**
     * @Route("/rechercher-utilisateur", name="search_user", options={"expose"=true},  methods="GET|POST")
     */
    public function searchUser(Request $request)
    {
        if ($request->isXmlHttpRequest() && $data = json_decode($request->getContent(), true)) {
            $id = $data['id'];

            $name = $this->userRepository->getNameById($id);
            if ($name == null)
                return new JsonResponse('null');
            return new JsonResponse($name);
        }
        throw new NotFoundHttpException("404");
    }
}
