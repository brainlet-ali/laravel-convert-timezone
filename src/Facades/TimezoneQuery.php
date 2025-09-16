<?php

namespace Brainlet\LaravelConvertTimezone\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Database\Query\Builder whereInTimezone(\Illuminate\Database\Query\Builder $query, string $column, string $operator, $value, ?string $timezone = null)
 * @method static \Illuminate\Database\Query\Builder whereDateInTimezone(\Illuminate\Database\Query\Builder $query, string $column, $date, ?string $timezone = null)
 * @method static \Illuminate\Database\Query\Builder whereTimeInTimezone(\Illuminate\Database\Query\Builder $query, string $column, string $operator, string $time, ?string $timezone = null)
 * @method static \Illuminate\Database\Query\Builder whereBetweenInTimezone(\Illuminate\Database\Query\Builder $query, string $column, array $values, ?string $timezone = null)
 * @method static \Illuminate\Database\Query\Builder whereMonthInTimezone(\Illuminate\Database\Query\Builder $query, string $column, int $month, ?string $timezone = null)
 * @method static \Illuminate\Database\Query\Builder whereYearInTimezone(\Illuminate\Database\Query\Builder $query, string $column, int $year, ?string $timezone = null)
 * @method static \Carbon\Carbon|null convertFromUTC($value, ?string $timezone = null)
 * @method static \Carbon\Carbon|null convertToUTC($value, ?string $timezone = null)
 *
 * @see \Brainlet\LaravelConvertTimezone\TimezoneQueryBuilder
 */
class TimezoneQuery extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Brainlet\LaravelConvertTimezone\TimezoneQueryBuilder::class;
    }
}