<?php

declare(strict_types=1);

namespace App\Domain\User\ValueObject\Trait;

use App\Domain\User\Exception\EmailAddressException;

/**
 * メールアドレスの値を検証するトレイト
 */
trait EmailAddressValidationTrait
{
    /** メールアドレスの最大文字数 */
    private const MAX_LENGTH = 255;

    /**
     * メールアドレスの値を検証する。
     *
     * @param string $email 検証対象のメールアドレス
     *
     * @throws EmailAddressException::empty         メールアドレスが空
     * @throws EmailAddressException::tooLong       メールアドレスが最大文字数を超えている
     * @throws EmailAddressException::invalidFormat メールアドレスが無効なフォーマット
     */
    private function assertValue(string $email): void
    {
        if ($email === '') {
            throw EmailAddressException::empty();
        }

        if (mb_strlen($email) > self::MAX_LENGTH) {
            throw EmailAddressException::tooLong($email, self::MAX_LENGTH);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw EmailAddressException::invalidFormat($email);
        }
    }
}
