<?php

declare(strict_types=1);

namespace App\Application\User\ListUsers;

use App\Domain\User\Entity\User;
use App\Infrastructure\Shared\Pagination\PaginationFactory;
use App\Infrastructure\Shared\Pagination\SortDirection;
use App\Infrastructure\Shared\Pagination\SortResolver;
use App\Infrastructure\User\Repository\UserRepository;
use Pagerfanta\Pagerfanta;

final readonly class ListUsersHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private SortResolver $sortResolver,
        private PaginationFactory $paginationFactory,
    ) {
    }

    public function handle(ListUsersInput $input): ListUsersResult
    {
        $defaultKey = 'updated_at';
        $defaultDirection = SortDirection::Desc;

        $sortMap = [
            'id' => 'u.id',
            'email' => 'u.email.value',
            'created_at' => 'u.createdAt',
            $defaultKey => 'u.updatedAt',
        ];

        $queryBuilder = $this->userRepository->createListQueryBuilder();

        if ($input->email !== '') {
            $this->userRepository->applyEmailFilter($queryBuilder, $input->email);
        }

        $sort = $this->sortResolver->resolve(
            sortMap: $sortMap,
            sortKey: $input->sortKey,
            direction: $input->direction,
            defaultKey: $defaultKey,
            defaultDirection: $defaultDirection,
        );

        $this->userRepository->applyListSort($queryBuilder, $sort);

        /** @var Pagerfanta<User> $pager */
        $pager = $this->paginationFactory->create(
            queryBuilder: $queryBuilder,
            page: $input->page,
            maxPerPage: $input->perPage,
        );

        return new ListUsersResult(
            pagination: $pager,
            currentSortKey: $sort->key,
            currentSortDirection: $sort->direction,
            searchEmail: $input->email,
        );
    }
}
