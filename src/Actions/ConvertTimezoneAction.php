<?php

namespace Brainlet\LaravelConvertTimezone\Actions;

use Brainlet\LaravelConvertTimezone\Exceptions\InvalidTimezone;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Exception;

class ConvertTimezoneAction
{
    /**
     * Convert a datetime value to the specified timezone.
     *
     * @throws InvalidTimezone
     */
    public function execute($value, string $timezone): Carbon
    {
        try {
            $carbon = new Carbon($value);
            $carbonTimezone = new CarbonTimeZone($timezone);
            
            return $carbon->setTimezone($carbonTimezone);
        } catch (Exception $e) {
            throw InvalidTimezone::make($timezone);
        }
    }
}
