<?php

namespace App\Repositories\Interfaces;

use App\Models\Cart;

interface CartRepositoryInterface
{
    public function getOrCreateCart(int $sessionId): Cart;

    public function hasValidCart(int $sessionId): ?Cart;
}
