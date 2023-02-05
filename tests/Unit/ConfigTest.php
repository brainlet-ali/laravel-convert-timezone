<?php

it('checks if config file is present', function () {
    $config = config('tz');
    $this->assertTrue(true, count($config) > 0);
});

it('checks if config file has key timezone', function () {
    $configTimezone = config('tz.timezone');
    $this->assertEquals('UTC', $configTimezone);
});
