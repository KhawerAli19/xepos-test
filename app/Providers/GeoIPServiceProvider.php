<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use GeoIp2\Database\Reader;

class GeoIPServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('geoip', function ($app) {
            return new Reader(database_path('geoip/GeoLite2-Country.mmdb'));
        });
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
