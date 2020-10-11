<?php

namespace Brainlet\LaravelConvertTimezone\Database\Factories;

use Brainlet\LaravelConvertTimezone\Tests\Models\MyModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class MyModelFactory extends Factory
{
    protected $model = MyModel::class;

    public function definition()
    {
        return [
            // silence is golden
        ];
    }
}

