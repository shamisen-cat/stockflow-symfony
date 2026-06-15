<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Domain\User\Entity\User;
use App\Domain\User\ValueObject\Email\Email;
use App\Domain\User\ValueObject\Password\HashedPassword;
use Symfony\Component\Uid\Uuid;

final class UserTestFactory
{
    private const string DEFAULT_ID = '00000000-0000-7000-8000-000000000001';
    private const string DEFAULT_EMAIL = 'test@example.com';
    private const string DEFAULT_PASSWORD = '$argon2id$v=19$m=65536,t=4,p=1$dummy-argon2id-hash';

    public static function create(
        ?Uuid $id = null,
        ?Email $email = null,
        ?HashedPassword $password = null,
        ?\DateTimeImmutable $createdAt = null,
    ): User {
        return User::create(
            id: $id ?? Uuid::fromString(self::DEFAULT_ID),
            email: $email ?? Email::of(self::DEFAULT_EMAIL),
            password: $password ?? HashedPassword::of(self::DEFAULT_PASSWORD),
            createdAt: $createdAt ?? new \DateTimeImmutable(TestClock::BASE_DATETIME),
        );
    }
}
