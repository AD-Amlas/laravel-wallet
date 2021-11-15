<?php

declare(strict_types=1);

namespace Bavix\Wallet\Traits;

use Bavix\Wallet\Exceptions\BalanceIsEmpty;
use Bavix\Wallet\Exceptions\ConfirmedInvalid;
use Bavix\Wallet\Exceptions\InsufficientFunds;
use Bavix\Wallet\Exceptions\UnconfirmedInvalid;
use Bavix\Wallet\Exceptions\WalletOwnerInvalid;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Bavix\Wallet\Internal\Service\DatabaseServiceInterface;
use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Internal\Service\TranslatorServiceInterface;
use Bavix\Wallet\Models\TransactionInterface;
use Bavix\Wallet\Services\AtomicServiceInterface;
use Bavix\Wallet\Services\CastServiceInterface;
use Bavix\Wallet\Services\CommonServiceLegacy;
use Bavix\Wallet\Services\ConsistencyServiceInterface;

trait CanConfirm
{
    /**
     * @throws BalanceIsEmpty
     * @throws InsufficientFunds
     * @throws ConfirmedInvalid
     * @throws WalletOwnerInvalid
     * @throws ExceptionInterface
     */
    public function confirm(TransactionInterface $transaction): bool
    {
        return app(DatabaseServiceInterface::class)->transaction(function () use ($transaction) {
            if ($transaction->getTypeAttribute() === TransactionInterface::TYPE_WITHDRAW) {
                app(ConsistencyServiceInterface::class)->checkPotential(
                    app(CastServiceInterface::class)->getWallet($this),
                    app(MathServiceInterface::class)->abs($transaction->getAmountAttribute())
                );
            }

            return $this->forceConfirm($transaction);
        });
    }

    public function safeConfirm(TransactionInterface $transaction): bool
    {
        try {
            return $this->confirm($transaction);
        } catch (ExceptionInterface $throwable) {
            return false;
        }
    }

    /**
     * Removal of confirmation (forced), use at your own peril and risk.
     *
     * @throws UnconfirmedInvalid
     */
    public function resetConfirm(TransactionInterface $transaction): bool
    {
        return app(AtomicServiceInterface::class)->block($this, fn () => app(DatabaseServiceInterface::class)->transaction(function () use ($transaction) {
            if (!$transaction->getConfirmedAttribute()) {
                throw new UnconfirmedInvalid(
                    app(TranslatorServiceInterface::class)->get('wallet::errors.unconfirmed_invalid'),
                    ExceptionInterface::UNCONFIRMED_INVALID
                );
            }

            $wallet = app(CastServiceInterface::class)->getWallet($this);
            $mathService = app(MathServiceInterface::class);
            $negativeAmount = $mathService->negative($transaction->getAmountAttribute());

            return $transaction->update(['confirmed' => false]) &&
                // update balance
                app(CommonServiceLegacy::class)
                    ->addBalance($wallet, $negativeAmount)
                ;
        }));
    }

    public function safeResetConfirm(TransactionInterface $transaction): bool
    {
        try {
            return $this->resetConfirm($transaction);
        } catch (ExceptionInterface $throwable) {
            return false;
        }
    }

    /**
     * @throws ConfirmedInvalid
     * @throws WalletOwnerInvalid
     */
    public function forceConfirm(TransactionInterface $transaction): bool
    {
        return app(AtomicServiceInterface::class)->block($this, fn () => app(DatabaseServiceInterface::class)->transaction(function () use ($transaction) {
            if ($transaction->getConfirmedAttribute()) {
                throw new ConfirmedInvalid(
                    app(TranslatorServiceInterface::class)->get('wallet::errors.confirmed_invalid'),
                    ExceptionInterface::CONFIRMED_INVALID
                );
            }

            $wallet = app(CastServiceInterface::class)->getWallet($this);
            if ($wallet->getKey() !== $transaction->getWalletIdAttribute()) {
                throw new WalletOwnerInvalid(
                    app(TranslatorServiceInterface::class)->get('wallet::errors.owner_invalid'),
                    ExceptionInterface::WALLET_OWNER_INVALID
                );
            }

            return $transaction->update(['confirmed' => true]) &&
                // update balance
                app(CommonServiceLegacy::class)
                    ->addBalance($wallet, $transaction->getAmountAttribute())
                ;
        }));
    }
}
