<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\Exception;

use App\Domain\User\Exception\UserAlreadyDeletedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class UserAlreadyDeletedExceptionTest extends TestCase
{
    #[Test]
    public function forUserReturnsExpectedProperties(): void
    {
        $userId = Uuid::fromString('00000000-0000-7000-8000-000000000001');

        $exception = UserAlreadyDeletedException::forUser($userId);

        self::assertSame('User is already deleted.', $exception->getMessage());
        self::assertSame($userId, $exception->userId);
    }
}
