<?php

declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;

final class SmokeTest extends TestCase
{
    public function testTrueIsTrue(): void
    {
        // @phpstan-ignore-next-line
        self::assertTrue(true);
    }
}
