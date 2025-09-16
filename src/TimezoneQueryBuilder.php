<?php

namespace Brainlet\LaravelConvertTimezone;

use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Standalone timezone query builder for use without Eloquent models.
 * Provides timezone-aware query capabilities for raw database queries.
 */
class TimezoneQueryBuilder
{
    /**
     * Apply timezone-aware datetime filter to a query.
     *
     * @throws InvalidArgumentException
     */
    public static function whereInTimezone(Builder $query, string $column, string $operator, $value, ?string $timezone = null): Builder
    {
        $timezone = self::validateAndGetTimezone($timezone);
        
        // Parse the datetime in the specified timezone, then convert to UTC
        $carbonDate = $value instanceof Carbon ? $value : Carbon::parse($value, $timezone);
        $utcDate = $carbonDate->copy()->setTimezone('UTC');
        
        return $query->where($column, $operator, $utcDate);
    }

    /**
     * Apply timezone-aware date filter to a query.
     *
     * @throws InvalidArgumentException
     */
    public static function whereDateInTimezone(Builder $query, string $column, $date, ?string $timezone = null): Builder
    {
        $timezone = self::validateAndGetTimezone($timezone);
        
        // Get the full day range in the specified timezone
        $startOfDay = Carbon::parse($date, $timezone)->startOfDay();
        $endOfDay = $startOfDay->copy()->endOfDay();
        
        $utcStart = $startOfDay->copy()->setTimezone('UTC');
        $utcEnd = $endOfDay->copy()->setTimezone('UTC');
        
        return $query->whereBetween($column, [$utcStart, $utcEnd]);
    }

    /**
     * Apply timezone-aware time filter to a query.
     *
     * @throws InvalidArgumentException
     */
    public static function whereTimeInTimezone(Builder $query, string $column, string $operator, string $time, ?string $timezone = null): Builder
    {
        $timezone = self::validateAndGetTimezone($timezone);
        $driver = $query->getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Use datetime modifiers with seconds offset
            $offsetSeconds = (new DateTimeZone($timezone))->getOffset(new \DateTime('2024-01-01'));

            return $query->whereRaw(
                "TIME(datetime({$column}, '{$offsetSeconds} seconds')) {$operator} ?",
                [$time]
            );
        }

        // MySQL/PostgreSQL: Use CONVERT_TZ function
        return $query->whereRaw(
            "TIME(CONVERT_TZ({$column}, '+00:00', ?)) {$operator} ?",
            [self::getTimezoneOffset($timezone), $time]
        );
    }

    /**
     * Apply timezone-aware between dates filter to a query.
     *
     * @throws InvalidArgumentException
     */
    public static function whereBetweenInTimezone(Builder $query, string $column, array $values, ?string $timezone = null): Builder
    {
        $timezone = self::validateAndGetTimezone($timezone);
        
        $start = Carbon::parse($values[0], $timezone)->startOfDay();
        $end = Carbon::parse($values[1], $timezone)->endOfDay();
        
        $utcStart = $start->copy()->setTimezone('UTC');
        $utcEnd = $end->copy()->setTimezone('UTC');
        
        return $query->whereBetween($column, [$utcStart, $utcEnd]);
    }

    /**
     * Apply timezone-aware month filter to a query.
     *
     * @throws InvalidArgumentException
     */
    public static function whereMonthInTimezone(Builder $query, string $column, int $month, ?string $timezone = null): Builder
    {
        $timezone = self::validateAndGetTimezone($timezone);
        $driver = $query->getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Use strftime with timezone offset
            $year = date('Y');
            $testDate = "{$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-15";
            $offsetSeconds = (new DateTimeZone($timezone))->getOffset(new \DateTime($testDate));
            
            return $query->whereRaw(
                "CAST(strftime('%m', datetime({$column}, '{$offsetSeconds} seconds')) AS INTEGER) = ?",
                [$month]
            );
        }

        // MySQL/PostgreSQL: Use MONTH with CONVERT_TZ
        return $query->whereRaw(
            "MONTH(CONVERT_TZ({$column}, '+00:00', ?)) = ?",
            [self::getTimezoneOffset($timezone), $month]
        );
    }

    /**
     * Apply timezone-aware year filter to a query.
     *
     * @throws InvalidArgumentException
     */
    public static function whereYearInTimezone(Builder $query, string $column, int $year, ?string $timezone = null): Builder
    {
        $timezone = self::validateAndGetTimezone($timezone);
        $driver = $query->getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite: Handle year boundary with appropriate offset
            $tz = new DateTimeZone($timezone);
            
            // Check offsets at year boundaries for DST handling
            $offsetJan = $tz->getOffset(new \DateTime("{$year}-01-01"));
            $offsetDec = $tz->getOffset(new \DateTime("{$year}-12-31"));
            $offsetPrevDec = $tz->getOffset(new \DateTime(($year - 1) . "-12-31"));
            $offsetNextJan = $tz->getOffset(new \DateTime(($year + 1) . "-01-01"));
            
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
            [self::getTimezoneOffset($timezone), $year]
        );
    }

    /**
     * Convert a datetime value from UTC to the specified timezone.
     *
     * @throws InvalidArgumentException
     */
    public static function convertFromUTC($value, ?string $timezone = null): ?Carbon
    {
        $timezone = self::validateAndGetTimezone($timezone);
        
        if (!$value) {
            return null;
        }
        
        return Carbon::parse($value, 'UTC')->setTimezone($timezone);
    }

    /**
     * Convert a datetime value to UTC from the specified timezone.
     *
     * @throws InvalidArgumentException
     */
    public static function convertToUTC($value, ?string $timezone = null): ?Carbon
    {
        $timezone = self::validateAndGetTimezone($timezone);
        
        if (!$value) {
            return null;
        }
        
        return Carbon::parse($value, $timezone)->setTimezone('UTC');
    }

    /**
     * Validate and return the timezone, falling back to config default.
     *
     * @throws InvalidArgumentException When timezone is invalid
     */
    protected static function validateAndGetTimezone(?string $timezone = null): string
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
     * Calculate the timezone offset in MySQL-compatible format.
     */
    protected static function getTimezoneOffset(string $timezone): string
    {
        $tz = new DateTimeZone($timezone);
        $offset = $tz->getOffset(new \DateTime);
        $hours = floor(abs($offset) / 3600);
        $minutes = floor((abs($offset) % 3600) / 60);
        $sign = $offset >= 0 ? '+' : '-';
        
        return sprintf('%s%02d:%02d', $sign, $hours, $minutes);
    }
}