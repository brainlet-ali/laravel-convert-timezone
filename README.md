# Laravel Convert Timezone

[![Latest Version on Packagist](https://img.shields.io/packagist/v/brainlet-ali/laravel-convert-timezone.svg?style=flat-square)](https://packagist.org/packages/brainlet-ali/laravel-convert-timezone)

Seamlessly handle timezone conversions in your Laravel applications. This lightweight package automatically converts datetime fields to your users' local timezones without modifying your database, keeping everything in UTC while displaying dates and times in the correct timezone for each user.

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

## Why Use This Package?

Working with timezones in web applications is challenging. Store times in UTC? Display in user's timezone? Filter by local dates? This package solves these problems elegantly:

- ✅ **Zero Database Changes** - Keep storing everything in UTC
- ✅ **Automatic Conversion** - DateTime fields automatically display in the configured timezone
- ✅ **Smart Filtering** - Query by dates/times in any timezone without complex calculations
- ✅ **Laravel Native** - Works seamlessly with Eloquent models and query builder
- ✅ **Minimal Setup** - Just add a trait to your model and you're ready to go

## Quick Start

```php
use Brainlet\LaravelConvertTimezone\Traits\ConvertTZ;

class Post extends Model
{
    use ConvertTZ;
}

// That's it! All datetime fields now automatically convert to your configured timezone
$post = Post::first();
echo $post->created_at; // 2024-01-15 09:30:00 (in America/New_York instead of UTC)
```

## Basic Usage

### Configuration

Set your default timezone in the config file or `.env`:

```php
// config/tz.php
'timezone' => env('TIMEZONE', 'America/New_York'),
```

### Automatic Conversion

Once you add the `ConvertTZ` trait, all datetime fields (`created_at`, `updated_at`, etc.) are automatically converted:

```php
$user = User::create(['email' => 'user@example.com']);
echo $user->created_at; // Displays in your configured timezone, not UTC
```

### Accessor Compatibility

The trait respects existing accessors - if you have a custom accessor for a datetime field, it won't interfere:

```php
public function getCreatedAtAttribute($value)
{
    // Your custom logic here
    return $value; // This will take precedence over timezone conversion
}
```

## Documentation

For more detailed usage examples and advanced features, see the [full documentation](DOC.md).

## Requirements

- PHP 7.4+ or 8.0+
- Laravel 9.0+ | 10.0+ | 11.0+
- Doctrine DBAL 3.8+

## Testing

```bash
composer test
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

### Reporting Issues

When [creating an issue](https://github.com/brainlet-ali/laravel-convert-timezone/issues/new), please provide:
- Clear description of the problem
- Steps to reproduce the issue
- Expected vs actual behavior
- Laravel version and PHP version
- Any relevant error messages or stack traces

### Security Issues

If you discover any security vulnerabilities, please [create an issue](https://github.com/brainlet-ali/laravel-convert-timezone/issues/new) with:
- **Title**: [SECURITY] Brief description
- **Description**: Detailed explanation of the vulnerability
- **Steps to reproduce**: How to replicate the issue
- **Impact**: Potential security implications
- **Suggested fix**: If you have a solution in mind

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
