<?php

declare(strict_types=1);

namespace App\Application\User\ListUsers;

use App\Infrastructure\Shared\Pagination\SortDirection;
use App\Infrastructure\Shared\Pagination\SortResolver;
use App\Infrastructure\User\Repository\UserRepository;

final readonly class ListUsersHandler
{
    public function __construct(
        private SortResolver $sortResolver,
        private UserRepository $userRepository,
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

        $sort = $this->sortResolver->resolve(
            sortMap: $sortMap,
            sortKey: $input->sortKey,
            direction: $input->direction,
            defaultKey: $defaultKey,
            defaultDirection: $defaultDirection,
        );

        $pager = $this->userRepository->paginate(
            email: $input->email,
            sort: $sort,
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
