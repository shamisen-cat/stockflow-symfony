<?php

declare(strict_types=1);

namespace App\Infrastructure\Dev\Command;

use App\Domain\User\Entity\User;
use App\Domain\User\Password\PlainPasswordHasherInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email\Email;
use App\Domain\User\ValueObject\Password\PlainPassword;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Uid\Uuid;

#[AsCommand(
    name: 'app:dev:user:create',
    description: 'Create dev user with default credentials.',
)]
final class CreateUserCommand extends Command
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly PlainPasswordHasherInterface $plainPasswordHasher,
        #[Autowire(param: 'kernel.environment')]
        private readonly string $kernelEnvironment,
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->kernelEnvironment !== 'dev') {
            $io->error('Not available outside dev environment.');

            return Command::FAILURE;
        }

        $email = 'dev@example.com';
        $password = 'stockflow-dev';

        $existingUser = $this->userRepository->findActiveByEmail($email);

        if ($existingUser !== null) {
            $io->error(sprintf('User already exists: %s.', $email));

            return Command::FAILURE;
        }

        $plainPassword = PlainPassword::of($password);
        $hashedPassword = $this->plainPasswordHasher->hash($plainPassword);

        $user = User::create(
            id: Uuid::v7(),
            email: Email::of($email),
            password: $hashedPassword,
            createdAt: new \DateTimeImmutable(),
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $io->success(sprintf(
            'User created: %s (id: %s).',
            $user->email->value(),
            $user->id->toRfc4122(),
        ));

        return Command::SUCCESS;
    }
}
