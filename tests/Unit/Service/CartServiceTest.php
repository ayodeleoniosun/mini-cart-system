<?php

namespace Tests\Unit\Service;

use App\Exceptions\CustomException;
use App\Http\Resources\CartItemCollection;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Session;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\SessionRepositoryInterface;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

uses(RefreshDatabase::class);
uses()->group('cart_service_test');

beforeEach(function () {
    $this->identifier = '12345ab';
    $this->request = \Mockery::mock(Request::class)->makePartial();
    $this->cartRepo = \Mockery::mock(CartRepositoryInterface::class);
    $this->cartItemRepo = \Mockery::mock(CartItemRepositoryInterface::class);
    $this->sessionRepo = \Mockery::mock(SessionRepositoryInterface::class);

    $this->cart = \Mockery::mock(Cart::class)->makePartial();
    $this->cartItem = \Mockery::mock(CartItem::class)->makePartial();
    $this->session = \Mockery::mock(Session::class)->makePartial();

    $this->service = new CartService($this->cartRepo, $this->cartItemRepo, $this->sessionRepo);
});

test('can add cart items', function () {
    $product = $this->createProduct();
    $this->session->id = 1;
    $this->cart->id = 1;

    $data = [
        'identifier' => $this->identifier,
        'user_agent' => $this->faker->userAgent,
        'product_id' => $product->id,
        'quantity'   => 1,
    ];

    $this->cartItem->id = 1;
    $this->cartItem->cart_id = $this->cart->id;
    $this->cartItem->product_id = $data['product_id'];
    $this->cartItem->quantity = $data['quantity'];

    $this->sessionRepo->shouldReceive('getOrCreateSession')
        ->once()
        ->with($data['identifier'], $data['user_agent'])
        ->andReturn($this->session);

    $this->cartRepo->shouldReceive('getOrCreateCart')
        ->once()
        ->with($this->session->id)
        ->andReturn($this->cart);

    $this->cartItemRepo->shouldReceive('getCartItem')
        ->once()
        ->with($this->cart->id, $data['product_id'])
        ->andReturn(null);

    $cartItemData = [
        'cart_id'    => $this->cart->id,
        'product_id' => $product->id,
        'quantity'   => 1,
    ];

    $this->cartItemRepo->shouldReceive('add')
        ->once()
        ->with($cartItemData)
        ->andReturn($this->cartItem);

    $response = $this->service->addCartItems($data);
    $this->assertInstanceOf(CartItem::class, $response);
});

test('update existing cart item', function () {
    $product = $this->createProduct();
    $this->session->id = 1;
    $this->cart->id = 1;

    $data = [
        'identifier' => $this->identifier,
        'user_agent' => $this->faker->userAgent,
        'product_id' => $product->id,
    ];

    $this->cartItem->id = 1;
    $this->cartItem->cart_id = $this->cart->id;
    $this->cartItem->product_id = $data['product_id'];

    $this->sessionRepo->shouldReceive('getOrCreateSession')
        ->once()
        ->with($data['identifier'], $data['user_agent'])
        ->andReturn($this->session);

    $this->cartRepo->shouldReceive('getOrCreateCart')
        ->once()
        ->with($this->session->id)
        ->andReturn($this->cart);

    $this->cartItemRepo->shouldReceive('getCartItem')
        ->once()
        ->with($this->cart->id, $data['product_id'])
        ->andReturn($this->cartItem);

    $cartItemData = [
        'cart_id'    => $this->cart->id,
        'product_id' => $product->id,
        'quantity'   => 5,
    ];

    $this->cartItemRepo->shouldReceive('update')
        ->once()
        ->with($cartItemData)
        ->andReturn($this->cartItem);

    $data['quantity'] = $cartItemData['quantity'];

    $response = $this->service->addCartItems($data);
    $this->assertInstanceOf(CartItem::class, $response);
});

test('has no session record', function () {
    $this->session->id = 1;

    $this->sessionRepo->shouldReceive('getSessionByIdentifier')
        ->once()
        ->with($this->identifier)
        ->andReturn(null);

    $this->expectException(CustomException::class);
    $this->expectExceptionMessage('Invalid account.');
    $this->service->hasValidCart($this->identifier);
});

