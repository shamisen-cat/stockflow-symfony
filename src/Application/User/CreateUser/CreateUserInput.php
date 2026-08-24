<?php

declare(strict_types=1);

namespace App\Application\User\CreateUser;

use App\Domain\User\Email\Email;
use App\Domain\User\Password\PlainPassword;

final readonly class CreateUserInput
{
    public Email $email;
    public PlainPassword $password;

    public function __construct(
        string $email,
        string $password,
        public \DateTimeImmutable $createdAt,
    ) {
        $this->email = Email::of(trim($email));
        $this->password = PlainPassword::of($password);
    }
}
