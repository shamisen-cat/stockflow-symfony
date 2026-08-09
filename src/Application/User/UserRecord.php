<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\User\Enum\UserStatus;
use Symfony\Component\Uid\Uuid;

final readonly class UserRecord
{
    public string $id;

    public function __construct(
        Uuid $id,
        public string $email,
        public UserStatus $status,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $updatedAt,
        public ?\DateTimeImmutable $disabledAt,
        public ?\DateTimeImmutable $suspendedAt,
        public ?\DateTimeImmutable $deletedAt,
    ) {
        $this->id = $id->toRfc4122();
    }
}