test('has no cart item', function () {
    $this->session->id = 1;

    $this->sessionRepo->shouldReceive('getSessionByIdentifier')
        ->once()
        ->with($this->identifier)
        ->andReturn($this->session);

    $this->cartRepo->shouldReceive('hasValidCart')
        ->once()
        ->with($this->session->id)
        ->andReturn(null);

    $this->expectException(CustomException::class);
    $this->expectExceptionMessage('No cart yet.');
    $this->service->hasValidCart($this->identifier);
});

test('can delete existing cart item', function () {
    $this->session->id = 1;
    $this->cart->id = 1;
    $this->cartItem->id = 1;

    $this->sessionRepo->shouldReceive('getSessionByIdentifier')
        ->once()
        ->with($this->identifier)
        ->andReturn($this->session);

    $this->cartRepo->shouldReceive('hasValidCart')
        ->once()
        ->with($this->session->id)
        ->andReturn($this->cart);

    $this->cartItemRepo->shouldReceive('delete')
        ->once()
        ->with($this->cartItem->id, $this->cart->id)
        ->andReturn(true);

    $response = $this->service->delete($this->identifier, $this->cartItem->id);
    $this->assertTrue($response);
});

test('cannot delete non-existent cart item', function () {
    $this->session->id = 1;
    $this->cart->id = 1;
    $this->cartItem->id = 1;

    $this->sessionRepo->shouldReceive('getSessionByIdentifier')
        ->once()
        ->with($this->identifier)
        ->andReturn($this->session);

    $this->cartRepo->shouldReceive('hasValidCart')
        ->once()
        ->with($this->session->id)
        ->andReturn($this->cart);

    $this->cartItemRepo->shouldReceive('delete')
        ->once()
        ->with($this->cartItem->id, $this->cart->id)
        ->andReturn(false);

    $this->expectException(CustomException::class);
    $this->expectExceptionMessage('Unable to delete item from cart. Check if the item exist.');
    $this->service->delete($this->identifier, $this->cartItem->id);
});

test('get user cart items', function () {
    $this->session->id = 1;
    $this->cart->id = 1;

    $this->sessionRepo->shouldReceive('getSessionByIdentifier')
        ->once()
        ->with($this->identifier)
        ->andReturn($this->session);

    $this->cartRepo->shouldReceive('hasValidCart')
        ->once()
        ->with($this->session->id)
        ->andReturn($this->cart);

    $pagination = \Mockery::mock(LengthAwarePaginator::class)->makePartial();
    $pagination->shouldReceive('total')->andReturn(1);

    $this->cartItemRepo->shouldReceive('getUserCartItems')
        ->once()
        ->with($this->cart)
        ->andReturn($pagination);

    $request = new Request();
    $request->server->add(['REMOTE_ADDR' => $this->identifier]);

    $response = $this->service->getUserCartItems($request);
    $this->assertInstanceOf(CartItemCollection::class, $response);
})->skip('Inability to assert instance of CartItemCollection');

test('get no deleted cart items', function () {
    $pagination = \Mockery::mock(LengthAwarePaginator::class)->makePartial();
    $pagination->shouldReceive('total')->once()->andReturn(0);

    $this->cartItemRepo->shouldReceive('getDeletedCartItems')
        ->once()
        ->andReturn($pagination);

    $response = $this->service->getDeletedCartItems();
    $this->assertInstanceOf(CartItemCollection::class, $response);
})->skip('Inability to assert instance of CartItemCollection');

test('can get all deleted cart items', function () {
    $pagination = \Mockery::mock(LengthAwarePaginator::class)->makePartial();
    $pagination->shouldReceive('total')->andReturn(1);

    $this->cartItemRepo->shouldReceive('getDeletedCartItems')
        ->once()
        ->andReturn($pagination);

    $response = $this->service->getDeletedCartItems();
    $this->assertInstanceOf(CartItemCollection::class, $response);
})->skip('Inability to assert instance of CartItemCollection');
