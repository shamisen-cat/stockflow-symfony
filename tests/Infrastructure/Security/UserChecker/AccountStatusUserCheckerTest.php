<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Security\UserChecker;

use App\Infrastructure\Security\UserChecker\AccountStatusUserChecker;
use App\Tests\Support\MockClockTestTrait;
use App\Tests\Support\UnsupportedUser;
use App\Tests\Support\UserTestFactory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

final class AccountStatusUserCheckerTest extends TestCase
{
    use MockClockTestTrait;

    private AccountStatusUserChecker $userChecker;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->initializeClock();

        $this->userChecker = new AccountStatusUserChecker();
    }

    #[Test]
    public function checkPreAuthAllowsActiveUser(): void
    {
        $user = UserTestFactory::create();

        $this->expectNotToPerformAssertions();

        $this->userChecker->checkPreAuth($user);
    }

    #[Test]
    public function checkPreAuthThrowsForDisabledUser(): void
    {
        $user = UserTestFactory::create();
        $user->disable($this->now());

        try {
            $this->userChecker->checkPreAuth($user);
            self::fail('Expected DisabledException was not thrown.');
        } catch (DisabledException $e) {
            self::assertSame($user, $e->getUser());
        }
    }

    #[Test]
    public function checkPreAuthThrowsForSuspendedUser(): void
    {
        $user = UserTestFactory::create();
        $user->suspend($this->now());

        try {
            $this->userChecker->checkPreAuth($user);
            self::fail('Expected LockedException was not thrown.');
        } catch (LockedException $e) {
            self::assertSame($user, $e->getUser());
        }
    }

    #[Test]
    public function checkPreAuthPrioritizesDisabledState(): void
    {
        $user = UserTestFactory::create();
        $user->disable($this->now());
        $user->suspend($this->now());

        $this->expectException(DisabledException::class);

        $this->userChecker->checkPreAuth($user);
    }

    #[Test]
    public function checkPreAuthThrowsForUnsupportedUser(): void
    {
        $unsupportedUser = new UnsupportedUser();

        $message = sprintf(
            'User class "%s" is not supported.',
            get_debug_type($unsupportedUser),
        );

        try {
            $this->userChecker->checkPreAuth($unsupportedUser);
            self::fail('Expected UnsupportedUserException was not thrown.');
        } catch (UnsupportedUserException $e) {
            self::assertSame($message, $e->getMessage());
        }
    }

    #[Test]
    public function checkPostAuthAllowsUser(): void
    {
        $user = UserTestFactory::create();

        $this->expectNotToPerformAssertions();

        $this->userChecker->checkPostAuth($user);
    }

    #[Test]
    public function checkPostAuthThrowsForUnsupportedUser(): void
    {
        $unsupportedUser = new UnsupportedUser();

        $message = sprintf(
            'User class "%s" is not supported.',
            get_debug_type($unsupportedUser),
        );

        try {
            $this->userChecker->checkPostAuth($unsupportedUser);
            self::fail('Expected UnsupportedUserException was not thrown.');
        } catch (UnsupportedUserException $e) {
            self::assertSame($message, $e->getMessage());
        }
    }
}
