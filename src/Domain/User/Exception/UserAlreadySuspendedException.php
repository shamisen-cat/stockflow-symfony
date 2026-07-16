<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

use App\Domain\Shared\Exception\EntityException;
use Symfony\Component\Uid\Uuid;

final class UserAlreadySuspendedException extends EntityException
{
    private function __construct(
        string $message,
        public readonly Uuid $userId,
    ) {
        parent::__construct($message);
    }

    public static function forUser(Uuid $userId): self
    {
        return new self('User is already suspended.', $userId);
    }
}
