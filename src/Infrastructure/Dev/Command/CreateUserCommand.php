<?php

declare(strict_types=1);

namespace App\Infrastructure\Dev\Command;

use App\Application\Shared\Transaction\TransactionManagerInterface;
use App\Application\User\CreateUser\CreateUserHandler;
use App\Application\User\CreateUser\CreateUserInput;
use App\Domain\User\Exception\UserAlreadyExistsException;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'app:dev:user:create',
    description: 'Create dev user with default credentials.',
)]
final class CreateUserCommand extends Command
{
    public function __construct(
        #[Autowire(param: 'kernel.environment')]
        private readonly string $kernelEnvironment,
        private readonly TransactionManagerInterface $transactionManager,
        private readonly CreateUserHandler $createUserHandler,
        private readonly ClockInterface $clock,
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

        $createUserInput = CreateUserInput::create(
            email: $email,
            password: $password,
            createdAt: $this->clock->now(),
        );

        try {
            $this->transactionManager->transactional(
                fn () => $this->createUserHandler->handle($createUserInput),
            );
        } catch (UserAlreadyExistsException) {
            $io->error(sprintf('User already exists: %s.', $email));

            return Command::FAILURE;
        }

        $io->success(sprintf('User created: %s.', $email));

        return Command::SUCCESS;
    }
}
