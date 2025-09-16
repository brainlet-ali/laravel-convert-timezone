<?php

namespace Brainlet\LaravelConvertTimezone\Tests\Unit;

use Brainlet\LaravelConvertTimezone\Tests\TestCase;

class ConfigTest extends TestCase
{
    public function test_checks_if_config_file_is_present()
    {
        $config = config('tz');
        $this->assertTrue(count($config) > 0);
    }

    public function test_checks_if_config_file_has_key_timezone()
    {
        $configTimezone = config('tz.timezone');
        $this->assertEquals('UTC', $configTimezone);
    }
}