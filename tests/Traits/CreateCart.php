<?php

namespace Tests\Traits;

use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Database\Eloquent\Factories\Sequence;

trait CreateCart
{
    protected function createCart()
    {
        return Cart::factory()->create();
    }

    protected function createCarts(array $sessions): array
    {
        $carts = [];
        collect($sessions)->map(function ($session) use (&$carts) {
            $carts[] = Cart::factory()->state(new Sequence(
                ['session_id' => $session]
            ))->create();
        });

        return $carts;
    }

}
