<?php

declare(strict_types=1);

namespace App\Infrastructure\User;

use App\Application\User\ListUsers\ListUsersInput;
use App\Application\User\ListUsers\ListUsersResult;
use App\Application\User\UserReaderInterface;
use App\Application\User\UserRecord;
use App\Domain\User\User;
use App\Infrastructure\Shared\Sort\SortDirection;
use App\Infrastructure\Shared\Sort\SortResolver;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;

final readonly class UserReader implements UserReaderInterface
{
    private const string DEFAULT_SORT_KEY = 'updated_at';

    /** @var array<string, string> */
    private const array SORT_MAP = [
        'id' => 'u.id',
        'email' => 'u.email.value',
        'updated_at' => 'u.updatedAt',
    ];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private SortResolver $sortResolver,
    ) {
    }

    /**
     * @see UserReaderInterface
     */
    #[\Override]
    public function paginate(ListUsersInput $input): ListUsersResult
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select(sprintf(
                'NEW %s(
                    u.id,
                    u.email.value,
                    u.status,
                    u.createdAt,
                    u.updatedAt,
                    u.disabledAt,
                    u.suspendedAt,
                    u.deletedAt
                )',
                UserRecord::class,
            ))
            ->from(User::class, 'u');

        if ($input->email !== '') {
            $escapedEmail = addcslashes($input->email, '%_\\');

            $queryBuilder
                ->andWhere('u.email.value LIKE :email')
                ->setParameter('email', '%'.$escapedEmail.'%');
        }

        $sort = $this->sortResolver->resolve(
            sortMap: self::SORT_MAP,
            sortKey: $input->sortKey,
            direction: $input->direction,
            defaultKey: self::DEFAULT_SORT_KEY,
            defaultDirection: SortDirection::Desc,
        );

        $queryBuilder->orderBy($sort->field, $sort->direction);

        if ($sort->field !== 'u.id') {
            $queryBuilder->addOrderBy('u.id', 'ASC');
        }

        $adapter = new QueryAdapter(
            query: $queryBuilder,
            fetchJoinCollection: false,
            useOutputWalkers: false,
        );

        /** @var Pagerfanta<UserRecord> $pager */
        $pager = Pagerfanta::createForCurrentPageWithMaxPerPage(
            adapter: $adapter,
            currentPage: $input->page,
            maxPerPage: $input->maxPerPage,
        );

        return new ListUsersResult(
            pagination: $pager,
            currentSortKey: $sort->key,
            currentSortDirection: $sort->direction,
            searchEmail: $input->email,
        );
    }
}
