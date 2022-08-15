<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Resources\CartItemCollection;
use App\Services\Interfaces\CartServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private CartServiceInterface $cartService;

    public function __construct(CartServiceInterface $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(string $identifier)
    {
        return $this->cartService->getUserCartItems($identifier);
    }

    public function store(AddToCartRequest $request): JsonResponse
    {
        $response = $this->cartService->addCartItems([
            'identifier' => $request->identifier,
            'user_agent' => $request->userAgent(),
            'product_id' => $request->product_id,
            'quantity'   => $request->quantity
        ]);

        return response()->success($response, 'Item successfully added to cart.', 201);
    }

    public function delete(Request $request, int $cartItemId): JsonResponse
    {
        $this->cartService->delete($request->identifier, $cartItemId);

        return response()->deleted();
    }

    public function getDeletedItems(): CartItemCollection
    {
        return $this->cartService->getDeletedCartItems();
    }
}
