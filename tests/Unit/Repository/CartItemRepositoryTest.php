<?php

namespace Tests\Unit\Repository;

use App\Models\CartItem;
use App\Repositories\CartItemRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
uses()->group('cart_item_repository');

beforeEach(function () {
    $cartItem = new CartItem();
    $this->cartItemRepo = new CartItemRepository($cartItem);
});

test('add cart items', function () {
    $product = $this->createProduct();
    $this->createSession();
    $cart = $this->createCart();

    $cartItem = $this->cartItemRepo->add([
        'cart_id'    => $cart->id,
        'product_id' => $product->id,
        'quantity'   => 2,
    ]);

    $this->assertInstanceOf(CartItem::class, $cartItem);
    $this->assertEquals($cartItem->product_id, $product->id);
    $this->assertEquals($cartItem->cart_id, $cart->id);
});

test('update existing cart item', function () {
    $product = $this->createProduct();
    $this->createSession();
    $cart = $this->createCart();
    $this->createCartItems([$product->id], $cart);

    $updatedQuantity = 2;

    $updateCartItem = $this->cartItemRepo->update([
        'cart_id'    => $cart->id,
        'product_id' => $product->id,
        'quantity'   => $updatedQuantity,
    ]);

    $this->assertInstanceOf(CartItem::class, $updateCartItem);
    $this->assertEquals($updateCartItem->product_id, $product->id);
    $this->assertEquals($updateCartItem->cart_id, $cart->id);
    $this->assertEquals($updateCartItem->quantity, $updatedQuantity);
    $this->assertNull($updateCartItem->deleted_at);
});

test('re-add removed cart item', function () {
    //add new cart item

    $product = $this->createProduct();
    $this->createSession();
    $cart = $this->createCart();

    $cartItem = $this->cartItemRepo->add([
        'cart_id'    => $cart->id,
        'product_id' => $product->id,
        'quantity'   => 2,
    ]);

    //remove cart item
    $this->cartItemRepo->delete($cartItem->id, $cart->id);
    $cartItem->refresh();

    $this->assertEquals($cartItem->status, CartItem::REMOVED);

    //re-add removed cart item
    $updateCartItem = $this->cartItemRepo->update([
        'cart_id'    => $cart->id,
        'product_id' => $product->id,
        'quantity'   => 3,
    ]);

    $this->assertInstanceOf(CartItem::class, $updateCartItem);
    $this->assertEquals($updateCartItem->product_id, $product->id);
    $this->assertEquals($updateCartItem->cart_id, $cart->id);
    $this->assertEquals($updateCartItem->quantity, 3);
    $this->assertEquals($updateCartItem->status, CartItem::PENDING);
});

test('get existing cart item', function () {
    $product = $this->createProduct();
    $this->createSession();
    $cart = $this->createCart();
    $this->createCartItems([$product->id], $cart);

    $cartItem = $this->cartItemRepo->getCartItem($cart->id, $product->id);

    $this->assertInstanceOf(CartItem::class, $cartItem);
    $this->assertEquals($cartItem->product_id, $product->id);
    $this->assertEquals($cartItem->cart_id, $cart->id);
});

test('unable to get non-existent cart item', function () {
    $product = $this->createProduct();
    $this->createSession();
    $cart = $this->createCart();

    $cartItem = $this->cartItemRepo->getCartItem($cart->id, $product->id);
    $this->assertNull($cartItem);
});

test('delete existing cart item', function () {
    $product = $this->createProduct();
    $this->createSession();
    $cart = $this->createCart();
    $cartItem = $this->createCartItems([$product->id], $cart);

    $deletedItem = $this->cartItemRepo->delete($cartItem[0]->id, $cart->id);
    $this->assertTrue($deletedItem);
});

test('unable to delete non-existent cart item', function () {
    $deletedItem = $this->cartItemRepo->delete(1, 1);
    $this->assertFalse($deletedItem);
});

test('get existing user cart items', function () {
    $count = 5;
    $product = $this->createProduct($count);
    $this->createSession();
    $cart = $this->createCart();
    $this->createCartItems($product->pluck('id')->toArray(), $cart);

    $cartItems = $this->cartItemRepo->getUserCartItems($cart);

    $this->assertEquals($cartItems->count(), $count);

    $cartItems->each(function ($item) use ($cart) {
        $this->assertEquals($item->cart_id, $cart->id);
    });
});

test('get zero user cart items', function () {
    $this->createSession();
    $cart = $this->createCart();

    $cartItems = $this->cartItemRepo->getUserCartItems($cart);

    $this->assertEquals($cartItems->count(), 0);
});

test('get all deleted cart items', function () {
    $noOfProducts = 10;
    $noOfCartItemsToDelete = 5;
    $guests = 2;

    //create cart items for two guests
    $products = $this->createProduct($noOfProducts);
    $session = $this->createSession($guests);
    $carts = $this->createCarts($session->pluck('id')->toArray());

    $guestOneCartItems = $this->createCartItems($products->pluck('id')->toArray(), $carts[0]);
    $guestTwoCartItems = $this->createCartItems($products->pluck('id')->toArray(), $carts[1]);

    //delete the first 3 cart items of guest one and two from guest two
    $topThreeGuestOneCartItems = array_slice($guestOneCartItems, 0, 3);
    $topTwoGuestTwoCartItems = array_slice($guestTwoCartItems, 0, 2);
    $cartItems = array_merge($topThreeGuestOneCartItems, $topTwoGuestTwoCartItems);

    foreach ($cartItems as $cartItem) {
        $this->cartItemRepo->delete($cartItem['id'], $cartItem['cart_id']);
    }

    //get all deleted cart items
    $deletedCartItems = $this->cartItemRepo->getDeletedCartItems();

    $this->assertEquals($deletedCartItems->count(), $noOfCartItemsToDelete);

    $deletedCartItems->each(function ($item) use ($session, $products) {
        $this->assertEquals($item->status, CartItem::REMOVED);
    });
});

test('get zero deleted cart items', function () {
    $deletedCartItems = $this->cartItemRepo->getDeletedCartItems();
    $this->assertEquals($deletedCartItems->count(), 0);
});
