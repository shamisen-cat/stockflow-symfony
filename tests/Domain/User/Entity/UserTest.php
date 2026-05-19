<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\Entity;

use App\Domain\User\Entity\User;
use App\Domain\User\Exception\UserAlreadyDeletedException;
use App\Domain\User\ValueObject\Email\Email;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\Uid\Uuid;

final class UserTest extends TestCase
{
    private const string TEST_ID    = '00000000-0000-7000-8000-000000000001';
    private const string TEST_EMAIL = 'test@example.com';

    private const string BASE_DATE_TIME        = '2024-01-01 10:00:00';
    private const int    DEFAULT_SLEEP_SECONDS = 3600;

    private MockClock $clock;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $baseDateTime = new \DateTimeImmutable(self::BASE_DATE_TIME);
        $this->clock  = new MockClock($baseDateTime);
    }

    private function nowAfter(int $seconds = self::DEFAULT_SLEEP_SECONDS): \DateTimeImmutable
    {
        $this->clock->sleep($seconds);

        return $this->clock->now();
    }

    #[Test]
    public function constructorSetsProvidedValues(): void
    {
        $id    = Uuid::fromString(self::TEST_ID);
        $email = Email::of(self::TEST_EMAIL);

        $user = new User(
            id: $id,
            email: $email,
        );

        self::assertSame($id, $user->id);
        self::assertTrue($email->equals($user->email));
    }

    #[Test]
    public function isDeletedReturnsFalseForNewUser(): void
    {
        $user = $this->createUser();

        self::assertNull($user->deletedAt);
        self::assertFalse($user->isDeleted());
    }

    #[Test]
    public function softDeleteSetsDeletedAt(): void
    {
        $user      = $this->createUser();
        $deletedAt = $this->clock->now();

        $user->softDelete($deletedAt);

        self::assertSame($deletedAt, $user->deletedAt);
        self::assertTrue($user->isDeleted());
    }

    #[Test]
    public function softDeleteThrowsWhenUserIsAlreadyDeleted(): void
    {
        $user      = $this->createUser();
        $deletedAt = $this->clock->now();

        $user->softDelete($deletedAt);

        $secondDeletedAt = $this->nowAfter();

        $this->expectException(UserAlreadyDeletedException::class);

        $user->softDelete($secondDeletedAt);
    }

    #[Test]
    public function getUserIdentifierReturnsIdAsRfc4122(): void
    {
        $id    = Uuid::fromString(self::TEST_ID);
        $email = Email::of(self::TEST_EMAIL);

        $user = new User(
            id: $id,
            email: $email,
        );

        self::assertSame(self::TEST_ID, $user->getUserIdentifier());
    }

    #[Test]
    public function getRolesReturnsRoleUser(): void
    {
        $user = $this->createUser();

        self::assertSame(['ROLE_USER'], $user->getRoles());
    }

    #[Test]
    public function getPasswordReturnsEmptyString(): void
    {
        $user = $this->createUser();

        self::assertSame('', $user->getPassword());
    }

    private function createUser(
        ?Uuid $id = null,
        ?Email $email = null,
    ): User {
        return new User(
            id: $id       ?? Uuid::fromString(self::TEST_ID),
            email: $email ?? Email::of(self::TEST_EMAIL),
        );
    }
}
