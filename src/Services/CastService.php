<?php

declare(strict_types=1);

namespace Bavix\Wallet\Services;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Models\WalletInterface;
use Illuminate\Database\Eloquent\Model;

/** @psalm-internal */
final class CastService implements CastServiceInterface
{
    public function getWallet(Wallet $object, bool $save = true): WalletInterface
    {
        $wallet = $this->getModel($object);
        if (!($wallet instanceof WalletInterface)) {
            $wallet = $wallet->getAttribute('wallet');
            assert($wallet instanceof WalletInterface);
        }

        if ($save) {
            $wallet->exists or $wallet->save();
        }

        return $wallet;
    }

    /** @param Model|Wallet $object */
    public function getHolder($object): Model
    {
        return $this->getModel($object instanceof WalletInterface ? $object->holder : $object);
    }

    public function getModel(object $object): Model
    {
        assert($object instanceof Model);

        return $object;
    }
}
