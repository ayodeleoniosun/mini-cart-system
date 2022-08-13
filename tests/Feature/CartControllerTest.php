<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

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

    $response = $this->getJson($this->baseUrl);
    $responseJson = json_decode($response->content());

    $this->assertEquals('error', $responseJson->status);
    $this->assertEquals('You have not added any item to cart.', $responseJson->message);
    $response->assertNotFound();
});

test('can get all user cart items', function () {
    $count = 5;
    $products = $this->createProduct($count);
    $this->createSession();
    $this->createCartItems($products->pluck('id')->toArray());

    $response = $this->getJson($this->baseUrl);
    $responseJson = json_decode($response->content());

    $this->assertEquals(count($responseJson->data), $count);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'product', 'session', 'created_at', 'updated_at'],
            ]
        ]);
});

test('user has no deleted cart item', function () {
    $this->createSession();

    $response = $this->getJson($this->baseUrl . '?deleted=true');
    $responseJson = json_decode($response->content());

    $this->assertEquals('error', $responseJson->status);
    $this->assertEquals('You have not removed any item from your cart.', $responseJson->message);
    $response->assertNotFound();
});

test('can get user deleted cart items', function () {

    //delete cart item
    $this->createProduct();
    $this->createSession();
    $cart = $this->createCart();

    $this->deleteJson($this->baseUrl . '/' . $cart->id);

    //get deleted items
    $response = $this->getJson($this->baseUrl . '?deleted=true');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'product', 'session', 'created_at', 'updated_at', 'deleted_at'],
            ]
        ]);
});

// tests for store method starts here

test('cannot add invalid product to cart', function () {
    $data = [
        'ip_address' => $this->faker->ipv4(),
        'user_agent' => $this->faker->userAgent,
        'product_id' => 2,
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

test('cannot add existing item to cart', function () {
    $product = $this->createProduct();
    $this->createSession();
    $this->createCart();

    $data = [
        'ip_address' => '127.0.0.1',
        'user_agent' => $this->faker->userAgent,
        'product_id' => $product->id,
    ];

    $response = $this->postJson($this->baseUrl, $data);
    $responseJson = json_decode($response->content());

    $this->assertEquals('error', $responseJson->status);
    $this->assertEquals('Item already added to cart.', $responseJson->message);
    $response->assertStatus(400);
});

test('can add new item to cart', function () {
    $product = $this->createProduct();

    $data = [
        'ip_address' => $this->faker->ipv4(),
        'user_agent' => $this->faker->userAgent,
        'product_id' => $product->id,
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
                'id', 'product_id', 'session_id', 'created_at', 'updated_at',
            ],
        ]);
});

// tests for delete method starts here

test('cannot delete non-existent cart item', function () {
    $this->createSession();

    $response = $this->deleteJson($this->baseUrl . '/100');
    $responseJson = json_decode($response->content());

    $this->assertEquals('error', $responseJson->status);
    $this->assertEquals('Unable to delete item from cart. Check if the item exist.', $responseJson->message);
    $response->assertStatus(400);
});

test('can delete cart item', function () {
    $this->createProduct();
    $this->createSession();
    $cart = $this->createCart();

    $response = $this->deleteJson($this->baseUrl . '/' . $cart->id);
    $responseJson = json_decode($response->content());

    $this->assertEquals('success', $responseJson->status);
    $this->assertEquals('Item successfully removed from cart.', $responseJson->message);
    $response->assertOk();
});

// tests for deletedItems method starts here

test('got no deleted cart items', function () {
    $this->createSession();

    $response = $this->getJson($this->baseUrl . '/items/deleted');
    $responseJson = json_decode($response->content());

    $this->assertEquals('error', $responseJson->status);
    $this->assertEquals('No item has been deleted from cart', $responseJson->message);
    $response->assertNotFound();
});

test('can get all deleted cart items', function () {
    $noOfProducts = 7;
    $noOfCartItemsToDelete = 3;

    //create cart items
    $products = $this->createProduct($noOfProducts);
    $this->createSession();
    $carts = $this->createCartItems($products->pluck('id')->toArray());

    //delete the first 3 cart items
    asort($carts);
    $topThreeCartItems = array_slice($carts, 0, $noOfCartItemsToDelete);
    $this->deleteCartItems($topThreeCartItems);

    $response = $this->getJson($this->baseUrl . '/items/deleted');
    $responseJson = json_decode($response->content());

    $this->assertEquals(count($responseJson->data), count($topThreeCartItems));

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'product', 'session', 'created_at', 'updated_at', 'deleted_at'],
            ]
        ]);
});
