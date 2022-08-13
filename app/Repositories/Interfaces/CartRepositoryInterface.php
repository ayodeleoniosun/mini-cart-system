<?php

namespace App\Repositories\Interfaces;

use App\Models\Cart;
use Illuminate\Pagination\LengthAwarePaginator;

interface CartRepositoryInterface
{
    public function add(array $data): Cart;

    public function delete(string $cartId, string $sessionId): bool;

    public function itemExistInCart(string $productId, string $sessionId): bool;

    public function getUserCartItems(string $sessionId, bool $deleted = false): LengthAwarePaginator;

    public function getDeletedCartItems(): LengthAwarePaginator;
}
