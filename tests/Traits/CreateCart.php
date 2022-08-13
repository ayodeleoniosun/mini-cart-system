<?php

namespace Tests\Traits;

use App\Models\Cart;
use Illuminate\Database\Eloquent\Factories\Sequence;

trait CreateCart
{
    protected function createCart()
    {
        return Cart::factory()->create();
    }

    protected function createCartItems(array $products): array
    {
        $carts = [];

        collect($products)->map(function ($product) use (&$carts) {
            $carts[] = Cart::factory()->state(new Sequence(
                ['product_id' => $product]
            ))->create();
        });

        return $carts;
    }

    protected function deleteCartItems(array $items): void
    {
        collect($items)->map(function ($item) {
            $item->deleted_at = now();
            $item->save();
        });
    }
}
