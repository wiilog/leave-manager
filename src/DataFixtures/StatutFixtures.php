<?php

namespace App\DataFixtures;

use App\Entity\Holiday;
use App\Entity\Statut;
use App\Repository\StatutRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class StatutFixtures extends Fixture implements FixtureGroupInterface {

    /**
     * @var StatutRepository
     */
    private $statutRepository;

    public function __construct(EntityManagerInterface $manager) {
        $this->statutRepository = $manager->getRepository(Statut::class);
    }

    public function load(ObjectManager $manager) {
        $statuts = [
            Holiday::STATUS_A_VALIDER,
            Holiday::STATUS_VALIDE,
            Holiday::STATUS_REFUSE,
        ];
        foreach ($statuts as $statutIterate) {
            $statut = $this->statutRepository->findOneByLabelAndCategorie($statutIterate, Holiday::CATEGORIE);
            if ($statut === null) {
                $statut = new Statut();
                $statut
                    ->setCategorie(Holiday::CATEGORIE)
                    ->setLabel($statutIterate);
                $manager->persist($statut);
                $manager->flush();
            }
        }
    }

    /**
     * This method must return an array of groups
     * on which the implementing class belongs to
     *
     * @return string[]
     */
    public static function getGroups(): array {
        return ['fixtures'];
    }

}
