<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use App\Domain\Shared\ValueObject\AbstractValueObject;
use App\Domain\User\ValueObject\Trait\EmailAddressValidationTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * 保留中のメールアドレスの値オブジェクト
 */
#[ORM\Embedded]
final class PendingEmailAddress extends AbstractValueObject
{
    /** メールアドレスの値を検証するトレイト */
    use EmailAddressValidationTrait;

    /** 保留中のメールアドレスの値 */
    #[ORM\Column(
        name: 'pending_email',
        type: Types::STRING,
        length: self::MAX_LENGTH,
        nullable: true,
    )]
    private readonly ?string $value;

    /**
     * 保留中のメールアドレスの値オブジェクトを生成する。
     *
     * @param string|null $email 保留対象のメールアドレスまたはnull
     */
    private function __construct(?string $email)
    {
        if ($email !== null) {
            $this->assertValue($email);
        }

        $this->value = $email;
    }

    /**
     * 指定されたメールアドレスから値オブジェクトを生成する。
     *
     * @param string $email 保留対象のメールアドレス
     *
     * @return self 保留中のメールアドレスの値オブジェクト
     */
    public static function of(string $email): self
    {
        return new self($email);
    }

    /**
     * メールアドレスが未設定の値オブジェクトを生成する。
     *
     * @return self メールアドレスが未設定の値オブジェクト
     */
    public static function none(): self
    {
        return new self(null);
    }

    /**
     * 保留中のメールアドレスの有無を判定する。
     */
    public function isNone(): bool
    {
        return $this->value() === null;
    }

    /**
     * 保留中のメールアドレスまたはnullを取得する。
     *
     * @return string|null 保留中のメールアドレスまたはnull
     */
    public function toString(): ?string
    {
        return $this->value();
    }

    /**
     * 値を取得する。
     *
     * @return string|null 値
     *
     * @see AbstractValueObject
     */
    public function value(): ?string
    {
        return $this->value;
    }
}
