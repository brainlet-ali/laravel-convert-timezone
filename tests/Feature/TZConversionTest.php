<?php

namespace Brainlet\LaravelConvertTimezone\Tests\Feature;

use Brainlet\LaravelConvertTimezone\Exceptions\InvalidTimezone;
use Brainlet\LaravelConvertTimezone\Models\TestModel;
use Brainlet\LaravelConvertTimezone\Models\TestModelWithAccessor;
use Brainlet\LaravelConvertTimezone\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TZConversionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_converts_utc_time_to_asked_timezone()
    {
        $utcTime = now(); // UTC

        $model = TestModel::factory()->create(['created_at' => $utcTime]);

        config(['tz.timezone' => 'Asia/Karachi']);

        $destinationTime = Carbon::parse($utcTime)
          ->addHours(5)->format('Y-m-d H:i:s'); // UTC (+5) [Asia/Karachi]

        $convertedTime = $model->created_at;

        $this->assertInstanceOf(Carbon::class, $convertedTime);

        $this::assertSame($destinationTime, $convertedTime->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_never_converts_filed_if_accessor_method_is_available()
    {
        $utcTime = now(); // UTC

        $model = TestModelWithAccessor::factory()->create(['created_at' => $utcTime]);

        config(['tz.timezone' => 'Asia/Karachi']);

        $destinationTime = Carbon::parse($utcTime)
            ->addHours(5)->format('Y-m-d H:i:s'); // UTC (+5) [Asia/Karachi]

        $convertedTime = $model->created_at;

        $this->assertNotInstanceOf(Carbon::class, $convertedTime);

        $this::assertNotSame($destinationTime, $convertedTime);
    }

    /** @test */
    public function it_throws_exception_if_timezone_is_invalid()
    {
        $this->expectException(InvalidTimezone::class);
        $this->expectExceptionMessage('The timezone `invalid-timezone` is invalid.');

        config(['tz.timezone' => 'invalid-timezone']);

        $model = TestModel::factory()->create(['created_at' => now()]);

        $this->assertNotInstanceOf(Carbon::class, $model->created_at);
    }
}
