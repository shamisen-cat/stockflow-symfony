<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

use App\Domain\Shared\Exception\InvalidValueObjectException;
use App\Domain\User\ValueObject\Email\Email;
use App\Domain\User\ValueObject\Email\EmailValidationResult;

final class InvalidEmailException extends InvalidValueObjectException
{
    private function __construct(
        string $message,
        public readonly string $email,
        public readonly EmailValidationResult $result,
    ) {
        parent::__construct($message);
    }

    public static function fromValidationResult(
        string $email,
        EmailValidationResult $result,
    ): self {
        if ($result === EmailValidationResult::VALID) {
            throw new \LogicException(sprintf(
                'Cannot create InvalidEmailException from %s result.',
                $result->name,
            ));
        }

        $message = match ($result) {
            EmailValidationResult::EMPTY    => 'Email must not be empty.',
            EmailValidationResult::TOO_LONG => sprintf(
                'Email must not exceed %d characters.',
                Email::MAX_LENGTH,
            ),
            EmailValidationResult::INVALID_FORMAT => 'Email format is invalid.',
        };

        return new self($message, $email, $result);
    }
}
