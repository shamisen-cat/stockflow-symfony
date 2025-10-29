<?php

declare(strict_types=1);

namespace App\Domain\Shared\Contract;

/**
 * 現在の日時を取得するインタフェース
 */
interface ClockInterface
{
    /**
     * 現在の日時を取得する。
     *
     * @return \DateTimeImmutable 現在の日時
     */
    public function now(): \DateTimeImmutable;
}
