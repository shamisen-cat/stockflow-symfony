<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\UserChecker;

use App\Domain\User\Entity\User;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class AccountStatusUserChecker implements UserCheckerInterface
{
    /**
     * @see UserCheckerInterface
     *
     * @throws UnsupportedUserException
     * @throws DisabledException
     * @throws LockedException
     */
    #[\Override]
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf(
                'User class "%s" is not supported.',
                get_debug_type($user),
            ));
        }

        if ($user->isDisabled()) {
            $exception = new DisabledException('User account is disabled.');
            $exception->setUser($user);

            throw $exception;
        }

        if ($user->isSuspended()) {
            $exception = new LockedException('User account is suspended.');
            $exception->setUser($user);

            throw $exception;
        }
    }

    /**
     * @see UserCheckerInterface
     *
     * @throws UnsupportedUserException
     */
    #[\Override]
    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf(
                'User class "%s" is not supported.',
                get_debug_type($user),
            ));
        }
    }
}
