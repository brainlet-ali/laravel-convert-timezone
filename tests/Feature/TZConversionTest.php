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
        $UTCDateTime = Carbon::now(); // UTC

        $model = TestModel::factory()->create(['created_at' => $UTCDateTime]);

        config(['tz.timezone' => 'Asia/Karachi']);

        $asiaKarachiTZ = Carbon::parse($UTCDateTime)
          ->addHours(5)->format('Y-m-d H:i:s'); // UTC (+5) [Asia/Karachi]

        $convertedTZ = $model->created_at->format('Y-m-d H:i:s');

        $this::assertSame($asiaKarachiTZ, $convertedTZ);
    }

    /** @test */
    public function it_never_converts_filed_if_accessor_method_is_available()
    {
        $UTCDateTime = Carbon::now(); // UTC

        $model = TestModelWithAccessor::factory()->create(['created_at' => $UTCDateTime]);

        $this->assertNotInstanceOf(Carbon::class, $model->created_at);
        $this->assertIsString($model->created_at);
    }

    /** @test */
    public function it_throws_exception_if_timezone_is_invalid()
    {
        $this->expectException(InvalidTimezone::class);
        $this->expectExceptionMessage('The timezone `invalid-timezone` is invalid.');

        config(['tz.timezone' => 'invalid-timezone']);

        $model = TestModel::factory()->create(['created_at' => now()]);

        echo $model->created_at;
    }
}
