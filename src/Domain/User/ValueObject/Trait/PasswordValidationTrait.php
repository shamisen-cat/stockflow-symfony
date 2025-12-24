<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject\Trait;

use App\Domain\User\Exception\PasswordException;

/**
 * パスワードの値オブジェクトを検証するためのトレイト
 */
trait PasswordValidationTrait
{
    /** データベースの最小文字数 */
    private const int MIN_LENGTH = 12;
    /** データベースの最大文字数 */
    private const int MAX_LENGTH = 255;

    /**
     * パスワードを検証する。
     *
     * 例外理由：
     * - パスワードが空
     * - パスワードが最小文字数を満たしていない
     * - パスワードが最大文字数を超えている
     *
     * @param string $password パスワード
     *
     * @throws PasswordException
     */
    private function assertValue(string $password): void
    {
        if ($password === '') {
            throw PasswordException::empty();
        }

        $length = mb_strlen($password, 'UTF-8');
        if ($length < self::MIN_LENGTH) {
            throw PasswordException::tooShort($length, self::MIN_LENGTH);
        }
        if ($length > self::MAX_LENGTH) {
            throw PasswordException::tooLong($length, self::MAX_LENGTH);
        }
    }
}
