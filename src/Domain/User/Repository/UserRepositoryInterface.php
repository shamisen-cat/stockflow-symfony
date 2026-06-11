<?php

declare(strict_types=1);

namespace App\Domain\User\Repository;

use App\Domain\User\Entity\User;
use Symfony\Component\Uid\Uuid;

interface UserRepositoryInterface
{
    public function findActiveById(Uuid $id): ?User;

    public function findActiveByEmail(string $email): ?User;
}
