<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Http\Resources\CartItemCollection;
use App\Models\Cart;
use App\Models\CartItem;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\SessionRepositoryInterface;
use App\Services\Interfaces\CartServiceInterface;
use Illuminate\Http\Request;

class CartService implements CartServiceInterface
{
    protected CartRepositoryInterface $cartRepo;

    protected CartItemRepositoryInterface $cartItemRepo;

    protected SessionRepositoryInterface $sessionRepo;

    /**
     * @param CartRepositoryInterface $cartRepo
     * @param CartItemRepositoryInterface $cartItemRepo
     * @param SessionRepositoryInterface $sessionRepo
     */

    public function __construct(
        CartRepositoryInterface     $cartRepo,
        CartItemRepositoryInterface $cartItemRepo,
        SessionRepositoryInterface  $sessionRepo)
    {
        $this->cartRepo = $cartRepo;
        $this->cartItemRepo = $cartItemRepo;
        $this->sessionRepo = $sessionRepo;
    }

    public function addCartItems(array $data): CartItem
    {
        $identifier = $data['identifier'];
        $userAgent = $data['user_agent'];
        $productId = $data['product_id'];
        $quantity = $data['quantity'];

        $session = $this->sessionRepo->getOrCreateSession($identifier, $userAgent);
        $cart = $this->cartRepo->getOrCreateCart($session->id);
        $cartItem = $this->cartItemRepo->getCartItem($cart->id, $productId);

        $cartItemData = [
            'cart_id'    => $cart->id,
            'product_id' => $productId,
            'quantity'   => $quantity
        ];

        if ($cartItem) {
            return $this->cartItemRepo->update($cartItemData);
        }

        return $this->cartItemRepo->add($cartItemData);
    }

    /**
     * @throws CustomException
     */
    public function delete(string $identifier, int $cartItemId): bool
    {
        $cart = $this->hasValidCart($identifier);

        $status = $this->cartItemRepo->delete($cartItemId, $cart->id);

        if (!$status) {
            throw new CustomException('Unable to delete item from cart. Check if the item exist.');
        }

        return true;
    }

    /**
     * @throws CustomException
     */
    public function getUserCartItems(string $identifier): CartItemCollection
    {
        $cart = $this->hasValidCart($identifier);

        $cartItems = $this->cartItemRepo->getUserCartItems($cart);

        return new CartItemCollection($cartItems);
    }

    public function getDeletedCartItems(): CartItemCollection
    {
        $cartItems = $this->cartItemRepo->getDeletedCartItems();
        return new CartItemCollection($cartItems);
    }

    /**
     * @throws CustomException
     */
    public function hasValidCart(string $identifier): Cart
    {
        $session = $this->sessionRepo->getSessionByIdentifier($identifier);

        if (!$session) {
            throw new CustomException('Invalid account.', 403);
        }

        $cart = $this->cartRepo->hasValidCart($session->id);

        if (!$cart) {
            throw new CustomException('No cart yet.', 403);
        }

        return $cart;
    }
}
