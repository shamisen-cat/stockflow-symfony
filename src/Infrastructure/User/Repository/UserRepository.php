<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Repository;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<User>
 */
final class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * @see UserRepositoryInterface
     */
    #[\Override]
    public function findActiveById(Uuid $id): ?User
    {
        $user = $this->find($id);

        if (!$user instanceof User || $user->isDeleted()) {
            return null;
        }

        return $user;
    }

    /**
     * @see UserRepositoryInterface
     */
    #[\Override]
    public function findActiveByEmail(string $email): ?User
    {
        $user = $this->createQueryBuilder('u')
            ->where('u.email.value = :email')
            ->andWhere('u.deletedAt IS NULL')
            ->setParameter('email', $email)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$user instanceof User) {
            return null;
        }

        return $user;
    }
}
