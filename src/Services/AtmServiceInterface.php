<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Dto\TransferDtoInterface;
use Bavix\Wallet\Models\TransactionInterface;
use Bavix\Wallet\Models\TransferInterface;

interface AtmServiceInterface
{
    /**
     * @param non-empty-array<int|string, TransactionDtoInterface> $objects
     *
     * @return non-empty-array<string, TransactionInterface>
     */
    public function makeTransactions(array $objects): array;

    /**
     * @param non-empty-array<int|string, TransferDtoInterface> $objects
     *
     * @return non-empty-array<string, TransferInterface>
     */
    public function makeTransfers(array $objects): array;
}
