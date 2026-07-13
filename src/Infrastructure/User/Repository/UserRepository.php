<?php

declare(strict_types=1);

namespace App\Infrastructure\User\Repository;

use App\Domain\User\Entity\User;
use App\Domain\User\Repository\UserRepositoryInterface;
use App\Infrastructure\Shared\Pagination\PaginationFactory;
use App\Infrastructure\Shared\Pagination\SortCriteria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Pagerfanta\Pagerfanta;
use Symfony\Component\Uid\Uuid;

/**
 * @extends ServiceEntityRepository<User>
 */
final class UserRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(
        ManagerRegistry $registry,
        private PaginationFactory $paginationFactory,
    ) {
        parent::__construct($registry, User::class);
    }

    /**
     * @see UserRepositoryInterface
     */
    #[\Override]
    public function findActiveById(Uuid $id): ?User
    {
        $user = $this->createQueryBuilder('u')
            ->where('u.id = :id')
            ->andWhere('u.deletedAt IS NULL')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$user instanceof User) {
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

    /**
     * @return Pagerfanta<User>
     */
    public function paginate(
        string $email,
        SortCriteria $sort,
        int $page,
        int $maxPerPage,
    ): Pagerfanta {
        $queryBuilder = $this->createQueryBuilder('u');

        if ($email !== '') {
            $escapedEmail = addcslashes($email, '%_\\');

            $queryBuilder
                ->andWhere('u.email.value LIKE :email')
                ->setParameter('email', '%'.$escapedEmail.'%');
        }

        $queryBuilder->orderBy($sort->field, $sort->direction);

        /** @var Pagerfanta<User> $pager */
        $pager = $this->paginationFactory->create(
            queryBuilder: $queryBuilder,
            page: $page,
            maxPerPage: $maxPerPage,
        );

        return $pager;
    }
}
