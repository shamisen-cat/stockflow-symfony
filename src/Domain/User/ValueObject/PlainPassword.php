<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use App\Domain\Shared\ValueObject\AbstractValueObject;
use App\Domain\User\Interface\PasswordValueObject;
use App\Domain\User\ValueObject\Trait\PasswordValidationTrait;

/**
 * 平文パスワードの値オブジェクト
 *
 * @extends AbstractValueObject<string>
 */
final readonly class PlainPassword extends AbstractValueObject implements PasswordValueObject
{
    /** パスワードの値オブジェクトを検証するためのトレイト */
    use PasswordValidationTrait;

    /** 平文パスワード */
    private readonly string $value;

    /**
     * @param string $password 平文パスワード
     *
     * @see PasswordValidationTrait::assertValue
     */
    private function __construct(string $password)
    {
        $this->assertValue($password);
        $this->value = $password;
    }

    /**
     * 平文パスワードから値オブジェクトを生成する。
     *
     * @param string $password 平文パスワード
     *
     * @return self 平文パスワードの値オブジェクト
     */
    public static function of(string $password): self
    {
        return new self($password);
    }

    /**
     * 平文パスワードの値を取得する。
     *
     * @return string 平文パスワードの値
     *
     * @see PasswordValueObject::value
     * @see AbstractValueObject::value
     */
    public function value(): string
    {
        return $this->value;
    }
}
