<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use App\Domain\Shared\ValueObject\AbstractValueObject;
use App\Domain\User\Interface\EmailValueObject;
use App\Domain\User\ValueObject\Trait\EmailValidationTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * メールの値オブジェクト
 *
 * @extends AbstractValueObject<string>
 */
#[ORM\Embeddable]
final readonly class Email extends AbstractValueObject implements EmailValueObject
{
    /** メールの値オブジェクトを検証するためのトレイト */
    use EmailValidationTrait;

    /** メール */
    #[ORM\Column(
        name: 'email',
        type: Types::STRING,
        length: self::MAX_LENGTH,
        unique: true,
    )]
    private readonly string $value;

    /**
     * @param string $email メール
     *
     * @see EmailValidationTrait::assertValue
     */
    private function __construct(string $email)
    {
        $this->assertValue($email);
        $this->value = $email;
    }

    /**
     * メールから値オブジェクトを生成する。
     *
     * @param string $email メール
     *
     * @return self メールの値オブジェクト
     */
    public static function of(string $email): self
    {
        return new self($email);
    }

    /**
     * メールの値を取得する。
     *
     * @return string メールの値
     *
     * @see EmailValueObject::value
     * @see AbstractValueObject::value
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * メールの値オブジェクトの等価性を比較する。
     *
     * @param EmailValueObject $other メールの値オブジェクト
     *
     * @see EmailValueObject::isSameValue
     */
    public function isSameValue(EmailValueObject $other): bool
    {
        return $this->value() === $other->value();
    }
}
