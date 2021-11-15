<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use function app;
use Bavix\Wallet\Exceptions\AmountInvalid;
use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\TransactionInterface;
use Bavix\Wallet\Models\Transfer;
use Bavix\Wallet\Models\TransferInterface;
use Bavix\Wallet\Models\WalletInterface as WalletModel;
use Bavix\Wallet\Services\BookkeeperServiceInterface;
use Bavix\Wallet\Services\CastServiceInterface;
use Bavix\Wallet\Services\CommonServiceLegacy;
use Bavix\Wallet\Services\ConsistencyServiceInterface;
use function config;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Throwable;

/**
 * Trait HasWallet.
 *
 * @property Collection|WalletModel[] $wallets
 * @property string                   $balance
 * @property int                      $balanceInt
 */
trait HasWallet
{
    use MorphOneWallet;

    /**
     * The input means in the system.
     *
     * @param int|string $amount
     *
     * @throws AmountInvalid
     * @throws Throwable
     */
    public function deposit($amount, ?array $meta = null, bool $confirmed = true): TransactionInterface
    {
        return app(DatabaseServiceInterface::class)->transaction(fn () => app(CommonServiceLegacy::class)
            ->makeTransaction($this, TransactionInterface::TYPE_DEPOSIT, $amount, $meta, $confirmed));
    }

    /**
     * Magic laravel framework method, makes it
     *  possible to call property balance.
     *
     * Example:
     *  $user1 = User::first()->load('wallet');
     *  $user2 = User::first()->load('wallet');
     *
     * Without static:
     *  var_dump($user1->balance, $user2->balance); // 100 100
     *  $user1->deposit(100);
     *  $user2->deposit(100);
     *  var_dump($user1->balance, $user2->balance); // 200 200
     *
     * With static:
     *  var_dump($user1->balance, $user2->balance); // 100 100
     *  $user1->deposit(100);
     *  var_dump($user1->balance); // 200
     *  $user2->deposit(100);
     *  var_dump($user2->balance); // 300
     */
    public function getBalanceAttribute(): string
    {
        /** @var Wallet $this */
        return app(BookkeeperServiceInterface::class)->amount(
            app(CastServiceInterface::class)->getWallet($this)
        );
    }

    /**
     * @throws Throwable
     */
    public function getBalanceIntAttribute(): int
    {
        return (int) $this->getBalanceAttribute();
    }

    /**
     * all user actions on wallets will be in this method.
     */
    public function transactions(): MorphMany
    {
        return app(CastServiceInterface::class)
            ->getHolder($this)
            ->morphMany(config('wallet.transaction.model', TransactionInterface::class), 'payable')
        ;
    }

    /**
     * This method ignores errors that occur when transferring funds.
     *
     * @param int|string $amount
     */
    public function safeTransfer(Wallet $wallet, $amount, ?array $meta = null): ?TransferInterface
    {
        try {
            return $this->transfer($wallet, $amount, $meta);
        } catch (Throwable $throwable) {
            return null;
        }
    }

    /**
     * A method that transfers funds from host to host.
     *
     * @param int|string $amount
     *
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws Throwable
     */
    public function transfer(Wallet $wallet, $amount, ?array $meta = null): TransferInterface
    {
        /** @var Wallet $this */
        app(ConsistencyServiceInterface::class)->checkPotential($this, $amount);

        return $this->forceTransfer($wallet, $amount, $meta);
    }

    /**
     * Withdrawals from the system.
     *
     * @param int|string $amount
     *
     * @throws AmountInvalid
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws Throwable
     */
    public function withdraw($amount, ?array $meta = null, bool $confirmed = true): TransactionInterface
    {
        /** @var Wallet $this */
        app(ConsistencyServiceInterface::class)->checkPotential($this, $amount);

        return $this->forceWithdraw($amount, $meta, $confirmed);
    }

    /**
     * Checks if you can withdraw funds.
     *
     * @param float|int|string $amount
     */
    public function canWithdraw($amount, bool $allowZero = false): bool
    {
        $math = app(MathServiceInterface::class);

        /**
         * Allow buying for free with a negative balance.
         */
        if ($allowZero && !$math->compare($amount, 0)) {
            return true;
        }

        return $math->compare($this->getBalanceAttribute(), $amount) >= 0;
    }

    /**
     * Forced to withdraw funds from system.
     *
     * @param int|string $amount
     *
     * @throws AmountInvalid
     * @throws Throwable
     */
    public function forceWithdraw($amount, ?array $meta = null, bool $confirmed = true): TransactionInterface
    {
        return app(DatabaseServiceInterface::class)->transaction(fn () => app(CommonServiceLegacy::class)
            ->makeTransaction($this, TransactionInterface::TYPE_WITHDRAW, $amount, $meta, $confirmed));
    }

    /**
     * the forced transfer is needed when the user does not have the money and we drive it.
     * Sometimes you do. Depends on business logic.
     *
     * @param int|string $amount
     *
     * @throws AmountInvalid
     * @throws Throwable
     */
    public function forceTransfer(Wallet $wallet, $amount, ?array $meta = null): TransferInterface
    {
        return app(DatabaseServiceInterface::class)->transaction(fn () => app(CommonServiceLegacy::class)
            ->forceTransfer($this, $wallet, $amount, $meta));
    }

    /**
     * the transfer table is used to confirm the payment
     * this method receives all transfers.
     */
    public function transfers(): MorphMany
    {
        /** @var Wallet $this */
        return app(CastServiceInterface::class)
            ->getWallet($this, false)
            ->morphMany(config('wallet.transfer.model', Transfer::class), 'from')
        ;
    }
}
