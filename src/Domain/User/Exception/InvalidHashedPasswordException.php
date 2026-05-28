<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

use App\Domain\Shared\Exception\InvalidValueObjectException;
use App\Domain\User\ValueObject\Password\HashedPassword;

final class InvalidHashedPasswordException extends InvalidValueObjectException
{
    private function __construct(
        string $message,
        public readonly ?string $algorithm,
    ) {
        parent::__construct($message);
    }

    public static function empty(): self
    {
        return new self('Hashed password must not be empty.', null);
    }

    public static function tooLong(): self
    {
        return new self(
            sprintf('Hashed password must not exceed %d characters.', HashedPassword::MAX_LENGTH),
            null,
        );
    }

    public static function unsupportedAlgorithm(string $algorithm): self
    {
        return new self('Hashed password must use Argon2id.', $algorithm);
    }
}
