<?php

namespace App\Services;

use App\Exceptions\CustomException;
use App\Http\Resources\CartCollection;
use App\Models\Cart;
use App\Models\Session;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\SessionRepositoryInterface;
use App\Services\Interfaces\CartServiceInterface;
use Illuminate\Http\Request;

class CartService implements CartServiceInterface
{
    protected CartRepositoryInterface $cartRepo;

    protected SessionRepositoryInterface $sessionRepo;

    /**
     * @param CartRepositoryInterface $cartRepo
     * @param SessionRepositoryInterface $sessionRepo
     */
    public function __construct(CartRepositoryInterface $cartRepo, SessionRepositoryInterface $sessionRepo)
    {
        $this->cartRepo = $cartRepo;
        $this->sessionRepo = $sessionRepo;
    }

    public function add(array $data): Cart
    {
        $ipAddress = $data['ip_address'];
        $userAgent = $data['user_agent'];
        $productId = $data['product_id'];

        $session = $this->sessionRepo->getOrCreateSession($ipAddress, $userAgent);

        $itemExistInCart = $this->cartRepo->itemExistInCart($productId, $session->id);

        if ($itemExistInCart) {
            throw new CustomException('Item already added to cart.');
        }

        $data['session_id'] = $session->id;

        return $this->cartRepo->add($data);
    }

    /**
     * @throws CustomException
     */
    public function delete(array $data): bool
    {
        $session = $this->validateSession($data['ip_address']);

        $status = $this->cartRepo->delete($data['cart_id'], $session->id);

        if (!$status) {
            throw new CustomException('Unable to delete item from cart. Check if the item exist.');
        }

        return $status;
    }

    /**
     * @throws CustomException
     */
    public function getUserCartItems(Request $request): CartCollection
    {
        $session = $this->validateSession($request->ip());
        $deleted = false;

        if ($request->filled('deleted')) {
            $deleted = filter_var($request->deleted, FILTER_VALIDATE_BOOLEAN);
        }

        $carts = $this->cartRepo->getUserCartItems($session->id, $deleted);

        if ($carts->total() == 0) {
            $errorMessage = $deleted ? 'You have not removed any item from your cart.' : 'You have not added any item to cart.';
            throw new CustomException($errorMessage, 404);
        }

        return new CartCollection($carts);
    }

    public function getDeletedCartItems(Request $request): CartCollection
    {
        $carts = $this->cartRepo->getDeletedCartItems();

        if ($carts->total() == 0) {
            throw new CustomException('No item has been deleted from cart', 404);
        }

        return new CartCollection($carts);
    }


    /**
     * @throws CustomException
     */
    public function validateSession(string $ipAddress): Session
    {
        $session = $this->sessionRepo->getSessionByIpAddress($ipAddress);

        if (!$session) {
            throw new CustomException('Invalid account.', 403);
        }

        return $session;
    }
}
