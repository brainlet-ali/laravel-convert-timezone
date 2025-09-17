<?php

namespace Brainlet\LaravelConvertTimezone;

use Illuminate\Support\ServiceProvider;

class TzServiceProvider extends ServiceProvider
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
        $this->publishes([
            __DIR__.'/../config/tz.php' => config_path('tz.php'),
        ], 'tz-config');
    }
}
