<?php

namespace Tests\Traits;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Factories\Sequence;

trait CreateCartItem
{
    protected function createCartItems(array $products, Cart $cart): array
    {
        $cartItems = [];

        collect($products)->map(function ($product) use ($cart, &$cartItems) {
            $cartItems[] = CartItem::factory()->state(new Sequence(
                ['cart_id' => $cart->id],
                ['product_id' => $product]
            ))->create();
        });

        return $cartItems;
    }
}
