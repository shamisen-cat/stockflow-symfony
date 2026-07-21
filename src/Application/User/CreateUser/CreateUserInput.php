<?php

declare(strict_types=1);

namespace App\Application\User\CreateUser;

use App\Domain\User\ValueObject\Email\Email;
use App\Domain\User\ValueObject\Password\PlainPassword;

final readonly class CreateUserInput
{
    public function __construct(
        public Email $email,
        public PlainPassword $password,
        public \DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        string $email,
        string $password,
        \DateTimeImmutable $createdAt,
    ): self {
        $trimmedEmail = trim($email);

        return new self(
            email: Email::of($trimmedEmail),
            password: PlainPassword::of($password),
            createdAt: $createdAt,
        );
    }
}
