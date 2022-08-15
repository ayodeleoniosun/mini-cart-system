<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Repositories\Interfaces\CartRepositoryInterface;

class CartRepository implements CartRepositoryInterface
{
    private Cart $cart;

    /**
     * @param Cart $cart
     */
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }

    public function getOrCreateCart(int $sessionId): Cart
    {
        return $this->cart->firstOrCreate(['session_id' => $sessionId]);
    }

    public function hasValidCart(int $sessionId): ?Cart
    {
        return $this->cart->where(['session_id' => $sessionId])->first();
    }
}
