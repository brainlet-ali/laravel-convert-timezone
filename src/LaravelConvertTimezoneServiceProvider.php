<?php

namespace Brainlet\LaravelConvertTimezone;

use Illuminate\Support\ServiceProvider;

class LaravelConvertTimezoneServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->offerPublishing();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/tz.php',
            'tz'
        );
    }

    protected function offerPublishing()
    {
        if (! function_exists('config_path')) {
            // function not available and 'publish' not relevant in Lumen
            return;
        }

        $this->publishes([
            __DIR__.'/../config/tz.php' => config_path('tz.php'),
        ], 'tz-config');
    }
}
