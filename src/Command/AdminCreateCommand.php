<?php declare(strict_types=1);

namespace App\Command;

use App\Entity\ENUM\Roles;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:admin:create',
    description: 'Create administrator',
)]
class AdminCreateCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly UserRepository $userRepository,
        private readonly ValidatorInterface $validator
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->text('Create administrator');
        /** @var string $email */
        $email         = $io->ask('Enter email:');

        if ($this->userRepository->findOneBy(['email' => $email]) instanceof User) {
            $io->error('This email is taken');

            return Command::FAILURE;
        }

        $firstName = $io->ask('Enter firstname');
        $lastName = $io->ask('Enter lastname');
        $password = $io->ask('Enter password');
        $phone = $io->ask('Enter phone');

        $user = new User();

        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setPhoneNumber($phone);
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                $password
            )
        );
        $roles   = $user->getRoles();
        $roles[] = Roles::ROLE_ADMIN;
        $user->setRoles($roles);

        $errors = $this->validator->validate($user);
        if (count($errors) === 0) {
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            $io->success('You successfully create administrator.');

            return Command::SUCCESS;
        }

        $io->error((string)$errors);

        return Command::FAILURE;
    }
}
