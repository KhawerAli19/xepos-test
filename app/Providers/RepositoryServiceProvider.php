<?php

namespace App\Providers;

use App\Interfaces\UserInterface;
use App\Repositories\UserRepository;
use App\Interfaces\UserAuthInterface;
use App\Repositories\StretchRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\UserAuthRepository;
use App\Interfaces\StretchRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(StretchRepositoryInterface::class, StretchRepository::class);
        $this->app->bind(UserInterface::class, UserRepository::class);
        $this->app->bind(UserAuthInterface::class, UserAuthRepository::class);
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
