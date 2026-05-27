<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject\Email;

enum EmailValidationResult
{
    case VALID;
    case EMPTY;
    case TOO_LONG;
    case INVALID_FORMAT;

    public function isValid(): bool
    {
        return $this === self::VALID;
    }
}
