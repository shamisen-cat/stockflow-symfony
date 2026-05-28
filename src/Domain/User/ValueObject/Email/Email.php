<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject\Email;

use App\Domain\Shared\ValueObject\AbstractValueObject;
use App\Domain\User\Exception\InvalidEmailException;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * @extends AbstractValueObject<string>
 */
#[ORM\Embeddable]
final readonly class Email extends AbstractValueObject
{
    public const int MAX_LENGTH = 255;

    #[ORM\Column(
        name: 'email',
        type: Types::STRING,
        length: self::MAX_LENGTH,
    )]
    private string $value;

    public static function of(string $email): self
    {
        return new self($email);
    }

    private function __construct(string $email)
    {
        $result = self::validate($email);

        if (!$result->isValid()) {
            throw InvalidEmailException::fromValidationResult($email, $result);
        }

        $this->value = $email;
    }

    public static function validate(string $email): EmailValidationResult
    {
        if ($email === '') {
            return EmailValidationResult::EMPTY;
        }

        if (mb_strlen($email, 'UTF-8') > self::MAX_LENGTH) {
            return EmailValidationResult::TOO_LONG;
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return EmailValidationResult::INVALID_FORMAT;
        }

        return EmailValidationResult::VALID;
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
