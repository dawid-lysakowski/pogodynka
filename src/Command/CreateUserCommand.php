<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateUserCommand extends Command
{
    protected static $defaultName = 'app:create-user';

    public function __construct(private EntityManagerInterface $em)
    {
        // Ensure the command has a name even when registered as a service
        parent::__construct(self::$defaultName);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Create or update a user in the database')
            ->addOption('username', null, InputOption::VALUE_REQUIRED, 'Username', 'admin')
            ->addOption('password-hash', null, InputOption::VALUE_REQUIRED, 'Password hash (already hashed)', "\$2y\$13\$07C96qJL4qEQrEbaLAjpa.GPIz2sRUtKBLT.2HyhxT4x2ONCkKCQq")
            ->addOption('roles', null, InputOption::VALUE_REQUIRED, 'Comma separated roles', 'ROLE_ADMIN')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $username = (string) $input->getOption('username');
        $passwordHash = (string) $input->getOption('password-hash');
        $roles = array_map('trim', explode(',', (string) $input->getOption('roles')));

        $repo = $this->em->getRepository(User::class);
        $user = $repo->findOneBy(['username' => $username]);

        if (!$user) {
            $user = new User();
            $user->setUsername($username);
            $io->text("Creating user '$username'");
        } else {
            $io->text("Updating existing user '$username'");
        }

        $user->setPassword($passwordHash);
        $user->setRoles($roles);

        $this->em->persist($user);
        $this->em->flush();

        $io->success(sprintf('User "%s" saved with roles: %s', $username, implode(', ', $roles)));

        return Command::SUCCESS;
    }
}
