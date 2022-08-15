<?php

namespace App\Providers\Repositories;

use App\Repositories\CartItemRepository;
use App\Repositories\Interfaces\CartItemRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class CartItemRepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            CartItemRepositoryInterface::class,
            CartItemRepository::class
        );
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
