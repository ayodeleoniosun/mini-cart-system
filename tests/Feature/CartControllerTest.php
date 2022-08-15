<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
uses()->group('integration');

// tests for index method starts here

test('invalid account cannot view cart items', function () {
    $response = $this->getJson($this->baseUrl);
    $responseJson = json_decode($response->content());

    $this->assertEquals('error', $responseJson->status);
    $this->assertEquals('Invalid account.', $responseJson->message);
    $response->assertForbidden();
});

test('valid account that has no cart item', function () {
    $this->createSession();
    $this->createCart();

    $response = $this->getJson($this->baseUrl);
    $responseJson = json_decode($response->content());

    $this->assertEquals(count($responseJson->data), 0);
    $response->assertOk();
});

test('can get all user cart items', function () {
    $count = 5;
    $products = $this->createProduct($count);
    $this->createSession();
    $cart = $this->createCart();
    $this->createCartItems($products->pluck('id')->toArray(), $cart);

    $response = $this->getJson($this->baseUrl);
    $responseJson = json_decode($response->content());

    $this->assertEquals(count($responseJson->data), $count);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'product', 'created_at', 'updated_at'],
            ]
        ]);
});

// tests for store method starts here

test('cannot add invalid product to cart', function () {
    $data = [
        'ip_address' => $this->faker->ipv4(),
        'user_agent' => $this->faker->userAgent,
        'product_id' => 2,
        'quantity'   => 2
    ];

    $response = $this->postJson($this->baseUrl, $data);
    $responseJson = json_decode($response->content());

    $this->assertEquals('The selected product id is invalid.', $responseJson->message);

    $response->assertUnprocessable()
        ->assertJsonStructure([
            'message',
            'errors' => [
                'product_id' => []
            ],
        ]);
});

test('cannot add item with no quantity to cart', function () {
    $data = [
        'ip_address' => $this->faker->ipv4(),
        'user_agent' => $this->faker->userAgent,
        'product_id' => 2,
    ];

    $response = $this->postJson($this->baseUrl, $data);
    $responseJson = json_decode($response->content());

    $this->assertEquals('The quantity field is required.', $responseJson->errors->quantity[0]);

    $response->assertUnprocessable()
        ->assertJsonStructure([
            'message',
            'errors' => [
                'quantity' => []
            ],
        ]);
});

test('can update existing cart item', function () {
    $product = $this->createProduct();
    $this->createSession();
    $cart = $this->createCart();
    $cartItem = $this->createCartItems([$product->id], $cart);

    $newQuantity = 2;
    $updatedQuantity = $cartItem[0]->quantity + $newQuantity;

    $data = [
        'ip_address' => '127.0.0.1',
        'user_agent' => $this->faker->userAgent,
        'product_id' => $cartItem[0]->product_id,
        'quantity'   => $newQuantity
    ];

    $response = $this->postJson($this->baseUrl, $data);
    $responseJson = json_decode($response->content());

    $this->assertEquals('success', $responseJson->status);
    $this->assertEquals('Item successfully added to cart.', $responseJson->message);
    $this->assertEquals($updatedQuantity, $responseJson->data->quantity);
    $response->assertStatus(201);
});

test('can add new item to cart', function () {
    $product = $this->createProduct();

    $data = [
        'ip_address' => $this->faker->ipv4(),
        'user_agent' => $this->faker->userAgent,
        'product_id' => $product->id,
        'quantity'   => 2,
    ];

    $response = $this->postJson($this->baseUrl, $data);
    $responseJson = json_decode($response->content());

    $this->assertEquals('success', $responseJson->status);
    $this->assertEquals('Item successfully added to cart.', $responseJson->message);
    $this->assertEquals($responseJson->data->product_id, $product->id);

    $response->assertCreated()
        ->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'id', 'product_id', 'cart_id', 'quantity', 'created_at', 'updated_at',
            ],
        ]);
});

// tests for delete method starts here

test('authorize user cart before deleting cart item', function () {
    $this->createSession();

    $response = $this->deleteJson($this->baseUrl . '/100');
    $responseJson = json_decode($response->content());

    $this->assertEquals('error', $responseJson->status);
    $this->assertEquals('No cart yet.', $responseJson->message);
    $response->assertForbidden();
});

test('cannot delete non-existent cart item', function () {
    $this->createSession();
    $this->createCart();

    $response = $this->deleteJson($this->baseUrl . '/100');
    $responseJson = json_decode($response->content());

    $this->assertEquals('error', $responseJson->status);
    $this->assertEquals('Unable to delete item from cart. Check if the item exist.', $responseJson->message);
    $response->assertStatus(400);
});

test('can delete cart item', function () {
    $product = $this->createProduct();
    $this->createSession();
    $cart = $this->createCart();
    $cartItem = $this->createCartItems([$product->id], $cart);

    $response = $this->deleteJson($this->baseUrl . '/' . $cartItem[0]->id);
    $response->assertNoContent();
});

// tests for deletedItems method starts here

test('got no deleted cart items', function () {
    $this->createSession();

    $response = $this->getJson($this->baseUrl . '/items/deleted');
    $responseJson = json_decode($response->content());

    $this->assertEquals(count($responseJson->data), 0);
    $response->assertOk();
});

test('can get all deleted cart items', function () {
    $noOfProducts = 7;
    $noOfCartItemsToDelete = 3;

    //create cart items
    $products = $this->createProduct($noOfProducts);
    $this->createSession();
    $cart = $this->createCart();

    $cartItems = $this->createCartItems($products->pluck('id')->toArray(), $cart);

    //delete the first 3 cart items
    $topThreeCartItems = array_slice($cartItems, 0, $noOfCartItemsToDelete);

    foreach ($topThreeCartItems as $cartItem) {
        $this->deleteJson($this->baseUrl . '/' . $cartItem->id);
    }

    $response = $this->getJson($this->baseUrl . '/items/deleted');
    $responseJson = json_decode($response->content());

    $this->assertEquals(count($responseJson->data), count($topThreeCartItems));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'product', 'created_at', 'updated_at', 'deleted_at'],
            ]
        ]);
});
