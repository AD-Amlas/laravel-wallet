<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Models\WalletInterface;

interface BookkeeperServiceInterface
{
    public function missing(WalletInterface $wallet): bool;

    public function amount(WalletInterface $wallet): string;

    /** @param float|int|string $value */
    public function sync(WalletInterface $wallet, $value): bool;

    /** @param float|int|string $value */
    public function increase(WalletInterface $wallet, $value): string;
}
