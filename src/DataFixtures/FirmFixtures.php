<?php

namespace App\DataFixtures;

use App\Entity\Firm;
use App\Repository\FirmRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class FirmFixtures extends Fixture implements FixtureGroupInterface
{

    /**
     * @var FirmRepository
     */
    private $firmRepository;

    public function __construct(EntityManagerInterface $manager)
    {
        $this->firmRepository = $manager->getRepository(Firm::class);
    }

    public function load(ObjectManager $manager)
    {
        if ($this->firmRepository->findByLabel('Wiilog') === null) {
            $newFirm = new Firm();
            $newFirm
                ->setLabel('Wiilog')
                ->setEmployeeNumber(6);
            $manager->persist($newFirm);
            $manager->flush();
        }
    }


    public static function getGroups(): array
    {
        return ['fixtures'];
    }
}
