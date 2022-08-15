<?php

namespace App\Services\Interfaces;

use App\Http\Resources\CartItemCollection;
use App\Models\CartItem;
use Illuminate\Http\Request;

interface CartServiceInterface
{
    public function addCartItems(array $data): CartItem;

    public function delete(string $identifier, int $cartItemId): bool;

    public function getUserCartItems(string $identifier): CartItemCollection;

    public function getDeletedCartItems(): CartItemCollection;
}
