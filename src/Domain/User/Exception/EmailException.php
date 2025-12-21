<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

use App\Domain\Shared\Exception\Trait\TruncateTrait;
use App\Domain\Shared\Exception\ValueObjectException;

/**
 * メールの値オブジェクトに関する例外クラス
 */
class EmailException extends ValueObjectException
{
    /** 例外メッセージの一部を短縮するためのトレイト */
    use TruncateTrait;

    /**
     * メールが空の場合の例外を生成する。
     */
    public static function empty(): self
    {
        return new self('Email is empty.');
    }

    /**
     * メールが最大文字数を超えている場合の例外を生成する。
     *
     * @param string $email     メール
     * @param int    $maxLength メールの最大文字数
     *
     * @see TruncateTrait::truncate
     */
    public static function tooLong(string $email, int $maxLength): self
    {
        return new self(sprintf(
            "Email '%s' exceeds maximum length: %d characters (actual: %d).",
            self::truncate($email),
            $maxLength,
            mb_strlen($email, 'UTF-8'),
        ));
    }

    /**
     * メールが無効な形式の場合の例外を生成する。
     *
     * @param string $email メール
     *
     * @see TruncateTrait::truncate
     */
    public static function invalidFormat(string $email): self
    {
        return new self(sprintf(
            "Email '%s' is invalid format.",
            self::truncate($email),
        ));
    }
}
