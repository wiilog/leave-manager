<?php

namespace App\DataFixtures;

use App\Entity\Access;

use App\Repository\AccessRepository;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class AccessFixtures extends Fixture implements FixtureGroupInterface
{
    /**
     * @var AccessRepository
     */
    private $accessRepository;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->accessRepository = $manager->getRepository(Access::class);
    }


    public function load(ObjectManager $manager)
    {
        $manageUsersAccess = $this->accessRepository->findOneByLabel('Gérer les utilisateurs');

        if(!$manageUsersAccess){
            $manageUsersAccess = new Access();
            $manageUsersAccess
                ->setLabel('Gérer les utilisateurs');
            $manager->persist($manageUsersAccess);
        }
        $manageUsersAccess->setCode(Access::MANAGE_USERS);


        $displayHolidaysAccess = $this->accessRepository->findOneByLabel('Voir toutes les demandes');

        if(!$displayHolidaysAccess){
            $displayHolidaysAccess = new Access();
            $displayHolidaysAccess
                ->setLabel('Voir toutes les demandes');
            $manager->persist($displayHolidaysAccess);
        }
        $displayHolidaysAccess->setCode('DISPLAY_ALL_HOLIDAYS');

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['fixtures'];
    }
}
