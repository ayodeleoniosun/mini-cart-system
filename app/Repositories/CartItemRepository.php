<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CartItemRepository implements CartItemRepositoryInterface
{
    private CartItem $cartItem;

    protected int $perPage = 10;

    /**
     * @param CartItem $cartItem
     */
    public function __construct(CartItem $cartItem)
    {
        $this->cartItem = $cartItem;
    }

    public function add(array $data): CartItem
    {
        return $this->cartItem->create([
            'product_id' => $data['product_id'],
            'cart_id'    => $data['cart_id'],
            'quantity'   => $data['quantity']
        ]);
    }

    public function update(array $data): CartItem
    {
        $cartId = $data['cart_id'];
        $productId = $data['product_id'];
        $quantity = $data['quantity'];

        $cartItem = $this->getCartItem($cartId, $productId);

        if ($cartItem->trashed()) {
            $cartItem->quantity = $quantity;
            $cartItem->deleted_at = null;
        } else {
            $cartItem->quantity += $quantity;
        }

        $cartItem->save();

        $cartItem->refresh();

        return $cartItem;
    }

    public function getCartItem(int $cartId, int $productId): ?CartItem
    {
        return $this->cartItem->where([
            'cart_id'    => $cartId,
            'product_id' => $productId,
        ])->withTrashed()->first();
    }

    public function delete(int $cartItemId, int $cartId): bool
    {
        return $this->cartItem->where([
            'id'      => $cartItemId,
            'cart_id' => $cartId
        ])->delete();
    }

    public function getUserCartItems(Cart $cart): LengthAwarePaginator
    {
        return $cart->cartItems()->paginate($this->perPage);
    }

    public function getDeletedCartItems(): LengthAwarePaginator
    {
        return $this->cartItem->onlyTrashed()->paginate($this->perPage);
    }
}
