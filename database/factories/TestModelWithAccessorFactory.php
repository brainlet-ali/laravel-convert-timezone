<?php

namespace Brainlet\LaravelConvertTimezone\Database\Factories;

use Brainlet\LaravelConvertTimezone\Models\TestModelWithAccessor;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestModelWithAccessorFactory extends Factory
{
    protected $model = TestModelWithAccessor::class;

    public function definition()
    {
        return [
            // silence is golden
        ];
    }
}
