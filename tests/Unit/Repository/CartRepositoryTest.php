<?php

namespace Tests\Unit\Repository;

use App\Models\Cart;
use App\Repositories\CartRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $cart = new Cart();
    $this->cartRepo = new CartRepository($cart);
});

test('add cart items', function () {
    $product = $this->createProduct();
    $session = $this->createSession();

    $cart = $this->cartRepo->add([
        'product_id' => $product->id,
        'session_id' => $session->id
    ]);

    $this->assertInstanceOf(Cart::class, $cart);
    $this->assertEquals($cart->product_id, $product->id);
    $this->assertEquals($cart->session_id, $session->id);
});

test('delete existing cart item', function () {
    $product = $this->createProduct();
    $session = $this->createSession();

    $cart = $this->cartRepo->add([
        'product_id' => $product->id,
        'session_id' => $session->id
    ]);

    $deletedItem = $this->cartRepo->delete($cart->id, $cart->session_id);

    $this->assertTrue($deletedItem);
});

test('unable to delete non-existent cart item', function () {
    $deletedItem = $this->cartRepo->delete(1, 1);
    $this->assertFalse($deletedItem);
});

test('check if item exist in cart', function () {
    $product = $this->createProduct();
    $session = $this->createSession();

    $this->cartRepo->add([
        'product_id' => $product->id,
        'session_id' => $session->id
    ]);

    $itemExist = $this->cartRepo->itemExistInCart($product->id, $session->id);

    $this->assertTrue($itemExist);
});

test('check if item does not exist in cart', function () {
    $itemExist = $this->cartRepo->itemExistInCart(1, 1);
    $this->assertFalse($itemExist);
});

test('get user cart items', function () {
    $count = 5;
    $products = $this->createProduct($count);
    $session = $this->createSession();

    foreach ($products as $product) {
        $this->cartRepo->add([
            'product_id' => $product->id,
            'session_id' => $session->id
        ]);
    }

    $cartItems = $this->cartRepo->getUserCartItems($session->id);

    $this->assertEquals($cartItems->count(), $count);

    $cartItems->each(function ($item) use ($session, $products) {
        $this->assertEquals($item->session_id, $session->id);
    });
});

test('get zero user cart items', function () {
    $session = $this->createSession();
    $cartItems = $this->cartRepo->getUserCartItems($session->id);
    $this->assertEquals($cartItems->count(), 0);
});

test('get user deleted cart items', function () {
    $noOfProducts = 7;
    $noOfCartItemsToDelete = 3;

    //create cart items
    $products = $this->createProduct($noOfProducts);
    $session = $this->createSession();

    $carts = [];

    foreach ($products as $product) {
        $cart = $this->cartRepo->add([
            'product_id' => $product->id,
            'session_id' => $session->id
        ]);

        $carts[] = $cart->id;
    }

    //delete the first 3 cart items
    asort($carts);
    $topThreeCartItems = array_slice($carts, 0, $noOfCartItemsToDelete);

    foreach ($topThreeCartItems as $cart) {
        $this->cartRepo->delete($cart, $session->id);
    }

    //get deleted cart items
    $deletedCartItems = $this->cartRepo->getUserCartItems($session->id, true);

    $this->assertEquals($deletedCartItems->count(), $noOfCartItemsToDelete);

    $deletedCartItems->each(function ($item) use ($session, $products) {
        $this->assertEquals($item->session_id, $session->id);
        $this->assertNotNull($item->deleted_at);
    });
});

test('get zero user deleted cart items', function () {
    $session = $this->createSession();
    $deletedCartItems = $this->cartRepo->getUserCartItems($session->id, true);

    $this->assertEquals($deletedCartItems->count(), 0);
});

test('get all deleted cart items', function () {
    $noOfProducts = 10;
    $noOfCartItemsToDelete = 5;

    //create cart items for two guests
    $products = $this->createProduct($noOfProducts);
    $session = $this->createSession(2);

    $carts = [];
    $counter = 0;

    foreach ($products as $product) {
        if ($counter < 5) {
            $cart = $this->cartRepo->add([
                'product_id' => $product->id,
                'session_id' => $session[0]->id
            ]);

            $carts[] = [
                'cart_id'    => $cart->id,
                'session_id' => $session[0]->id
            ];

        } else {
            $cart = $this->cartRepo->add([
                'product_id' => $product->id,
                'session_id' => $session[1]->id
            ]);

            $carts[] = [
                'cart_id'    => $cart->id,
                'session_id' => $session[1]->id
            ];
        }

        $counter++;
    }

    //delete the first 5 cart items
    asort($carts);
    $topFiveCartItems = array_slice($carts, 0, $noOfCartItemsToDelete);

    foreach ($topFiveCartItems as $cart) {
        $this->cartRepo->delete($cart['cart_id'], $cart['session_id']);
    }

    //get all deleted cart items
    $deletedCartItems = $this->cartRepo->getDeletedCartItems();

    $this->assertEquals($deletedCartItems->count(), $noOfCartItemsToDelete);

    $deletedCartItems->each(function ($item) use ($session, $products) {
        $this->assertEquals($item->session_id, $session[0]->id);
        $this->assertNotNull($item->deleted_at);
    });
});

test('get zero deleted cart items', function () {
    $deletedCartItems = $this->cartRepo->getDeletedCartItems();
    $this->assertEquals($deletedCartItems->count(), 0);
});
