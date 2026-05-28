<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject\Password;

use App\Domain\Shared\ValueObject\AbstractValueObject;
use App\Domain\User\Exception\InvalidHashedPasswordException;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * @extends AbstractValueObject<string>
 */
#[ORM\Embeddable]
final readonly class HashedPassword extends AbstractValueObject
{
    public const int MAX_LENGTH = 255;

    #[ORM\Column(
        name: 'password',
        type: Types::STRING,
        length: self::MAX_LENGTH,
    )]
    private string $value;

    public static function of(string $hashedPassword): self
    {
        return new self($hashedPassword);
    }

    private function __construct(string $hashedPassword)
    {
        if ($hashedPassword === '') {
            throw InvalidHashedPasswordException::empty();
        }

        if (strlen($hashedPassword) > self::MAX_LENGTH) {
            throw InvalidHashedPasswordException::tooLong();
        }

        $passwordInfo = password_get_info($hashedPassword);

        /** @var string $algorithm */
        $algorithm = $passwordInfo['algoName'] ?? 'unknown';

        if ($algorithm !== 'argon2id') {
            throw InvalidHashedPasswordException::unsupportedAlgorithm($algorithm);
        }

        $this->value = $hashedPassword;
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
