<?php

namespace Brainlet\LaravelConvertTimezone;

use Illuminate\Support\ServiceProvider;

class LaravelConvertTimezoneServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if (Helpers::isLaravel() && $this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/tz.php' => config_path('tz.php'),
            ], 'config');
        }

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/tz.php',
            'tz'
        );
    }
}
