<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject\Trait;

use App\Domain\User\Exception\EmailException;

/**
 * メールの値オブジェクトを検証するためのトレイト
 */
trait EmailValidationTrait
{
    /** データベースの最大文字数 */
    private const int MAX_LENGTH = 255;

    /**
     * メールを検証する。
     *
     * 例外理由：
     * - メールが空
     * - メールが最大文字数を超えている
     * - メールが無効な形式
     *
     * @param string $email メール
     *
     * @throws EmailException
     */
    private function assertValue(string $email): void
    {
        if ($email === '') {
            throw EmailException::empty();
        }

        if (mb_strlen($email, 'UTF-8') > self::MAX_LENGTH) {
            throw EmailException::tooLong($email, self::MAX_LENGTH);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw EmailException::invalidFormat($email);
        }
    }
}
