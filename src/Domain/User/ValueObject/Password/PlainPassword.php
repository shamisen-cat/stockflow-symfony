<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject\Password;

use App\Domain\Shared\ValueObject\AbstractValueObject;
use App\Domain\User\Exception\InvalidPlainPasswordException;

/**
 * @extends AbstractValueObject<string>
 */
final readonly class PlainPassword extends AbstractValueObject
{
    public const int MIN_LENGTH = 12;
    public const int MAX_LENGTH = 128;

    private string $value;

    public static function of(string $plainPassword): self
    {
        return new self($plainPassword);
    }

    private function __construct(string $plainPassword)
    {
        $result = self::validate($plainPassword);

        if (!$result->isValid()) {
            throw InvalidPlainPasswordException::fromValidationResult($result);
        }

        $this->value = $plainPassword;
    }

    public static function validate(string $plainPassword): PlainPasswordValidationResult
    {
        if ($plainPassword === '') {
            return PlainPasswordValidationResult::EMPTY;
        }

        $length = mb_strlen($plainPassword, 'UTF-8');

        if ($length < self::MIN_LENGTH) {
            return PlainPasswordValidationResult::TOO_SHORT;
        }

        if ($length > self::MAX_LENGTH) {
            return PlainPasswordValidationResult::TOO_LONG;
        }

        return PlainPasswordValidationResult::VALID;
    }

    /**
     * @see AbstractValueObject
     */
    #[\Override]
    public function value(): string
    {
        return $this->value;
    }
}
