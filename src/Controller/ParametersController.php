<?php

namespace App\Controller;

use App\Entity\Parameters;
use App\Repository\ParametersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * @Route("/parametres")
 */
class ParametersController extends AbstractController
{
    /**
     * @var ParametersRepository
     */
    private $parametersRepository;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->parametersRepository = $manager->getRepository(Parameters::class);
    }

    /**
     * @Route("/parametrage", name="params")
     */
    public function index(): response
    {
        $parameters =  $this->parametersRepository->findOne();
        return $this->render('parameters/index.html.twig', [
            'parameters' => $parameters
        ]);
    }


    /**
     * @Route("/ajax-params", name="ajax_params",  options={"expose"=true},  methods="GET|POST")
     */
    public function ajaxDo(Request $request): response
    {
        if (!$request->isXmlHttpRequest() && $data = json_decode($request->getContent(), true)) {
            $em = $this->getDoctrine()->getEntityManager();
            $parameters =  $this->parametersRepository->findOne();
            if (!$parameters) {
                $parameters = new Parameters();
                $em->persist($parameters);
            }
            $parameters
                ->setCpIncrement(floatval($data['cp']))
                ->setRttIncrement(floatval($data['rtt']));
            $em->flush();

            return new JsonResponse($data);
        }
        throw new NotFoundHttpException("404");
    }
}
