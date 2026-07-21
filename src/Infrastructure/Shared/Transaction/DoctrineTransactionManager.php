<?php

declare(strict_types=1);

namespace App\Infrastructure\Shared\Transaction;

use App\Application\Shared\Transaction\TransactionManagerInterface;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineTransactionManager implements TransactionManagerInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @see TransactionManagerInterface
     */
    #[\Override]
    public function transactional(callable $operation): mixed
    {
        return $this->entityManager->wrapInTransaction(
            static fn (): mixed => $operation(),
        );
    }
}
