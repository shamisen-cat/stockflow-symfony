<?php

declare(strict_types=1);

namespace App\Domain\Shared\Interface;

/**
 * 日時を提供するインターフェース
 */
interface Clock
{
    /**
     * 現在日時を返す。
     *
     * @return \DateTimeImmutable 現在日時
     */
    public function now(): \DateTimeImmutable;

    /**
     * 現在日時に指定された秒数を加えた日時を返す。
     *
     * @param int $seconds 加算する秒数
     *
     * @return \DateTimeImmutable 加算後の日時
     */
    public function nowAfterSeconds(int $seconds): \DateTimeImmutable;
}
