<?php

declare(strict_types=1);

namespace App\Domain\User\Password;

interface PlainPasswordHasherInterface
{
    public function hash(PlainPassword $plainPassword): HashedPassword;
}
