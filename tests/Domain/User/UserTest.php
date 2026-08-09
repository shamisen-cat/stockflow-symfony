<?php

declare(strict_types=1);

namespace App\Tests\Domain\User;

use App\Domain\User\Email\Email;
use App\Domain\User\Exception\UserAlreadyDeletedException;
use App\Domain\User\Exception\UserAlreadyDisabledException;
use App\Domain\User\Exception\UserAlreadySuspendedException;
use App\Domain\User\Exception\UserDeletedException;
use App\Domain\User\Exception\UserNotDisabledException;
use App\Domain\User\Exception\UserNotSuspendedException;
use App\Domain\User\Exception\UserSuspendedException;
use App\Domain\User\Password\HashedPassword;
use App\Domain\User\User;
use App\Domain\User\UserStatus;
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
        self::assertSame(UserStatus::Active, $user->status);

        self::assertSame($createdAt, $user->createdAt);
        self::assertSame($createdAt, $user->updatedAt);

        self::assertNull($user->disabledAt);
        self::assertNull($user->suspendedAt);
        self::assertNull($user->deletedAt);

        self::assertFalse($user->isDisabled());
        self::assertFalse($user->isSuspended());
        self::assertFalse($user->isDeleted());
    }

    #[Test]
    public function disableSetsDisabledAt(): void
    {
        $user = UserTestFactory::create(createdAt: $this->now());
        $disabledAt = $this->nowAfter();

        $user->disable($disabledAt);

        self::assertSame($disabledAt, $user->disabledAt);
        self::assertSame($disabledAt, $user->updatedAt);

        self::assertSame(UserStatus::Disabled, $user->status);
        self::assertTrue($user->isDisabled());
    }

    #[Test]
    public function disableThrowsWhenUserIsDeleted(): void
    {
        $message = 'User is deleted.';

        $user = UserTestFactory::create(createdAt: $this->now());

        $deletedAt = $this->nowAfter();
        $user->softDelete($deletedAt);

        try {
            $user->disable($this->nowAfter());
            self::fail('Expected UserDeletedException was not thrown.');
        } catch (UserDeletedException $e) {
            self::assertSame($message, $e->getMessage());
            self::assertSame($user->id, $e->userId);
        }
    }

    #[Test]
    public function disableThrowsWhenUserIsSuspended(): void
    {
        $message = 'User is suspended.';

        $user = UserTestFactory::create(createdAt: $this->now());

        $suspendedAt = $this->nowAfter();
        $user->suspend($suspendedAt);

        try {
            $user->disable($this->nowAfter());
            self::fail('Expected UserSuspendedException was not thrown.');
        } catch (UserSuspendedException $e) {
            self::assertSame($message, $e->getMessage());
            self::assertSame($user->id, $e->userId);
            self::assertNull($user->disabledAt);
            self::assertSame($suspendedAt, $user->suspendedAt);
            self::assertSame($suspendedAt, $user->updatedAt);
        }
    }

    #[Test]
    public function disableThrowsWhenUserIsAlreadyDisabled(): void
    {
        $message = 'User is already disabled.';

        $user = UserTestFactory::create(createdAt: $this->now());

        $disabledAt = $this->nowAfter();
        $user->disable($disabledAt);

        try {
            $user->disable($this->nowAfter());
            self::fail('Expected UserAlreadyDisabledException was not thrown.');
        } catch (UserAlreadyDisabledException $e) {
            self::assertSame($message, $e->getMessage());
            self::assertSame($user->id, $e->userId);
            self::assertSame($disabledAt, $user->disabledAt);
            self::assertSame($disabledAt, $user->updatedAt);
        }
    }

    #[Test]
    public function enableClearsDisabledAt(): void
    {
        $user = UserTestFactory::create(createdAt: $this->now());

        $user->disable($this->nowAfter());
        $enabledAt = $this->nowAfter();

        $user->enable($enabledAt);

        self::assertNull($user->disabledAt);
        self::assertSame($enabledAt, $user->updatedAt);

        self::assertSame(UserStatus::Active, $user->status);
        self::assertFalse($user->isDisabled());
    }

    #[Test]
    public function enableThrowsWhenUserIsDeleted(): void
    {
        $message = 'User is deleted.';

        $user = UserTestFactory::create(createdAt: $this->now());

        $deletedAt = $this->nowAfter();
        $user->softDelete($deletedAt);

        try {
            $user->enable($this->nowAfter());
            self::fail('Expected UserDeletedException was not thrown.');
        } catch (UserDeletedException $e) {
            self::assertSame($message, $e->getMessage());
            self::assertSame($user->id, $e->userId);
        }
    }

    #[Test]
    public function enableThrowsWhenUserIsSuspended(): void
    {
        $message = 'User is suspended.';

        $user = UserTestFactory::create(createdAt: $this->now());

        $at = $this->nowAfter();
        $user->disable($at);
        $user->suspend($at);

        try {
            $user->enable($this->nowAfter());
            self::fail('Expected UserSuspendedException was not thrown.');
        } catch (UserSuspendedException $e) {
            self::assertSame($message, $e->getMessage());
            self::assertSame($user->id, $e->userId);
            self::assertSame($at, $user->disabledAt);
            self::assertSame($at, $user->suspendedAt);
            self::assertSame($at, $user->updatedAt);
        }
    }

    #[Test]
    public function enableThrowsWhenUserIsNotDisabled(): void
    {
        $message = 'User is not disabled.';

        $createdAt = $this->now();
        $user = UserTestFactory::create(createdAt: $createdAt);

        try {
            $user->enable($this->nowAfter());
            self::fail('Expected UserNotDisabledException was not thrown.');
        } catch (UserNotDisabledException $e) {
            self::assertSame($message, $e->getMessage());
            self::assertSame($user->id, $e->userId);
            self::assertNull($user->disabledAt);
            self::assertSame($createdAt, $user->updatedAt);
        }
    }

    #[Test]
    public function suspendSetsSuspendedAt(): void
    {
        $user = UserTestFactory::create(createdAt: $this->now());
        $suspendedAt = $this->nowAfter();

        $user->suspend($suspendedAt);

        self::assertSame($suspendedAt, $user->suspendedAt);
        self::assertSame($suspendedAt, $user->updatedAt);

        self::assertSame(UserStatus::Suspended, $user->status);
        self::assertTrue($user->isSuspended());
    }

    #[Test]
    public function suspendThrowsWhenUserIsDeleted(): void
    {
        $message = 'User is deleted.';

        $user = UserTestFactory::create(createdAt: $this->now());
        $deletedAt = $this->nowAfter();
        $user->softDelete($deletedAt);

        try {
            $user->suspend($this->nowAfter());
            self::fail('Expected UserDeletedException was not thrown.');
        } catch (UserDeletedException $e) {
            self::assertSame($message, $e->getMessage());
            self::assertSame($user->id, $e->userId);
        }
    }

    #[Test]
    public function suspendThrowsWhenUserIsAlreadySuspended(): void
    {
        $message = 'User is already suspended.';

        $user = UserTestFactory::create(createdAt: $this->now());

        $suspendedAt = $this->nowAfter();
        $user->suspend($suspendedAt);

        try {
            $user->suspend($this->nowAfter());
            self::fail('Expected UserAlreadySuspendedException was not thrown.');
        } catch (UserAlreadySuspendedException $e) {
            self::assertSame($message, $e->getMessage());
            self::assertSame($user->id, $e->userId);
            self::assertSame($suspendedAt, $user->suspendedAt);
            self::assertSame($suspendedAt, $user->updatedAt);
        }
    }

    #[Test]
    public function unsuspendClearsSuspendedAt(): void
    {
        $user = UserTestFactory::create(createdAt: $this->now());

        $user->suspend($this->nowAfter());

        $unsuspendedAt = $this->nowAfter();
        $user->unsuspend($unsuspendedAt);

        self::assertNull($user->suspendedAt);
        self::assertSame($unsuspendedAt, $user->updatedAt);

        self::assertSame(UserStatus::Active, $user->status);
        self::assertFalse($user->isSuspended());
    }

    #[Test]
    public function unsuspendThrowsWhenUserIsDeleted(): void
    {
        $message = 'User is deleted.';

        $user = UserTestFactory::create(createdAt: $this->now());

        $deletedAt = $this->nowAfter();
        $user->softDelete($deletedAt);

        try {
            $user->unsuspend($this->nowAfter());
            self::fail('Expected UserDeletedException was not thrown.');
        } catch (UserDeletedException $e) {
            self::assertSame($message, $e->getMessage());
            self::assertSame($user->id, $e->userId);
        }
    }

    #[Test]
    public function unsuspendThrowsWhenUserIsNotSuspended(): void
    {
        $message = 'User is not suspended.';

        $createdAt = $this->now();
        $user = UserTestFactory::create(createdAt: $createdAt);

        try {
            $user->unsuspend($this->nowAfter());
            self::fail('Expected UserNotSuspendedException was not thrown.');
        } catch (UserNotSuspendedException $e) {
            self::assertSame($message, $e->getMessage());
            self::assertSame($user->id, $e->userId);
            self::assertNull($user->suspendedAt);
            self::assertSame($createdAt, $user->updatedAt);
        }
    }

    #[Test]
    public function softDeleteSetsDeletedAt(): void
    {
        $user = UserTestFactory::create(createdAt: $this->now());

        $deletedAt = $this->nowAfter();
        $user->softDelete($deletedAt);

        self::assertSame($deletedAt, $user->deletedAt);
        self::assertSame($deletedAt, $user->updatedAt);

        self::assertSame(UserStatus::Deleted, $user->status);
        self::assertTrue($user->isDeleted());
    }

    #[Test]
    public function statusPrefersSuspendedOverDisabled(): void
    {
        $user = UserTestFactory::create(createdAt: $this->now());

        $at = $this->nowAfter();
        $user->disable($at);
        $user->suspend($at);

        self::assertSame(UserStatus::Suspended, $user->status);
    }

    #[Test]
    public function statusPrefersDeletedOverOtherStates(): void
    {
        $user = UserTestFactory::create(createdAt: $this->now());

        $at = $this->nowAfter();
        $user->disable($at);
        $user->suspend($at);
        $user->softDelete($at);

        self::assertSame(UserStatus::Deleted, $user->status);
    }

    #[Test]
    public function unsuspendRestoresDisabledStatusWhenStillDisabled(): void
    {
        $user = UserTestFactory::create(createdAt: $this->now());

        $at = $this->nowAfter();
        $user->disable($at);
        $user->suspend($at);

        $user->unsuspend($this->nowAfter());

        self::assertNull($user->suspendedAt);

        self::assertSame(UserStatus::Disabled, $user->status);
        self::assertTrue($user->isDisabled());
    }

    #[Test]
    public function softDeleteThrowsWhenUserIsAlreadyDeleted(): void
    {
        $message = 'User is already deleted.';

        $user = UserTestFactory::create(createdAt: $this->now());

        $deletedAt = $this->nowAfter();
        $user->softDelete($deletedAt);

        try {
            $user->softDelete($this->nowAfter());
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
