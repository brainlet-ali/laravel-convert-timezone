<?php

namespace Brainlet\LaravelConvertTimezone\Traits;

use Brainlet\LaravelConvertTimezone\Actions\ConvertTimezoneAction;
use Brainlet\LaravelConvertTimezone\Actions\GetDateTimeAttributesAction;
use Brainlet\LaravelConvertTimezone\Exceptions\InvalidTimezone;
use Carbon\Carbon;
use Exception;

/**
 * Trait ConvertTZ - Automatically converts datetime attributes to configured timezone
 *
 * @property array $dateTimeAttributes The attributes that should be converted to the configured timezone
 */
trait ConvertTZ
{
    use TimezoneScopes;

    /**
     * The attributes of a model that should be converted to the configured timezone.
     *
     * @var array<string>
     */
    private array $dateTimeAttributes = [];

    /**
     * Override the parent's boot method so that we can register our event listener.
     *
     * @throws Exception
     */
    public static function bootConvertTZ(): void
    {
        static::retrieved(function (self $model) {
            $model->convertDatetimeAttributes();
        });
    }

    /**
     * Convert all datetime attributes to the configured timezone.
     */
    protected function convertDatetimeAttributes(): void
    {
        $this->dateTimeAttributes = $this->getDateTimeAttributes();

        foreach ($this->attributes as $key => $value) {
            if ($this->shouldConvertAttribute($key)) {
                $this->attributes[$key] = $this->convertToTimezone($value);
            }
        }
    }

    /**
     * Check if an attribute should be converted.
     */
    protected function shouldConvertAttribute(string $key): bool
    {
        return $this->isTzAttribute($key) && ! $this->hasGetMutator($key);
    }

    /**
     * Convert a value to the configured timezone.
     *
     * @throws InvalidTimezone
     */
    protected function convertToTimezone($value): Carbon
    {
        return (new ConvertTimezoneAction)->execute($value, $this->getTz());
    }

    /**
     * Check if a key is a datetime attribute.
     */
    protected function isTzAttribute(string $key): bool
    {
        return in_array($key, $this->dateTimeAttributes);
    }

    /**
     * Get all datetime attributes of the model.
     *
     * @return array<string> List of datetime column names
     */
    protected function getDateTimeAttributes(): array
    {
        return (new GetDateTimeAttributesAction)->execute($this);
    }

    /**
     * Get the timezone from the config file.
     */
    protected function getTz(): string
    {
        return config('tz.timezone');
    }

    /**
     * Convert model to array with timezone converted datetime fields.
     */
    public function toArray(): array
    {
        $attributes = parent::toArray();

        foreach ($this->getDateTimeAttributes() as $key) {
            if ($this->shouldConvertAttribute($key)) {
                $attributes[$key] = $this->convertToTimezone($attributes[$key])->toIso8601String();
            }
        }

        return $attributes;
    }
}
