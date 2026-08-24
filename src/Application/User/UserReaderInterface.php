<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Application\User\ListUsers\ListUsersInput;
use App\Application\User\ListUsers\ListUsersResult;

interface UserReaderInterface
{
    public function paginate(ListUsersInput $input): ListUsersResult;
}
