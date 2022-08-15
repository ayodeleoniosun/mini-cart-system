<?php

namespace App\Repositories\Interfaces;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Pagination\LengthAwarePaginator;

interface CartItemRepositoryInterface
{
    public function add(array $data): CartItem;

    public function getCartItem(int $cartId, int $productId): ?CartItem;

    public function update(array $data): CartItem;

    public function delete(int $cartItemId, int $cartId): bool;

    public function getUserCartItems(Cart $cart): LengthAwarePaginator;

    public function getDeletedCartItems(): LengthAwarePaginator;
}
