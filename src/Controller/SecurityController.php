<?php

namespace App\Controller;

use App\Entity\Access;
use App\Entity\Firm;
use App\Entity\User;

use App\Repository\FirmRepository;
use App\Repository\AccessRepository;
use App\Repository\UserRepository;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;


class SecurityController extends AbstractController
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
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    private $em;

    public function __construct(UserPasswordEncoderInterface $encoder, EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->userRepository = $this->em->getRepository(User::class);
        $this->firmRepository = $this->em->getRepository(Firm::class);
        $this->accessRepository = $this->em->getRepository(Access::class);
        $this->encoder = $encoder;
    }

	/**
	 * @Route("/", name="main")
	 */
	public function index()
	{
		return $this->redirectToRoute('app_login');
	}

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
            return $this->redirectToRoute('list_holiday');
         }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \Exception('This method can be blank - it will be intercepted by the logout key on your firewall');
    }

    /**
     * @Route("/register", name="register")
     */
    public function register()
    {
        return $this->render('security/newUser.html.twig');
    }

    /**
     * @Route("/register/creer", name="register_user", options={"expose"=true}, methods="GET|POST")
     */
    public function newUser(Request $request): Response
    {
        if ($request->isXmlHttpRequest() && $data = json_decode($request->getContent(), true)) {
            $user = $this->userRepository->findOneByEmail($data['email']);
            if (!$user) {
                $newUser = new User();
                $password = $this->encoder->encodePassword($newUser, $data['password']);
                $newUser
                    ->setName($data['name'])
                    ->setFirstname($data['firstname'])
                    ->setEmail($data['email'])
                    ->setPassword($password)
                    ->setStatus(0)
                    ->setUsername($data['email']);

                $em = $this->getDoctrine()->getManager();
                $em->persist($newUser);
                $em->flush();
                return new JsonResponse('created');
            } else {
                return new JsonResponse('used');
            }
        }
        throw new NotFoundHttpException("404");
    }

	/**
	 * @Route("/acces-refuse", name="access_denied")
	 */
	public function access_denied()
	{
		return $this->render('security/access_denied.html.twig');
	}
}
