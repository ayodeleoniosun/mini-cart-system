<?php

namespace App\Services\Interfaces;

use App\Http\Resources\CartCollection;
use App\Models\Cart;
use App\Models\Session;
use Illuminate\Http\Request;

interface CartServiceInterface
{
    public function add(array $data): Cart;

    public function delete(array $data): bool;

    public function validateSession(string $ipAddress): Session;

    public function getUserCartItems(Request $request): CartCollection;

    public function getDeletedCartItems(Request $request): CartCollection;
}
