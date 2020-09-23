# 

[![Latest Version on Packagist](https://img.shields.io/packagist/v/brainlet-ali/laravel-convert-timezone.svg?style=flat-square)](https://packagist.org/packages/brainlet-ali/laravel-convert-timezone)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/brainlet-ali/laravel-convert-timezone/run-tests?label=tests)](https://github.com/brainlet-ali/laravel-convert-timezone/actions?query=workflow%3Arun-tests+branch%3Amaster)
[![Total Downloads](https://img.shields.io/packagist/dt/brainlet-ali/laravel-convert-timezone.svg?style=flat-square)](https://packagist.org/packages/brainlet-ali/laravel-convert-timezone)


A minimal package to convert any model's datetime fields from UTC to desired timezone.


## Installation

You can install the package via composer:

```bash
composer require brainlet-ali/laravel-convert-timezone
```

You can publish the config file with:
```bash
php artisan vendor:publish --provider="Brainlet\LaravelConvertTimezone\LaravelConvertTimezoneServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
return [
    // 'timezone' => ('UTC' !== auth()->user()->tz)
    // ?: env('TIMEZONE', 'UTC'),

    'timezone' => env('TIMEZONE', 'UTC'),
];
```

## Usage

``` php
use Brainlet\LaravelConvertTimezone\Traits\ConvertTZ;


class MyModel extends Model
{

    use ..., ConvertTZ;

    ...
}

$myModel->created_at; // (outputs converted to timezone as defined in config)

```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

If you found any security vulnerabilities please contact me at: ali@brainlet.co


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
