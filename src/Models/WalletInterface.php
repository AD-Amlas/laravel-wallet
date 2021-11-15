<?php

declare(strict_types=1);

namespace Bavix\Wallet\Models;

use Bavix\Wallet\Interfaces\Confirmable;
use Bavix\Wallet\Interfaces\Customer;
use Bavix\Wallet\Interfaces\Exchangeable;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Illuminate\Database\Eloquent\Relations\MorphTo;

interface WalletInterface extends Customer, WalletFloat, Confirmable, Exchangeable
{
    public function getTable(): string;

    public function setNameAttribute(string $name): void;

    /**
     * Under ideal conditions, you will never need a method.
     * Needed to deal with out-of-sync.
     *
     * @throws ExceptionInterface
     */
    public function refreshBalance(): bool;

    public function holder(): MorphTo;

    public function getOriginalBalanceAttribute(): string;

    public function getAvailableBalanceAttribute(): string;

    public function getCurrencyAttribute(): string;

    public function getDecimalPlacesAttribute(): int;
}
