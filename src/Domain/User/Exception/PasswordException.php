<?php

declare(strict_types=1);

namespace App\Domain\User\Exception;

use App\Domain\Shared\Exception\ValueObjectException;

/**
 * パスワードの値オブジェクトに関する例外クラス
 */
class PasswordException extends ValueObjectException
{
    /**
     * パスワードが空の場合の例外を生成する。
     */
    public static function empty(): self
    {
        return new self('Password is empty.');
    }

    /**
     * パスワードが最小文字数を満たしていない場合の例外を生成する。
     *
     * @param int $length    パスワードの文字数
     * @param int $minLength パスワードの最小文字数
     */
    public static function tooShort(int $length, int $minLength): self
    {
        return new self(sprintf(
            'Password is below minimum length: %d characters (actual: %d).',
            $minLength,
            $length,
        ));
    }

    /**
     * パスワードが最大文字数を超えている場合の例外を生成する。
     *
     * @param int $length    パスワードの文字数
     * @param int $maxLength パスワードの最大文字数
     */
    public static function tooLong(int $length, int $maxLength): self
    {
        return new self(sprintf(
            'Password exceeds maximum length: %d characters (actual: %d).',
            $maxLength,
            $length,
        ));
    }

    /**
     * パスワードがArgon2idを使用していない場合の例外を生成する。
     *
     * @param string $algorithmName ハッシュアルゴリズム名
     */
    public static function notArgon2id(string $algorithmName): self
    {
        return new self(sprintf(
            "Password algorithm is not 'Argon2id': '%s'.",
            $algorithmName,
        ));
    }
}
