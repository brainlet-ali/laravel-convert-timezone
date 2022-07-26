<?php

namespace Brainlet\LaravelConvertTimezone\Database\Factories;

use Brainlet\LaravelConvertTimezone\Tests\Models\TestModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class TestModelFactory extends Factory
{
    protected $model = TestModel::class;

    public function definition()
    {
        return [
            // silence is golden
        ];
    }
}
