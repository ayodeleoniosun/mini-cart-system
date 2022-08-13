<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Repositories\Interfaces\CartRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CartRepository implements CartRepositoryInterface
{
    private Cart $cart;

    protected int $perPage = 10;

    /**
     * @param Cart $cart
     */
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    public function add(array $data): Cart
    {
        return $this->cart->create([
            'product_id' => $data['product_id'],
            'session_id' => $data['session_id']
        ]);
    }

    public function delete(string $cartId, string $sessionId): bool
    {
        return $this->cart->where([
            'id'         => $cartId,
            'session_id' => $sessionId
        ])->delete();
    }

    public function itemExistInCart(string $productId, string $sessionId): bool
    {
        return $this->cart->where([
            'product_id' => $productId,
            'session_id' => $sessionId
        ])->exists();
    }

    public function getUserCartItems(string $sessionId, bool $deleted = false): LengthAwarePaginator
    {
        if ($deleted) {
            return $this->cart->onlyTrashed()->where('session_id', $sessionId)->paginate($this->perPage);
        }

        return $this->cart->where('session_id', $sessionId)->paginate($this->perPage);
    }

    public function getDeletedCartItems(): LengthAwarePaginator
    {
        return $this->cart->onlyTrashed()->paginate($this->perPage);
    }
}
