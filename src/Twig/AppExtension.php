<?php

namespace App\Twig;

use App\Entity\User;

use App\Repository\UserRepository;

use App\Service\AccessService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var AccessService
     */
    private $accessService;

	/**
	 * AppExtension constructor.
	 * @param AccessService $accessService
	 * @param UserRepository $userRepository
	 * @param TokenStorageInterface $tokenStorage
	 */
    public function __construct(AccessService $accessService, EntityManagerInterface $manager, TokenStorageInterface $tokenStorage)
    {
        $this->accessService = $accessService;
        $this->userRepository = $manager->getRepository(User::class);
        $this->tokenStorage = $tokenStorage;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('hasAccess', [$this, 'hasAccessFunction']),
            ];
    }

    public function hasAccessFunction($testedAccess)
    {
        $user = $this->tokenStorage->getToken()->getUser();
        return $this->accessService->hasAccess($user, $testedAccess);
    }
}
