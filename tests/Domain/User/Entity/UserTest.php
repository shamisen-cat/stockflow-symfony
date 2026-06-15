<?php

declare(strict_types=1);

namespace App\Tests\Domain\User\Entity;

use App\Domain\User\Entity\User;
use App\Domain\User\Exception\UserAlreadyDeletedException;
use App\Domain\User\ValueObject\Email\Email;
use App\Domain\User\ValueObject\Password\HashedPassword;
use App\Tests\Support\MockClockTestTrait;
use App\Tests\Support\UserTestFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

final class UserTest extends TestCase
{
    use MockClockTestTrait;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeClock();
    }

    #[Test]
    public function createReturnsUserWithProvidedValues(): void
    {
        $id = Uuid::fromString('00000000-0000-7000-8000-000000000001');
        $email = Email::of('test@example.com');
        $password = HashedPassword::of('$argon2id$v=19$m=65536,t=4,p=1$dummy-argon2id-hash');
        $createdAt = $this->now();

        $user = User::create(
            id: $id,
            email: $email,
            password: $password,
            createdAt: $createdAt,
        );

        self::assertSame($id, $user->id);
        self::assertTrue($email->equals($user->email));
        self::assertTrue($password->equals($user->password));
        self::assertSame($createdAt, $user->createdAt);
        self::assertSame($createdAt, $user->updatedAt);

        self::assertNull($user->deletedAt);
        self::assertFalse($user->isDeleted());
    }

    #[Test]
    public function softDeleteSetsDeletedAt(): void
    {
        $user = UserTestFactory::create(createdAt: $this->now());
        $deletedAt = $this->nowAfter();

        $user->softDelete($deletedAt);

        self::assertSame($deletedAt, $user->deletedAt);
        self::assertSame($deletedAt, $user->updatedAt);
        self::assertTrue($user->isDeleted());
    }

    #[Test]
    public function softDeleteThrowsWhenUserIsAlreadyDeleted(): void
    {
        $message = 'User is already deleted.';

        $user = UserTestFactory::create(createdAt: $this->now());
        $deletedAt = $this->nowAfter();
        $user->softDelete($deletedAt);

        $secondDeletedAt = $this->nowAfter();

        try {
            $user->softDelete($secondDeletedAt);
            self::fail('Expected UserAlreadyDeletedException was not thrown.');
        } catch (UserAlreadyDeletedException $e) {
            self::assertSame($message, $e->getMessage());
            self::assertSame($user->id, $e->userId);
            self::assertSame($deletedAt, $user->deletedAt);
            self::assertSame($deletedAt, $user->updatedAt);
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
