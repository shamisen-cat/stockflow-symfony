<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

use App\Domain\Shared\Exception\EntityException;

final class UserAlreadyExistsException extends EntityException
{
    private function __construct(
        string $message,
        public readonly string $email,
    ) {
        parent::__construct($message);
    }

    public static function forEmail(string $email): self
    {
        return new self('User already exists.', $email);
    }
}
