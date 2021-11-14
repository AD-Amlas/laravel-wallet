<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Dto\TransactionDto;
use Bavix\Wallet\Internal\Dto\TransferDto;

interface AssistantServiceInterface
{
    /**
     * @param non-empty-array<TransactionDto|TransferDto> $objects
     *
     * @return non-empty-array<int|string, string>
     */
    public function getUuids(array $objects): array;

    /**
     * @param non-empty-array<TransactionDto> $transactions
     *
     * @return array<int, string>
     */
    public function getSums(array $transactions): array;
}