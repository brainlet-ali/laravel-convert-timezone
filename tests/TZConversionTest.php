<?php

namespace Brainlet\LaravelConvertTimezone\Tests;

use Brainlet\LaravelConvertTimezone\Models\MyModel;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TZConversionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_converts_utc_to_asked_timezone()
    {
        $this->withoutExceptionHandling();
        $model = MyModel::factory()->create();

        $UTCDateTime = Carbon::now(); // UTC

        $model->update(['created_at' => $UTCDateTime]);

        $createdAtFromUTCToTimezone = Carbon::parse($UTCDateTime)
            ->addHours(5); // UTC (+5) [Asia/Karachi]

        $this::assertEquals(
            $createdAtFromUTCToTimezone->format('Y-m-d H:i:s'),
            $model->created_at->format('Y-m-d H:i:s')
        );
    }
}
