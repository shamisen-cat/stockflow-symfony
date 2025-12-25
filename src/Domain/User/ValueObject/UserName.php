<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject;

use App\Domain\Shared\ValueObject\AbstractValueObject;
use App\Domain\User\Exception\UserNameException;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * ユーザー名の値オブジェクト
 *
 * @extends AbstractValueObject<string|null>
 */
#[ORM\Embeddable]
final readonly class UserName extends AbstractValueObject
{
    /** データベースの最大文字数 */
    private const int MAX_LENGTH = 255;
    /** 前後に空白文字が含まれている正規表現パターン */
    private const string SURROUNDING_WHITESPACE_PATTERN = "/^[\s　]+|[\s　]+$/u";
    /** 無効な制御文字が含まれている正規表現パターン */
    private const string INVALID_CONTROL_CHARACTERS_PATTERN = "/[\t\n\r]/u";

    /** ユーザー名 */
    #[ORM\Column(
        name: 'name',
        type: Types::STRING,
        length: self::MAX_LENGTH,
        nullable: true,
    )]
    private readonly ?string $value;

    /**
     * @param string|null $name ユーザー名、またはnull
     */
    private function __construct(?string $name)
    {
        if ($name !== null) {
            $this->assertValue($name);
        }

        $this->value = $name;
    }

    /**
     * ユーザー名から値オブジェクトを生成する。
     *
     * @param string $name ユーザー名
     *
     * @return self ユーザー名の値オブジェクト
     */
    public static function of(string $name): self
    {
        return new self($name);
    }

    /**
     * ユーザー名の設定がない値オブジェクトを生成する。
     *
     * @return self ユーザー名の設定がない値オブジェクト
     */
    public static function none(): self
    {
        return new self(null);
    }

    /**
     * ユーザー名を検証する。
     *
     * 例外理由：
     * - ユーザー名が空
     * - ユーザー名が最大文字数を超えている
     * - ユーザー名が無効な形式
     *
     * @param string $name ユーザー名
     *
     * @throws UserNameException
     */
    private function assertValue(string $name): void
    {
        if ($name === '') {
            throw UserNameException::empty();
        }

        if (mb_strlen($name, 'UTF-8') > self::MAX_LENGTH) {
            throw UserNameException::tooLong($name, self::MAX_LENGTH);
        }

        if (preg_match(self::SURROUNDING_WHITESPACE_PATTERN, $name) === 1) {
            throw UserNameException::invalidFormat($name);
        }
        if (preg_match(self::INVALID_CONTROL_CHARACTERS_PATTERN, $name) === 1) {
            throw UserNameException::invalidFormat($name);
        }
    }

    /**
     * ユーザー名の値を取得する。
     *
     * @return string|null ユーザー名の値、またはnull
     *
     * @see AbstractValueObject::value
     */
    public function value(): ?string
    {
        return $this->value;
    }

    /**
     * ユーザー名の設定を確認する。
     */
    public function isNone(): bool
    {
        return $this->value() === null;
    }
}
