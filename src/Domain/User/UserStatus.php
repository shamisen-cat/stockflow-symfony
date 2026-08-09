<?php

declare(strict_types=1);

namespace App\Domain\User;

enum UserStatus: int
{
    case Active = 0;
    case Unverified = 10;
    case Disabled = 20;
    case Suspended = 30;
    case Deleted = 40;
}
