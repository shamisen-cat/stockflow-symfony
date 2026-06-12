<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Security\UserProvider;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Domain\User\ValueObject\Email\Email;
use App\Infrastructure\Security\UserProvider\EmailUserProvider;
use App\Tests\Support\UserTestFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

final class EmailUserProviderTest extends TestCase
{
    #[Test]
    public function loadUserByIdentifierReturnsActiveUser(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $user = UserTestFactory::create();
        $userRepository
            ->expects(self::once())
            ->method('findActiveByEmail')
            ->with($user->email->value())
            ->willReturn($user);

        $provider = new EmailUserProvider($userRepository);
        $loadedUser = $provider->loadUserByIdentifier($user->email->value());

        self::assertSame($user, $loadedUser);
    }

    #[Test]
    public function loadUserByIdentifierThrowsWhenActiveUserNotFound(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $email = 'unknown@example.com';
        $userRepository
            ->expects(self::once())
            ->method('findActiveByEmail')
            ->with($email)
            ->willReturn(null);

        $provider = new EmailUserProvider($userRepository);

        try {
            $provider->loadUserByIdentifier($email);
            self::fail('Expected UserNotFoundException was not thrown.');
        } catch (UserNotFoundException $e) {
            self::assertSame($email, $e->getUserIdentifier());
        }
    }

    #[Test]
    public function refreshUserReturnsRefreshedUser(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $user = UserTestFactory::create();
        $refreshedUser = UserTestFactory::create(
            id: $user->id,
            email: Email::of('refreshed@example.com'),
        );
        $userRepository
            ->expects(self::once())
            ->method('findActiveById')
            ->with($user->id)
            ->willReturn($refreshedUser);

        $provider = new EmailUserProvider($userRepository);
        $result = $provider->refreshUser($user);

        self::assertSame($refreshedUser, $result);
    }

    #[Test]
    public function refreshUserThrowsWhenActiveUserNotFound(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $user = UserTestFactory::create();
        $userRepository
            ->expects(self::once())
            ->method('findActiveById')
            ->with($user->id)
            ->willReturn(null);

        $provider = new EmailUserProvider($userRepository);

        try {
            $provider->refreshUser($user);
            self::fail('Expected UserNotFoundException was not thrown.');
        } catch (UserNotFoundException $e) {
            self::assertSame($user->getUserIdentifier(), $e->getUserIdentifier());
        }
    }

    #[Test]
    public function refreshUserThrowsForUnsupportedUser(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository
            ->expects(self::never())
            ->method('findActiveById');

        $provider = new EmailUserProvider($userRepository);

        $unsupportedUser = new class implements UserInterface {
            #[\Override]
            public function getUserIdentifier(): string
            {
                return 'unsupported-user';
            }

            #[\Override]
            public function getRoles(): array
            {
                return [];
            }

            #[\Override]
            public function eraseCredentials(): void
            {
            }
        };

        $message = sprintf('User class "%s" is not supported.', get_debug_type($unsupportedUser));

        try {
            $provider->refreshUser($unsupportedUser);
            self::fail('Expected UnsupportedUserException was not thrown.');
        } catch (UnsupportedUserException $e) {
            self::assertSame($message, $e->getMessage());
        }
    }

    #[Test]
    public function supportsClassReturnsTrueForUser(): void
    {
        $userRepository = self::createStub(UserRepositoryInterface::class);
        $provider = new EmailUserProvider($userRepository);

        self::assertTrue($provider->supportsClass(User::class));
    }

    #[Test]
    public function supportsClassReturnsFalseForUserInterface(): void
    {
        $userRepository = self::createStub(UserRepositoryInterface::class);
        $provider = new EmailUserProvider($userRepository);

        self::assertFalse($provider->supportsClass(UserInterface::class));
    }
}
