<?php

declare(strict_types=1);

namespace App\Tests\Support;

use Symfony\Component\Clock\MockClock;

trait MockClockTestTrait
{
    private MockClock $clock;

    private function initializeClock(?string $baseDateTime = null): void
    {
        $this->clock = new MockClock(
            new \DateTimeImmutable($baseDateTime ?? TestClock::BASE_DATETIME),
        );
    }

    private function now(): \DateTimeImmutable
    {
        return $this->clock->now();
    }

    private function nowAfter(int $seconds = 3600): \DateTimeImmutable
    {
        $this->clock->sleep($seconds);

        return $this->clock->now();
    }
}
