<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Shared\Uuid;

use App\Infrastructure\Shared\Uuid\UuidGenerator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\UuidV7;

final class UuidGeneratorTest extends TestCase
{
    #[Test]
    public function generateReturnsUuidV7(): void
    {
        $uuid = new UuidGenerator()->generate();

        self::assertInstanceOf(UuidV7::class, $uuid);
    }
}
