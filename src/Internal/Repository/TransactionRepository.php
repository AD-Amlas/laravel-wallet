<?php

declare(strict_types=1);

namespace Bavix\Wallet\Internal\Repository;

use Bavix\Wallet\Internal\Dto\TransactionDtoInterface;
use Bavix\Wallet\Internal\Query\TransactionQueryInterface;
use Bavix\Wallet\Internal\Service\JsonServiceInterface;
use Bavix\Wallet\Internal\Transform\TransactionDtoTransformerInterface;
use Bavix\Wallet\Models\Transaction;

final class TransactionRepository implements TransactionRepositoryInterface
{
    private TransactionDtoTransformerInterface $transformer;
    private JsonServiceInterface $jsonService;
    private Transaction $transaction;

    public function __construct(
        TransactionDtoTransformerInterface $transformer,
        JsonServiceInterface $jsonService,
        Transaction $transaction
    ) {
        $this->transformer = $transformer;
        $this->jsonService = $jsonService;
        $this->transaction = $transaction;
    }

    /**
     * @param non-empty-array<int|string, TransactionDtoInterface> $objects
     */
    public function insert(array $objects): void
    {
        $values = [];
        foreach ($objects as $object) {
            $values[] = array_map(
                fn ($value) => is_array($value) ? $this->jsonService->encode($value) : $value,
                $this->transformer->extract($object)
            );
        }

        assert($this->transaction->newQuery()->insert($values) === true);
    }

    public function insertOne(TransactionDtoInterface $dto): Transaction
    {
        $attributes = $this->transformer->extract($dto);
        $instance = $this->transaction->newInstance($attributes);
        assert($instance->saveQuietly() === true);

        return $instance;
    }

    /** @return Transaction[] */
    public function findBy(TransactionQueryInterface $query): array
    {
        return $this->transaction->newQuery()
            ->whereIn('uuid', $query->getUuids())
            ->get()
            ->all()
        ;
    }
}
