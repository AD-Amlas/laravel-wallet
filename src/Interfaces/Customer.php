<?php

declare(strict_types=1);

namespace Bavix\Wallet\Interfaces;

use Bavix\Wallet\Models\TransferInterface;

interface Customer extends Wallet
{
    public function pay(Product $product, bool $force = false): TransferInterface;

    public function safePay(Product $product, bool $force = false): ?TransferInterface;

    public function forcePay(Product $product): TransferInterface;

    public function paid(Product $product, bool $gifts = false): ?TransferInterface;

    public function refund(Product $product, bool $force = false, bool $gifts = false): bool;

    public function safeRefund(Product $product, bool $force = false, bool $gifts = false): bool;

    public function forceRefund(Product $product, bool $gifts = false): bool;

    /**
     * @return TransferInterface[]
     */
    public function payCart(CartInterface $cart, bool $force = false): array;

    /**
     * @return TransferInterface[]
     */
    public function safePayCart(CartInterface $cart, bool $force = false): array;

    /**
     * @return TransferInterface[]
     */
    public function forcePayCart(CartInterface $cart): array;

    public function refundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool;

    public function safeRefundCart(CartInterface $cart, bool $force = false, bool $gifts = false): bool;

    public function forceRefundCart(CartInterface $cart, bool $gifts = false): bool;
}
