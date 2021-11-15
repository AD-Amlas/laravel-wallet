<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Dto\TransferDtoInterface;
use Bavix\Wallet\Internal\Query\TransactionQuery;
use Bavix\Wallet\Internal\Query\TransferQuery;
use Bavix\Wallet\Internal\Repository\TransactionRepositoryInterface;
use Bavix\Wallet\Internal\Repository\TransferRepositoryInterface;
use Bavix\Wallet\Models\TransactionInterface;
use Bavix\Wallet\Models\TransferInterface;

/** @psalm-internal */
final class AtmService implements AtmServiceInterface
{
    private TransactionRepositoryInterface $transactionRepository;
    private TransferRepositoryInterface $transferRepository;
    private AssistantServiceInterface $assistantService;

    public function __construct(
        TransactionRepositoryInterface $transactionRepository,
        TransferRepositoryInterface $transferRepository,
        AssistantServiceInterface $assistantService
    ) {
        $this->transactionRepository = $transactionRepository;
        $this->transferRepository = $transferRepository;
        $this->assistantService = $assistantService;
    }

    /**
     * @param non-empty-array<int|string, TransactionDtoInterface> $objects
     *
     * @return non-empty-array<string, TransactionInterface>
     */
    public function makeTransactions(array $objects): array
    {
        $this->transactionRepository->insert($objects);
        $uuids = $this->assistantService->getUuids($objects);
        $query = new TransactionQuery($uuids);

        $items = $this->transactionRepository->findBy($query);
        assert(count($items) > 0);

        $results = [];
        foreach ($items as $item) {
            $results[$item->uuid] = $item;
        }

        return $results;
    }

    /**
     * @param non-empty-array<int|string, TransferDtoInterface> $objects
     *
     * @return non-empty-array<string, TransferInterface>
     */
    public function makeTransfers(array $objects): array
    {
        $this->transferRepository->insert($objects);
        $uuids = $this->assistantService->getUuids($objects);
        $query = new TransferQuery($uuids);

        $items = $this->transferRepository->findBy($query);
        assert(count($items) > 0);

        $results = [];
        foreach ($items as $item) {
            $results[$item->uuid] = $item;
        }

        return $results;
    }
}
