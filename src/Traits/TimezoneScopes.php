<?php

namespace Brainlet\LaravelConvertTimezone\Traits;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use InvalidArgumentException;

trait TimezoneScopes
{
    /**
     * Filter query results by a datetime column in a specific timezone.
     * Converts the provided datetime to UTC before comparison.
     *
     * @throws InvalidArgumentException
     */
    public function scopeWhereInTimezone($query, $column, $operator, $value, $timezone = null)
    {
        $timezone = $this->validateAndGetTimezone($timezone);

        // Parse the datetime in the specified timezone, then convert to UTC for storage
        $carbonDate = $value instanceof Carbon ? $value : Carbon::parse($value, $timezone);
        $utcDate = $carbonDate->copy()->setTimezone('UTC');

        return $query->where($column, $operator, $utcDate);
    }

    /**
     * Filter query results by date only (ignoring time) in a specific timezone.
     * Useful for finding all records on a specific date in the user's timezone.
     *
     * @throws InvalidArgumentException
     */
    public function scopeWhereDateInTimezone($query, $column, $date, $timezone = null)
    {
        $timezone = $this->validateAndGetTimezone($timezone);

        // Get the full day range in the specified timezone
        $startOfDay = Carbon::parse($date, $timezone)->startOfDay();
        $endOfDay = $startOfDay->copy()->endOfDay();

        $utcStart = $startOfDay->copy()->setTimezone('UTC');
        $utcEnd = $endOfDay->copy()->setTimezone('UTC');

        return $query->whereBetween($column, [$utcStart, $utcEnd]);
    }

    /**
     * Filter query results by time only (ignoring date) in a specific timezone.
     * Supports both MySQL (CONVERT_TZ) and SQLite (datetime modifiers).
     *
     * @throws InvalidArgumentException
     */
    public function scopeWhereTimeInTimezone($query, $column, $operator, $time, $timezone = null)
    {
        $timezone = $this->validateAndGetTimezone($timezone);
        $driver = $this->getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Use datetime modifiers with seconds offset
            $offsetSeconds = (new DateTimeZone($timezone))->getOffset(new DateTime('2024-01-01'));

            return $query->whereRaw(
                "TIME(datetime({$column}, '{$offsetSeconds} seconds')) {$operator} ?",
                [$time]
            );
        }

        // MySQL/PostgreSQL: Use CONVERT_TZ function
        return $query->whereRaw(
            "TIME(CONVERT_TZ({$column}, '+00:00', ?)) {$operator} ?",
            [$this->getTimezoneOffset($timezone), $time]
        );
    }

    /**
     * Filter query results between two dates in a specific timezone.
     * Both dates are converted to start/end of day in the specified timezone.
     *
     * @throws InvalidArgumentException
     */
    public function scopeWhereBetweenInTimezone($query, $column, array $values, $timezone = null)
    {
        $timezone = $this->validateAndGetTimezone($timezone);

        $start = Carbon::parse($values[0], $timezone)->startOfDay();
        $end = Carbon::parse($values[1], $timezone)->endOfDay();

        $utcStart = $start->copy()->setTimezone('UTC');
        $utcEnd = $end->copy()->setTimezone('UTC');

        return $query->whereBetween($column, [$utcStart, $utcEnd]);
    }

    /**
     * Filter query results by month (1-12) in a specific timezone.
     * Handles database-specific syntax for MySQL and SQLite.
     *
     * @throws InvalidArgumentException
     */
    public function scopeWhereMonthInTimezone($query, $column, $month, $timezone = null)
    {
        $timezone = $this->validateAndGetTimezone($timezone);
        $driver = $this->getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Use strftime with timezone offset
            $year = date('Y');
            $testDate = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-15";
            $offsetSeconds = (new DateTimeZone($timezone))->getOffset(new DateTime($testDate));
            
            return $query->whereRaw(
                "CAST(strftime('%m', datetime({$column}, '{$offsetSeconds} seconds')) AS INTEGER) = ?",
                [$month]
            );
        }

        // MySQL/PostgreSQL: Use MONTH with CONVERT_TZ
        return $query->whereRaw(
            "MONTH(CONVERT_TZ({$column}, '+00:00', ?)) = ?",
            [$this->getTimezoneOffset($timezone), $month]
        );
    }

    /**
     * Filter query results by year in a specific timezone.
     * Handles database-specific syntax for MySQL and SQLite.
     *
     * @throws InvalidArgumentException
     */
    public function scopeWhereYearInTimezone($query, $column, $year, $timezone = null)
    {
        $timezone = $this->validateAndGetTimezone($timezone);
        $driver = $this->getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Handle year boundary with appropriate offset
            $tz = new DateTimeZone($timezone);
            
            // Check offsets at year boundaries for DST handling
            $offsetJan = $tz->getOffset(new DateTime("{$year}-01-01"));
            $offsetDec = $tz->getOffset(new DateTime("{$year}-12-31"));
            $offsetPrevDec = $tz->getOffset(new DateTime(($year - 1) . "-12-31"));
            $offsetNextJan = $tz->getOffset(new DateTime(($year + 1) . "-01-01"));
            
            // Collect unique offsets that might affect year boundaries
            $offsets = array_unique([$offsetJan, $offsetDec, $offsetPrevDec, $offsetNextJan]);
            
            if (count($offsets) === 1) {
                // No DST changes affecting year boundary
                return $query->whereRaw(
                    "CAST(strftime('%Y', datetime({$column}, '{$offsets[0]} seconds')) AS INTEGER) = ?",
                    [$year]
                );
            }
            
            // Multiple offsets due to DST, check all possibilities
            $conditions = array_map(function($offset) use ($column) {
                return "CAST(strftime('%Y', datetime({$column}, '{$offset} seconds')) AS INTEGER) = ?";
            }, $offsets);
            
            return $query->whereRaw(
                '(' . implode(' OR ', $conditions) . ')',
                array_fill(0, count($offsets), $year)
            );
        }

        // MySQL/PostgreSQL: Use YEAR with CONVERT_TZ
        return $query->whereRaw(
            "YEAR(CONVERT_TZ({$column}, '+00:00', ?)) = ?",
            [$this->getTimezoneOffset($timezone), $year]
        );
    }

    /**
     * Validate and return the timezone, falling back to config default.
     * Ensures the timezone string is valid before use.
     *
     * @throws InvalidArgumentException When timezone is invalid
     */
    protected function validateAndGetTimezone($timezone = null)
    {
        $timezone = $timezone ?? config('tz.timezone', 'UTC');

        try {
            new DateTimeZone($timezone);
        } catch (\Exception $e) {
            throw new InvalidArgumentException("Invalid timezone: {$timezone}");
        }

        return $timezone;
    }

    /**
     * Calculate the timezone offset in MySQL-compatible format (+HH:MM or -HH:MM).
     * Used for CONVERT_TZ function in MySQL queries.
     */
    protected function getTimezoneOffset($timezone)
    {
        $tz = new DateTimeZone($timezone);
        $offset = $tz->getOffset(new \DateTime);
        $hours = floor(abs($offset) / 3600);
        $minutes = floor((abs($offset) % 3600) / 60);
        $sign = $offset >= 0 ? '+' : '-';

        return sprintf('%s%02d:%02d', $sign, $hours, $minutes);
    }
}
