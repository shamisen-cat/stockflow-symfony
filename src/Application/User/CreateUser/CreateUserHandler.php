<?php

declare(strict_types=1);

namespace App\Application\User\CreateUser;

use App\Domain\User\Entity\User;
use App\Domain\User\Exception\UserAlreadyExistsException;
use App\Domain\User\Password\PlainPasswordHasherInterface;
use App\Domain\User\Repository\UserRepositoryInterface;
use Symfony\Component\Uid\Uuid;

final readonly class CreateUserHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PlainPasswordHasherInterface $plainPasswordHasher,
    ) {
    }

    public function handle(CreateUserInput $input): Uuid
    {
        $existingUser = $this->userRepository->findActiveByEmail($input->email->value());

        if ($existingUser !== null) {
            throw UserAlreadyExistsException::forEmail($input->email->value());
        }

        $hashedPassword = $this->plainPasswordHasher->hash($input->password);

        $user = User::create(
            id: Uuid::v7(),
            email: $input->email,
            password: $hashedPassword,
            createdAt: $input->createdAt,
        );

        $this->userRepository->add($user);

        return $user->id;
    }
}
