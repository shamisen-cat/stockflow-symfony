<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use App\Domain\Shared\ValueObject\AbstractValueObject;
use App\Domain\User\ValueObject\Trait\EmailAddressValidationTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * メールアドレスの値オブジェクト
 */
#[ORM\Embedded]
final class EmailAddress extends AbstractValueObject
{
    /** メールアドレスの値を検証するトレイト */
    use EmailAddressValidationTrait;

    /** メールアドレスの値 */
    #[ORM\Column(
        name: 'email',
        type: Types::STRING,
        length: self::MAX_LENGTH,
        unique: true,
    )]
    private readonly string $value;

    /**
     * メールアドレスの値オブジェクトを生成する。
     *
     * @param string $email メールアドレス
     */
    private function __construct(string $email)
    {
        $this->assertValue($email);
        $this->value = $email;
    }

    /**
     * 指定されたメールアドレスから値オブジェクトを生成する。
     *
     * @param string $email メールアドレス
     *
     * @return self メールアドレスの値オブジェクト
     */
    public static function of(string $email): self
    {
        return new self($email);
    }

    /**
     * メールアドレスを取得する。
     *
     * @return string メールアドレス
     */
    public function toString(): string
    {
        return $this->value();
    }

    /**
     * 値を取得する。
     *
     * @return string 値
     *
     * @see AbstractValueObject
     */
    public function value(): string
    {
        return $this->value;
    }
}
