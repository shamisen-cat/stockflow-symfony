<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject\Password;

enum PlainPasswordValidationResult
{
    case VALID;
    case EMPTY;
    case TOO_SHORT;
    case TOO_LONG;

    public function isValid(): bool
    {
        return $this === self::VALID;
    }
}
