<?php

declare(strict_types=1);

namespace App\Domain\User\Interface;

/**
 * パスワードの値オブジェクトインターフェース
 */
interface PasswordValueObject
{
    /**
     * パスワードの値を取得する。
     *
     * @return string パスワードの値
     */
    public function value(): string;
}
