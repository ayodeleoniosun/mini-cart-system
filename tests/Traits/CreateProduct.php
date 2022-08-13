<?php

namespace Tests\Traits;

use App\Models\Product;

trait CreateProduct
{
    protected function createProduct(int $count = 1)
    {
        return $count > 1 ? Product::factory()->count($count)->create() : Product::factory()->create();
    }
}
