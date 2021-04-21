<?php


namespace App\Command;


use App\Entity\Parameters;
use App\Entity\User;
use App\Repository\ParametersRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HolidaysCommand extends Command
{
    protected static $defaultName = 'app:increment:days';


    /**
     * @var ParametersRepository
     */
    private $parametersRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * HolidaysCommand constructor.
     * @param ParametersRepository $parametersRepository
     * @param UserRepository $userRepository
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->parametersRepository = $entityManager->getRepository(Parameters::class);
        $this->userRepository= $entityManager->getRepository(User::class);
        $this->entityManager = $entityManager;
    }

    protected function configure()
    {
        $this->setDescription('This commands adds a certain amount of CP and RTT to every user at the end of the month.');
        $this->setHelp('This command is supposed to be executed at every end of month, via a cron on the server.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $parameters = $this->parametersRepository->findOne();
        foreach ($this->userRepository->findAll() as $user) {
            $user->endOfMonth($parameters);
            $this->entityManager->flush();
        }
    }
}
