<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

use App\Domain\Shared\Exception\ValueObjectException;

/**
 * 認証トークンの値オブジェクトに関する例外クラス
 */
class VerificationTokenException extends ValueObjectException
{
    /**
     * 認証トークンが空の場合の例外を生成する。
     */
    public static function empty(): self
    {
        return new self('Verification token is empty.');
    }

    /**
     * 認証トークンが最小文字数を満たしていない場合の例外を生成する。
     *
     * @param int $length    認証トークンの文字数
     * @param int $minLength 認証トークンの最小文字数
     */
    public static function tooShort(int $length, int $minLength): self
    {
        return new self(sprintf(
            'Verification token is below minimum length: %d characters (actual: %d).',
            $minLength,
            $length,
        ));
    }

    /**
     * 認証トークンが最大文字数を超えている場合の例外を生成する。
     *
     * @param int $length    認証トークンの文字数
     * @param int $maxLength 認証トークンの最大文字数
     */
    public static function tooLong(int $length, int $maxLength): self
    {
        return new self(sprintf(
            'Verification token exceeds maximum length: %d characters (actual: %d).',
            $maxLength,
            $length,
        ));
    }

    /**
     * 認証トークンが無効な形式の場合の例外を生成する。
     */
    public static function invalidFormat(): self
    {
        return new self('Verification token is invalid format.');
    }
}
