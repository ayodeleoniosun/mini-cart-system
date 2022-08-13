<?php

namespace App\Providers\Repositories;

use App\Repositories\Interfaces\SessionRepositoryInterface;
use App\Repositories\SessionRepository;
use Illuminate\Support\ServiceProvider;

class SessionRepositoryProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            SessionRepositoryInterface::class,
            SessionRepository::class
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
