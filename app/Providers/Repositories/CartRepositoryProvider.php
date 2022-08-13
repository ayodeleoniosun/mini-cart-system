<?php

namespace App\Providers\Repositories;

use App\Repositories\CartRepository;
use App\Repositories\Interfaces\CartRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class CartRepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            CartRepositoryInterface::class,
            CartRepository::class
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
