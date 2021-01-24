<?php

namespace Brainlet\LaravelConvertTimezone\Tests;

class ConfigTest extends TestCase
{

    /** @test */
    public function tz_config_file_is_present()
    {
        $config = config('tz');
        $this->assertTrue(true, count($config) > 0);
    }

    /** @test */
    public function it_has_key_timezone()
    {
        $configTimezone = config('tz.timezone');
        $this->assertEquals('Asia/Karachi', $configTimezone);
    }
}
