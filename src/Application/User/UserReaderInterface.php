<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Application\User\ListUsers\ListUsersResult;

interface UserReaderInterface
{
    public function paginate(
        string $email,
        string $sortKey,
        string $direction,
        int $page,
        int $maxPerPage,
    ): ListUsersResult;
}
