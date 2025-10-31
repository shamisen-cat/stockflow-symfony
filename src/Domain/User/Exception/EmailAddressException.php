<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

use App\Domain\Shared\Exception\Trait\ExceptionExcerptTrait;
use App\Domain\Shared\Exception\ValueObjectException;

/**
 * メールアドレスの検証例外
 */
class EmailAddressException extends ValueObjectException
{
    /** 値の短縮表示を生成するトレイト */
    use ExceptionExcerptTrait;

    /**
     * メールアドレスが空の場合の例外を生成する。
     */
    public static function empty(): self
    {
        return new self('Email address is empty.');
    }

    /**
     * メールアドレスが最大文字数を超えている場合の例外を生成する。
     *
     * @param string $email     例外対象のメールアドレス
     * @param int    $maxLength メールアドレスの最大文字数
     */
    public static function tooLong(string $email, int $maxLength): self
    {
        return new self(sprintf(
            "Email address '%s' exceeds the maximum length of %d characters (actual: %d).",
            self::getExcerpt($email),
            $maxLength,
            mb_strlen($email),
        ));
    }

    /**
     * メールアドレスが無効なフォーマットの場合の例外を生成する。
     *
     * @param string $email 例外対象のメールアドレス
     */
    public static function invalidFormat(string $email): self
    {
        return new self(sprintf(
            "Email address '%s' has an invalid format.",
            self::getExcerpt($email),
        ));
    }
}
