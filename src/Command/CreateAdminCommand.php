<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CreateAdminCommand extends Command
{
    protected static $defaultName = 'app:create-admin';

    private $entityManager;
    private $passwordEncoder;

    public function __construct(EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->entityManager = $entityManager;
        $this->passwordEncoder = $passwordEncoder;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates a new admin user.')
            ->setHelp('This command allows you to create an admin user...');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = $this->getHelper('question');

        $question = new Question('Please enter the name of the new admin user: ');
        $name = $io->ask($input, $output, $question);

        $question = new Question('Please enter the email of the new admin user: ');
        $email = $io->ask($input, $output, $question);

        $question = new Question('Please enter the password of the new admin user: ');
        $password = $io->ask($input, $output, $question);

        $userRepo = $this->entityManager->getRepository(User::class);
        $existingUser = $userRepo->findOneBy(['email' => $email]);

        if ($existingUser) {
            $output->writeln('<error>A user with this email already exists!</error>');
            return Command::FAILURE;
        }

        $user = new User();
        $user->setName($name);
        $user->setEmail($email);
        $user->setRoles([User::ROLE_ADMIN]);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $password));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln('<info>Admin user created!</info>');

        return Command::SUCCESS;
    }
}
