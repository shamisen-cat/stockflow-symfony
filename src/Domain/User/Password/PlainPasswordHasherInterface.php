<?php

declare(strict_types=1);

namespace App\Domain\User\Password;

use App\Domain\User\ValueObject\Password\HashedPassword;
use App\Domain\User\ValueObject\Password\PlainPassword;

interface PlainPasswordHasherInterface
{
    public function hash(PlainPassword $plainPassword): HashedPassword;
}
