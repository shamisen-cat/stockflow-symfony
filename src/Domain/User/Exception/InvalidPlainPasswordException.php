<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

use App\Domain\Shared\Exception\InvalidValueObjectException;
use App\Domain\User\ValueObject\Password\PlainPassword;
use App\Domain\User\ValueObject\Password\PlainPasswordValidationResult;

final class InvalidPlainPasswordException extends InvalidValueObjectException
{
    private function __construct(
        string $message,
        public readonly PlainPasswordValidationResult $result,
    ) {
        parent::__construct($message);
    }

    public static function fromValidationResult(PlainPasswordValidationResult $result): self
    {
        if ($result === PlainPasswordValidationResult::VALID) {
            throw new \LogicException(sprintf(
                'Cannot create InvalidPlainPasswordException from %s result.',
                $result->name,
            ));
        }

        $message = match ($result) {
            PlainPasswordValidationResult::EMPTY => 'Plain password must not be empty.',
            PlainPasswordValidationResult::TOO_SHORT => sprintf(
                'Plain password must be at least %d characters.',
                PlainPassword::MIN_LENGTH,
            ),
            PlainPasswordValidationResult::TOO_LONG => sprintf(
                'Plain password must not exceed %d characters.',
                PlainPassword::MAX_LENGTH,
            ),
        };

        return new self($message, $result);
    }
}
