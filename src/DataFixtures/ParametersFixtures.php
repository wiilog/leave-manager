<?php


namespace App\DataFixtures;


use App\Entity\Parameters;
use App\Repository\ParametersRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class ParametersFixtures extends Fixture implements FixtureGroupInterface
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
     * This method must return an array of groups
     * on which the implementing class belongs to
     *
     * @return string[]
     */
    public static function getGroups(): array
    {
        return ['fixtures'];
    }

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $parameters = $this->parametersRepository->findOne();
        if ($parameters === null) {
            $parameters = new Parameters();
            $parameters->setCpIncrement(2.08);
            $parameters->setRttIncrement(1);
            $manager->persist($parameters);
            $manager->flush();
        }
    }
}
