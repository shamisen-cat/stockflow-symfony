<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use App\Domain\Shared\ValueObject\AbstractValueObject;
use App\Domain\User\Exception\VerificationTokenException;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * メール認証トークンの値オブジェクト
 *
 * @extends AbstractValueObject<string>
 */
#[ORM\Embeddable]
final readonly class EmailVerificationToken extends AbstractValueObject
{
    /** データベースの最小文字数 */
    private const int MIN_LENGTH = 32;
    /** データベースの最大文字数 */
    private const int MAX_LENGTH = 255;
    /** 16進数の正規表現パターン */
    private const string HEX_PATTERN = '/^[0-9a-f]+$/';

    /** メール認証トークン */
    #[ORM\Column(
        name: 'token',
        type: Types::STRING,
        length: self::MAX_LENGTH,
        unique: true,
    )]
    private readonly string $value;

    /**
     * @param string $token メール認証トークン
     */
    private function __construct(string $token)
    {
        $this->assertValue($token);
        $this->value = $token;
    }

    /**
     * メール認証トークンから値オブジェクトを生成する。
     *
     * @param string $token メール認証トークン
     *
     * @return self メール認証トークンの値オブジェクト
     */
    public static function of(string $token): self
    {
        return new self($token);
    }

    /**
     * メール認証トークンを検証する。
     *
     * 例外理由：
     * - メール認証トークンが空
     * - メール認証トークンが最小文字数を満たしていない
     * - メール認証トークンが最大文字数を超えている
     * - メール認証トークンが無効な形式
     *
     * @param string $token メール認証トークン
     *
     * @throws VerificationTokenException
     */
    private function assertValue(string $token): void
    {
        if ($token === '') {
            throw VerificationTokenException::empty();
        }

        $length = mb_strlen($token, 'UTF-8');
        if ($length < self::MIN_LENGTH) {
            throw VerificationTokenException::tooShort($length, self::MIN_LENGTH);
        }
        if ($length > self::MAX_LENGTH) {
            throw VerificationTokenException::tooLong($length, self::MAX_LENGTH);
        }

        if (!preg_match(self::HEX_PATTERN, $token)) {
            throw VerificationTokenException::invalidFormat();
        }
    }

    /**
     * メール認証トークンの値を取得する。
     *
     * @return string メール認証トークンの値
     *
     * @see AbstractValueObject::value
     */
    public function value(): string
    {
        return $this->value;
    }
}
