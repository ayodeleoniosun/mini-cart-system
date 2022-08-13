<?php

namespace Tests\Unit\Service;

use App\Repositories\Interfaces\CartRepositoryInterface;
use App\Repositories\Interfaces\SessionRepositoryInterface;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $cartRepo = \Mockery::mock(CartRepositoryInterface::class);
    $sessionRepo = \Mockery::mock(SessionRepositoryInterface::class);

    $this->cartService = new CartService($cartRepo, $sessionRepo);
});

it('can add cart items', function () {
    $product = $this->createProduct();

    $data = [
        'ip_address' => '127.0.0.1',
        'user_agent' => $this->faker->userAgent,
        'product_id' => $product->id,
    ];

    $service = $this->cartService->shouldReceive(':getOrCreateSession')->andReturnSelf();
    dd($service);

//    $this->getMockBuilder(CartService::class)
//        ->addMethods(['getOrCreateSession', 'itemExistInCart'])
//        ->getMock();
//
//    $service = $this->cartService->add($data);
//    dd($service);
});
