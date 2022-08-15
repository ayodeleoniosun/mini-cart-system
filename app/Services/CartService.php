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
        /*
            Thought flow for adding cart items:

            1. Check if the guest user has a session record. If no, create a new session record, else, retrieve the existing session record.
            2. Check if a cart record exist for that session (guest).  If no, create a new cart record, else, retrieve the existing cart record.

            NOTE: A guest user can only have a single cart and in that cart are multiple cart items (One-to-many relationship).

            3. Check if the item to be added has already been added or soft deleted before.
            4. If the record exist but was soft deleted, update the quantity with the new quantity and null the deleted_at column
                in order to avoid creating identical cart items and prevent redundancy of data.
            5. However, if the record exist but was not soft deleted, increment the quantity with the new quantity.
         */

        $ipAddress = $data['ip_address'];
        $userAgent = $data['user_agent'];
        $productId = $data['product_id'];
        $quantity = $data['quantity'];

        $session = $this->sessionRepo->getOrCreateSession($ipAddress, $userAgent);
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
    public function delete(string $ipAddress, int $cartItemId): bool
    {
        $cart = $this->hasValidCart($ipAddress);

        $status = $this->cartItemRepo->delete($cartItemId, $cart->id);

        if (!$status) {
            throw new CustomException('Unable to delete item from cart. Check if the item exist.');
        }

        return true;
    }

    /**
     * @throws CustomException
     */
    public function getUserCartItems(Request $request): CartItemCollection
    {
        $cart = $this->hasValidCart($request->ip());

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
    public function hasValidCart(string $ipAddress): Cart
    {
        $session = $this->sessionRepo->getSessionByIpAddress($ipAddress);

        if (!$session) {
            throw new CustomException('Invalid account.', 403);
        }

        $cart = $this->cartRepo->hasValidCart($session->id);

        if (!$cart) {
            throw new CustomException('No cart yet.', 403);
        }

        return $cart;
    }

    //analysis for removed cart items
}
