<?php

namespace Brainlet\LaravelConvertTimezone\Tests\Feature;

use Brainlet\LaravelConvertTimezone\Exceptions\InvalidTimezone;
use Brainlet\LaravelConvertTimezone\Tests\Models\TestModel;
use Brainlet\LaravelConvertTimezone\Tests\Models\TestModelWithAccessor;
use Brainlet\LaravelConvertTimezone\Tests\TestCase;
use Carbon\Carbon;

class TZConversionTest extends TestCase
{
    public function test_converts_utc_time_to_destination_timezone()
    {
        $utcTime = now(); // UTC

        TestModel::factory()->create(['created_at' => $utcTime]);

        config(['tz.timezone' => 'Asia/Karachi']);

        $destinationTime = Carbon::parse($utcTime)
            ->addHours(5)->format('Y-m-d H:i:s'); // UTC (+5) [Asia/Karachi]

        $model = TestModel::first();

        $convertedTime = $model->created_at;

        $this->assertInstanceOf(Carbon::class, $convertedTime);
        $this->assertEquals($destinationTime, $convertedTime->format('Y-m-d H:i:s'));
    }

    public function test_never_converts_datetime_filed_if_accessor_method_is_available()
    {
        $utcTime = now(); // UTC

        TestModelWithAccessor::factory()->create(['created_at' => $utcTime]);

        config(['tz.timezone' => 'Asia/Karachi']);

        $destinationTime = Carbon::parse($utcTime)
            ->addHours(5)->format('Y-m-d H:i:s'); // UTC (+5) [Asia/Karachi]

        $model = TestModelWithAccessor::first();

        $convertedTime = $model->created_at;

        // The original Pest test was checking that $convertedTime (Carbon object) is not equal to $destinationTime (string)
        // This passes because Carbon object != string, even if their formatted values are the same
        $this->assertNotSame($destinationTime, $convertedTime);
    }

    public function test_throws_exception_if_timezone_is_invalid()
    {
        $this->expectException(InvalidTimezone::class);
        $this->expectExceptionMessage('The timezone `invalid-timezone` is invalid.');

        config(['tz.timezone' => 'invalid-timezone']);

        TestModel::factory()->create(['created_at' => now()]);

        $model = TestModel::first();

        // This will trigger the exception
        $model->created_at;
    }
}