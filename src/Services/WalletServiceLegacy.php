<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Internal\Service\MathServiceInterface;
use Bavix\Wallet\Models\WalletInterface;
use Bavix\Wallet\Models\WalletInterface as WalletModel;
use Illuminate\Database\Eloquent\Model;

final class WalletServiceLegacy
{
    private MathServiceInterface $math;
    private BookkeeperServiceInterface $bookkeeper;
    private AtomicServiceInterface $atomicService;

    public function __construct(
        MathServiceInterface $math,
        BookkeeperServiceInterface $bookkeeper,
        AtomicServiceInterface $atomicService
    ) {
        $this->math = $math;
        $this->bookkeeper = $bookkeeper;
        $this->atomicService = $atomicService;
    }

    /**
     * @deprecated
     * @see WalletModel::refreshBalance()
     */
    public function refresh(WalletInterface $wallet): bool
    {
        return $this->atomicService->block($wallet, function () use ($wallet) {
            /** @var Model|WalletInterface $wallet */
            $whatIs = $wallet->getBalanceAttribute();
            $balance = $wallet->getAvailableBalanceAttribute();
            if ($this->math->compare($whatIs, $balance) === 0) {
                return true;
            }

            $wallet->balance = (string) $balance;

            return $wallet->save() && $this->bookkeeper->sync($wallet, $balance);
        });
    }
}
