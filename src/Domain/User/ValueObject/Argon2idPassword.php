<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use App\Domain\Shared\ValueObject\AbstractValueObject;
use App\Domain\User\Exception\PasswordException;
use App\Domain\User\Interface\PasswordValueObject;
use App\Domain\User\ValueObject\Trait\PasswordValidationTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Argon2idパスワードの値オブジェクト
 *
 * @extends AbstractValueObject<string>
 */
#[ORM\Embeddable]
final readonly class Argon2idPassword extends AbstractValueObject implements PasswordValueObject
{
    /** パスワードの値オブジェクトを検証するためのトレイト */
    use PasswordValidationTrait;

    /** Argon2idパスワード */
    #[ORM\Column(
        name: 'password',
        type: Types::STRING,
        length: self::MAX_LENGTH,
    )]
    private readonly string $value;

    /**
     * @param string $password Argon2idパスワード
     *
     * @see PasswordValidationTrait::assertValue
     */
    private function __construct(string $password)
    {
        $this->assertValue($password);
        $this->assertArgon2id($password);
        $this->value = $password;
    }

    /**
     * Argon2idパスワードから値オブジェクトを生成する。
     *
     * @param string $password Argon2idパスワード
     *
     * @return self Argon2idパスワードの値オブジェクト
     */
    public static function of(string $password): self
    {
        return new self($password);
    }

    /**
     * Argon2idパスワードを検証する。
     *
     * 例外理由：
     * - パスワードがArgon2idを使用していない
     *
     * @param string $password Argon2idパスワード
     *
     * @throws PasswordException
     */
    private function assertArgon2id(string $password): void
    {
        $info          = password_get_info($password);
        $algorithmName = $info['algoName'] ?? 'unknown';
        if ($algorithmName !== 'argon2id') {
            throw PasswordException::notArgon2id($algorithmName);
        }
    }

    /**
     * Argon2idパスワードの値を取得する。
     *
     * @return string Argon2idパスワードの値
     *
     * @see PasswordValueObject::value
     * @see AbstractValueObject::value
     */
    public function value(): string
    {
        return $this->value;
    }
}
