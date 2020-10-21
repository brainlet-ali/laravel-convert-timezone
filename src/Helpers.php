<?php


namespace Brainlet\LaravelConvertTimezone;

class Helpers
{
    public static function isLaravel(): bool
    {
        return class_exists('Illuminate\Foundation\Application');
    }
}
