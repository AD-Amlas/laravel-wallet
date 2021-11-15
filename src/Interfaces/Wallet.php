<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Models\TransactionInterface;
use Bavix\Wallet\Models\TransferInterface;
use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Wallet
{
    /**
     * @param int|string $amount
     *
     * @throws AmountInvalid
     */
    public function deposit($amount, ?array $meta = null, bool $confirmed = true): TransactionInterface;

    /**
     * @param int|string $amount
     *
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     */
    public function withdraw($amount, ?array $meta = null, bool $confirmed = true): TransactionInterface;

    /**
     * @param int|string $amount
     *
     * @throws AmountInvalid
     */
    public function forceWithdraw($amount, ?array $meta = null, bool $confirmed = true): TransactionInterface;

    /**
     * @param int|string $amount
     *
     * @throws AmountInvalid
     */
    public function transfer(self $wallet, $amount, ?array $meta = null): TransferInterface;

    /**
     * @param int|string $amount
     *
     * @throws AmountInvalid
     */
    public function safeTransfer(self $wallet, $amount, ?array $meta = null): ?TransferInterface;

    /**
     * @param int|string $amount
     *
     * @throws AmountInvalid
     */
    public function forceTransfer(self $wallet, $amount, ?array $meta = null): TransferInterface;

    /**
     * @param int|string $amount
     */
    public function canWithdraw($amount, bool $allowZero = false): bool;

    public function getBalanceIntAttribute(): int;

    public function getBalanceAttribute(): string;

    public function transactions(): MorphMany;

    public function transfers(): MorphMany;
}
