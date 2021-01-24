<?php

namespace Brainlet\LaravelConvertTimezone\Tests\Feature;

use Brainlet\LaravelConvertTimezone\Tests\Models\TestModel;
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

        $asiaKarachiTZ = Carbon::parse($UTCDateTime)
          ->addHours(5)->format('Y-m-d H:i:s'); // UTC (+5) [Asia/Karachi]

        // the config/tz.php is set to UTC (+5), asia/karachi timezone
        $convertedTZ = $model->created_at->format('Y-m-d H:i:s');

        $this::assertSame($asiaKarachiTZ, $convertedTZ);
    }
}
