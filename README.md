# 

[![Latest Version on Packagist](https://img.shields.io/packagist/v/brainlet-ali/laravel-convert-timezone.svg?style=flat-square)](https://packagist.org/packages/brainlet-ali/laravel-convert-timezone)


A minimal package to convert any model's datetime fields from UTC to desired timezone.


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
Copy vendor/brainlet-ali/config/tz.php into your config directory

## Usage

``` php
use Brainlet\LaravelConvertTimezone\Traits\ConvertTZ;


class MyModel extends Model
{

    use ..., ConvertTZ;

    ...
}

$myModel = MyModel::first();
$myModel->created_at; // (outputs converted to timezone as defined in config)
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Security Vulnerabilities

If you found any security vulnerabilities please contact me at: ali@brainlet.co


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
