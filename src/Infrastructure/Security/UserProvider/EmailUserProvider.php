<?php

declare(strict_types=1);

namespace App\Infrastructure\Security\UserProvider;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<User>
 */
final readonly class EmailUserProvider implements UserProviderInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    /**
     * @see UserProviderInterface
     */
    #[\Override]
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->userRepository->findActiveByEmail($identifier);

        if ($user === null) {
            $exception = new UserNotFoundException('Active user not found.');
            $exception->setUserIdentifier($identifier);

            throw $exception;
        }

        return $user;
    }

    /**
     * @see UserProviderInterface
     */
    #[\Override]
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('User class "%s" is not supported.', get_debug_type($user)));
        }

        $refreshedUser = $this->userRepository->findActiveById($user->id);

        if ($refreshedUser === null) {
            $exception = new UserNotFoundException('Active user could not be refreshed.');
            $exception->setUserIdentifier($user->getUserIdentifier());

            throw $exception;
        }

        return $refreshedUser;
    }

    /**
     * @see UserProviderInterface
     */
    #[\Override]
    public function supportsClass(string $class): bool
    {
        return $class === User::class || is_subclass_of($class, User::class);
    }
}
