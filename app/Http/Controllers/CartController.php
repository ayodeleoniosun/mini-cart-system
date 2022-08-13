<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Resources\CartCollection;
use App\Services\Interfaces\CartServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private CartServiceInterface $cart;

    public function __construct(CartServiceInterface $cart)
    {
        $this->cart = $cart;
    }

    public function index(Request $request)
    {
        return $this->cart->getUserCartItems($request);
    }

    public function store(AddToCartRequest $request): JsonResponse
    {
        $data = [
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'product_id' => $request->product_id,
        ];

        $response = $this->cart->add($data);

        return response()->success($response, 'Item successfully added to cart.', 201);
    }

    public function delete(Request $request, string $cartId): JsonResponse
    {
        $data = [
            'ip_address' => $request->ip(),
            'cart_id'    => $cartId,
        ];

        $response = $this->cart->delete($data);

        return response()->success($response, 'Item successfully removed from cart.');
    }

    public function deletedItems(Request $request): CartCollection
    {
        return $this->cart->getDeletedCartItems($request);
    }
}
