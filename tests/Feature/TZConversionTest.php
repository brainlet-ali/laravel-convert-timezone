<?php

use Brainlet\LaravelConvertTimezone\Exceptions\InvalidTimezone;
use Brainlet\LaravelConvertTimezone\Tests\Models\TestModel;
use Brainlet\LaravelConvertTimezone\Tests\Models\TestModelWithAccessor;
use Carbon\Carbon;

it('converts utc time to destination timezone', function () {
    $utcTime = now(); // UTC

    TestModel::factory()->create(['created_at' => $utcTime]);

    config(['tz.timezone' => 'Asia/Karachi']);

    $destinationTime = Carbon::parse($utcTime)
        ->addHours(5)->format('Y-m-d H:i:s'); // UTC (+5) [Asia/Karachi]

    $model = TestModel::first();

    $convertedTime = $model->created_at;

    expect($convertedTime)
        ->toBeInstanceOf(Carbon::class)
        ->and($convertedTime->format('Y-m-d H:i:s'))->toBe($destinationTime);
});

it('never converts date/time filed if accessor method is available', function () {
    $utcTime = now(); // UTC

    TestModelWithAccessor::factory()->create(['created_at' => $utcTime]);

    config(['tz.timezone' => 'Asia/Karachi']);

    $destinationTime = Carbon::parse($utcTime)
        ->addHours(5)->format('Y-m-d H:i:s'); // UTC (+5) [Asia/Karachi]

    $model = TestModelWithAccessor::first();

    $convertedTime = $model->created_at;

    expect($convertedTime)->not->toBe($destinationTime);
});

it('throws exception if timezone is invalid', function () {
    config(['tz.timezone' => 'invalid-timezone']);

    TestModel::factory()->create(['created_at' => now()]);

    $model = TestModel::first();

    expect($model->created_at)
        ->not->toBeInstanceOf(Carbon::class);
})->throws(InvalidTimezone::class,
    'The timezone `invalid-timezone` is invalid.');
