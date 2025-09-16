# Laravel Convert Timezone

[![Latest Version on Packagist](https://img.shields.io/packagist/v/brainlet-ali/laravel-convert-timezone.svg?style=flat-square)](https://packagist.org/packages/brainlet-ali/laravel-convert-timezone)

A powerful Laravel package for automatic timezone conversion of Eloquent model datetime fields and timezone-aware query filtering.

## Installation

### Laravel
You can install the package via composer:
```bash
composer require brainlet-ali/laravel-convert-timezone
```
You can publish the config file with:
```bash
php artisan vendor:publish --provider="Brainlet\LaravelConvertTimezone\LaravelConvertTimezoneServiceProvider" --tag="tz-config"
```

## Features

- 🌍 **Automatic timezone conversion** for all datetime fields
- 🔍 **Timezone-aware query scopes** for filtering data
- 📅 **Date, time, and datetime filtering** with timezone support
- 🗄️ **Multi-database support** (MySQL, PostgreSQL, SQLite)
- ⚡ **Database-level performance** optimization
- 🎯 **Simple, intuitive API** that feels natural

## Quick Start

Add the `ConvertTZ` trait to your model:

```php
use Brainlet\LaravelConvertTimezone\Traits\ConvertTZ;

class Post extends Model
{
    use ConvertTZ;
}

// Automatically converts datetime fields to your configured timezone
$post = Post::first();
echo $post->created_at; // Shows in America/New_York instead of UTC

// Filter posts created on a specific date in user's timezone
$posts = Post::whereDateInTimezone('created_at', '2024-01-01', 'America/New_York')->get();
```

## Documentation

For detailed usage instructions, examples, and API reference, please see the [full documentation](DOC.md).

## Limitations

- Only works with Eloquent models

## Security Vulnerabilities

If you found any security vulnerabilities please contact me at: ali@brainlet.co

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
