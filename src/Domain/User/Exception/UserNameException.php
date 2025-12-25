<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

use App\Domain\Shared\Exception\Trait\TruncateTrait;
use App\Domain\Shared\Exception\ValueObjectException;

/**
 * ユーザー名の値オブジェクトに関する例外クラス
 */
class UserNameException extends ValueObjectException
{
    /** 例外メッセージの一部を短縮するためのトレイト */
    use TruncateTrait;

    /**
     * ユーザー名が空の場合の例外を生成する。
     */
    public static function empty(): self
    {
        return new self('User name is empty.');
    }

    /**
     * ユーザー名が最大文字数を超えている場合の例外を生成する。
     *
     * @param string $name      ユーザー名
     * @param int    $maxLength ユーザー名の最大文字数
     *
     * @see TruncateTrait::truncate
     */
    public static function tooLong(string $name, int $maxLength): self
    {
        return new self(sprintf(
            "User name '%s' exceeds maximum length: %d characters (actual: %d).",
            self::truncate($name),
            $maxLength,
            mb_strlen($name, 'UTF-8'),
        ));
    }

    /**
     * ユーザー名が無効な形式の場合の例外を生成する。
     *
     * @param string $name ユーザー名
     *
     * @see TruncateTrait::truncate
     */
    public static function invalidFormat(string $name): self
    {
        return new self(sprintf(
            "User name '%s' is invalid format.",
            self::truncate($name),
        ));
    }
}
