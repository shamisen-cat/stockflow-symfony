<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\Exception;

use App\Domain\User\Exception\UserAlreadyDeletedException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class UserAlreadyDeletedExceptionTest extends TestCase
{
    private const string TEST_ID = '00000000-0000-7000-8000-000000000001';

    #[Test]
    public function forUserReturnsExpectedProperties(): void
    {
        $userId  = Uuid::fromString(self::TEST_ID);
        $message = 'User is already deleted.';

        $exception = UserAlreadyDeletedException::forUser($userId);

        self::assertSame($message, $exception->getMessage());
        self::assertSame(self::TEST_ID, $exception->userId->toRfc4122());
    }
}
