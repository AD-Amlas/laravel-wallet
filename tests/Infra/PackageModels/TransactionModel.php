<?php

declare(strict_types=1);

namespace Bavix\Wallet\Test\Infra\PackageModels;

use Bavix\Wallet\Models\Transaction;

/**
 * Class Transaction.
 *
 * @property null|string $bank_method
 */
class TransactionModel extends Transaction
{
    /**
     * {@inheritdoc}
     */
    public function getFillable(): array
    {
        return array_merge($this->fillable, [
            'bank_method',
        ]);
    }
}
