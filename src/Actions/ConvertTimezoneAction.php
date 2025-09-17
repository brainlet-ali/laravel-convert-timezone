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
    public function execute($value, string $timezone): ?Carbon
    {
        // Return null if value is null
        if ($value === null) {
            return null;
        }

        // Validate timezone first
        try {
            $carbonTimezone = new CarbonTimeZone($timezone);
        } catch (Exception $e) {
            throw InvalidTimezone::make($timezone);
        }

        // If already Carbon, just change timezone
        if ($value instanceof Carbon) {
            return $value->setTimezone($carbonTimezone);
        }

        // Otherwise create Carbon instance and set timezone
        // Let Carbon's exceptions bubble up for invalid date formats
        $carbon = new Carbon($value);
        return $carbon->setTimezone($carbonTimezone);
    }
}
