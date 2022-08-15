<?php

namespace Tests\Unit\Repository;

use App\Models\Cart;
use App\Repositories\CartRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
uses()->group('cart_repository');

beforeEach(function () {
    $cart = new Cart();
    $this->cartRepo = new CartRepository($cart);
});

test('create new cart', function () {
    $session = $this->createSession();

    $cart = $this->cartRepo->getOrCreateCart($session->id);

    $this->assertInstanceOf(Cart::class, $cart);
    $this->assertEquals($cart->session_id, $session->id);
});

test('get existing cart', function () {
    $session = $this->createSession();
    $this->createCart();

    $cart = $this->cartRepo->getOrCreateCart($session->id);

    $this->assertInstanceOf(Cart::class, $cart);
    $this->assertEquals($cart->session_id, $session->id);
});

test('has no valid cart', function () {
    $session = $this->createSession();

    $cart = $this->cartRepo->hasValidCart($session->id);

    $this->assertNull($cart);
});

test('has valid cart', function () {
    $session = $this->createSession();
    $this->createCart();

    $cart = $this->cartRepo->hasValidCart($session->id);

    $this->assertInstanceOf(Cart::class, $cart);
    $this->assertEquals($cart->session_id, $session->id);
});
