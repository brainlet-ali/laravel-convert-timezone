# Laravel Convert Timezone Documentation

## Table of Contents
- [Installation](#installation)
- [Configuration](#configuration)
- [Basic Usage](#basic-usage)
- [Query Builder Timezone Scopes](#query-builder-timezone-scopes)
- [Examples](#examples)
- [Database Support](#database-support)

## Installation

Install the package via Composer:

```bash
composer require brainlet-ali/laravel-convert-timezone
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Brainlet\LaravelConvertTimezone\LaravelConvertTimezoneServiceProvider" --tag="tz-config"
```

## Configuration

After publishing, configure your timezone in `config/tz.php`:

```php
return [
    'timezone' => 'America/New_York', // Your desired timezone
];
```

You can also set the timezone dynamically using environment variables:

```php
return [
    'timezone' => env('APP_DISPLAY_TIMEZONE', 'America/New_York'),
];
```

## Basic Usage

### Automatic Timezone Conversion

Add the `ConvertTZ` trait to any Eloquent model to automatically convert datetime fields from UTC to your configured timezone:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Brainlet\LaravelConvertTimezone\Traits\ConvertTZ;

class Post extends Model
{
    use ConvertTZ;
}
```

Now when you retrieve the model, all datetime fields will be automatically converted:

```php
$post = Post::first();
echo $post->created_at; // Displays in configured timezone (e.g., America/New_York)
```

The trait automatically detects all datetime columns in your database table and converts them on retrieval.

## Query Builder Timezone Scopes

**New in v2.0:** The package now includes powerful query builder scopes that allow you to filter your database queries with timezone awareness. These scopes handle the complexity of timezone conversion at the database level for optimal performance.

### How It Works

When you use these scopes, the package:
1. Takes your input datetime/date/time in the specified timezone
2. Converts it to UTC (since your database stores dates in UTC)
3. Applies the filter at the database level using SQL functions
4. Returns results that match your criteria in the specified timezone

This means you can think in your local timezone while the database operations happen in UTC seamlessly.

### Available Scopes

### whereInTimezone

Filter records by datetime in a specific timezone:

```php
// Get posts created after 9 AM New York time
Post::whereInTimezone('created_at', '>=', '2024-01-01 09:00:00', 'America/New_York')->get();

// Uses default timezone from config if not specified
Post::whereInTimezone('created_at', '>=', '2024-01-01 09:00:00')->get();
```

### whereDateInTimezone

Filter records by date only (ignoring time) in a specific timezone:

```php
// Get all posts created on January 1st, 2024 in New York timezone
Post::whereDateInTimezone('created_at', '2024-01-01', 'America/New_York')->get();

// This handles UTC/timezone boundaries correctly
// A post created at 2024-01-01 04:00 UTC would be 2023-12-31 23:00 in NY
```

### whereTimeInTimezone

Filter records by time only (ignoring date) in a specific timezone:

```php
// Get posts created between 9 AM and 5 PM in New York time (any day)
Post::whereTimeInTimezone('created_at', '>=', '09:00:00', 'America/New_York')
    ->whereTimeInTimezone('created_at', '<=', '17:00:00', 'America/New_York')
    ->get();
```

### whereBetweenInTimezone

Filter records between two dates in a specific timezone:

```php
// Get posts created between Jan 1 and Jan 31 in Tokyo timezone
Post::whereBetweenInTimezone('created_at', ['2024-01-01', '2024-01-31'], 'Asia/Tokyo')->get();
```

### whereMonthInTimezone

Filter records by month in a specific timezone:

```php
// Get all posts created in January (month = 1) in London timezone
Post::whereMonthInTimezone('created_at', 1, 'Europe/London')->get();

// Get December posts
Post::whereMonthInTimezone('created_at', 12, 'America/New_York')->get();
```

### whereYearInTimezone

Filter records by year in a specific timezone:

```php
// Get all posts created in 2023 in Sydney timezone
Post::whereYearInTimezone('created_at', 2023, 'Australia/Sydney')->get();
```

## Examples

### Example 1: User-Specific Timezones

```php
class UserPost extends Model
{
    use ConvertTZ;
    
    public function scopeForUserDate($query, $date, $user)
    {
        return $query->whereDateInTimezone('created_at', $date, $user->timezone);
    }
}

// Get posts for user's "today"
$user = Auth::user(); // Assume user has timezone = 'Asia/Tokyo'
$todayPosts = UserPost::forUserDate(now()->toDateString(), $user)->get();
```

### Example 2: Business Hours Filtering

```php
// Get orders placed during business hours (9 AM - 5 PM) in LA
$businessOrders = Order::whereTimeInTimezone('created_at', '>=', '09:00:00', 'America/Los_Angeles')
    ->whereTimeInTimezone('created_at', '<=', '17:00:00', 'America/Los_Angeles')
    ->get();
```

### Example 3: Monthly Reports

```php
// Generate report for January 2024 in company timezone
$januaryData = Sale::whereYearInTimezone('completed_at', 2024, 'America/New_York')
    ->whereMonthInTimezone('completed_at', 1, 'America/New_York')
    ->sum('amount');
```

### Example 4: Date Range Queries

```php
// Get events happening this week in user's timezone
$weekStart = now()->startOfWeek()->toDateString();
$weekEnd = now()->endOfWeek()->toDateString();

$events = Event::whereBetweenInTimezone(
    'start_time', 
    [$weekStart, $weekEnd], 
    $user->timezone
)->get();
```

## Database Support

The package supports multiple database systems with automatic detection:

### MySQL / MariaDB
- Uses native `CONVERT_TZ()` function
- Full support for all timezone scopes
- Requires timezone tables to be loaded in MySQL

### PostgreSQL
- Uses timezone conversion functions
- Full support for all timezone scopes

### SQLite
- Uses `datetime()` and `strftime()` functions
- Handles timezone offsets in seconds
- Full support including DST (Daylight Saving Time) handling
- Commonly used in testing environments

## Important Notes

1. **Storage**: Dates are always stored in UTC in the database. Conversion happens on retrieval and in queries.

2. **Model Attributes**: The `ConvertTZ` trait only affects datetime attributes when reading from the database. When saving, ensure your dates are in UTC.

3. **Carbon Integration**: All converted datetime attributes are returned as Carbon instances with the correct timezone set.

4. **Performance**: The timezone scopes use database-level functions for optimal performance rather than PHP-level filtering.

5. **Timezone Validation**: Invalid timezones will throw an `InvalidArgumentException`.

## Limitations

- Only works with Eloquent models
- Requires datetime columns to be properly defined in the database
- MySQL requires timezone tables to be populated for `CONVERT_TZ()` to work

## Troubleshooting

### MySQL CONVERT_TZ Returns NULL

If `CONVERT_TZ()` returns NULL in MySQL, load the timezone tables:

```bash
mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root mysql
```

### Timezone Not Changing

1. Check that the configuration file is published
2. Verify the timezone setting in `config/tz.php`
3. Clear config cache: `php artisan config:clear`

### Invalid Timezone Error

Ensure you're using valid timezone identifiers from [PHP's timezone list](https://www.php.net/manual/en/timezones.php).