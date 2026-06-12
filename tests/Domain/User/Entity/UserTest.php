<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\Entity;

use App\Domain\User\Entity\User;
use App\Domain\User\Exception\UserAlreadyDeletedException;
use App\Domain\User\ValueObject\Email\Email;
use App\Domain\User\ValueObject\Password\HashedPassword;
use App\Tests\Support\UserTestFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Uid\Uuid;

final class UserTest extends TestCase
{
    private MockClock $clock;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $baseDateTime = new \DateTimeImmutable('2024-01-01 10:00:00');
        $this->clock = new MockClock($baseDateTime);
    }

    private function nowAfter(int $seconds = 3600): \DateTimeImmutable
    {
        $this->clock->sleep($seconds);

        return $this->clock->now();
    }

    #[Test]
    public function createReturnsUserWithProvidedValues(): void
    {
        $id = Uuid::fromString('00000000-0000-7000-8000-000000000001');
        $email = Email::of('test@example.com');
        $password = HashedPassword::of('$argon2id$v=19$m=65536,t=4,p=1$dummy-argon2id-hash');

        $user = User::create(
            id: $id,
            email: $email,
            password: $password,
        );

        self::assertSame($id, $user->id);
        self::assertTrue($email->equals($user->email));
        self::assertTrue($password->equals($user->password));
    }

    #[Test]
    public function isDeletedReturnsFalseForNewUser(): void
    {
        $user = UserTestFactory::create();

        self::assertNull($user->deletedAt);
        self::assertFalse($user->isDeleted());
    }

    #[Test]
    public function softDeleteSetsDeletedAt(): void
    {
        $user = UserTestFactory::create();
        $deletedAt = $this->clock->now();

        $user->softDelete($deletedAt);

        self::assertSame($deletedAt, $user->deletedAt);
        self::assertTrue($user->isDeleted());
    }

    #[Test]
    public function softDeleteThrowsWhenUserIsAlreadyDeleted(): void
    {
        $message = 'User is already deleted.';

        $user = UserTestFactory::create();
        $deletedAt = $this->clock->now();
        $user->softDelete($deletedAt);

        $secondDeletedAt = $this->nowAfter();

        try {
            $user->softDelete($secondDeletedAt);
            self::fail('Expected UserAlreadyDeletedException was not thrown.');
        } catch (UserAlreadyDeletedException $e) {
            self::assertSame($message, $e->getMessage());
            self::assertSame($user->id, $e->userId);
        }
    }

    #[Test]
    public function getUserIdentifierReturnsIdAsRfc4122(): void
    {
        $id = Uuid::fromString('00000000-0000-7000-8000-000000000001');
        $user = UserTestFactory::create(id: $id);

        self::assertSame($id->toRfc4122(), $user->getUserIdentifier());
    }

    #[Test]
    public function getRolesReturnsRoleUser(): void
    {
        $user = UserTestFactory::create();

        self::assertSame(['ROLE_USER'], $user->getRoles());
    }

    #[Test]
    public function getPasswordReturnsHashedPasswordValue(): void
    {
        $password = HashedPassword::of('$argon2id$v=19$m=65536,t=4,p=1$dummy-argon2id-hash');
        $user = UserTestFactory::create(password: $password);

        self::assertSame($password->value(), $user->getPassword());
    }
}
