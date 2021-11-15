<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Internal\Dto\BasketDtoInterface;
use Bavix\Wallet\Models\TransferInterface;

interface PurchaseServiceInterface
{
    /** @return TransferInterface[] */
    public function already(Customer $customer, BasketDtoInterface $basketDto, bool $gifts = false): array;
}
