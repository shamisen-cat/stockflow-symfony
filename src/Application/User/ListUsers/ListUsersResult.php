<?php

declare(strict_types=1);

namespace App\Application\User\ListUsers;

use App\Application\User\UserRecord;
use Pagerfanta\Pagerfanta;

final readonly class ListUsersResult
{
    /**
     * @param Pagerfanta<UserRecord> $pagination
     */
    public function __construct(
        public Pagerfanta $pagination,
        public string $currentSortKey,
        public string $currentSortDirection,
        public string $searchEmail,
    ) {
    }
}
