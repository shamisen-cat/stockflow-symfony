<?php

declare(strict_types=1);

namespace App\Domain\User\Interface;

/**
 * メールの値オブジェクトインターフェース
 */
interface EmailValueObject
{
    /**
     * メールの値を取得する。
     *
     * @return string メールの値
     */
    public function value(): string;

    /**
     * メールの値オブジェクトの等価性を比較する。
     *
     * @param self $other メールの値オブジェクト
     */
    public function isSameValue(self $other): bool;
}
