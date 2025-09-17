<?php

namespace Brainlet\LaravelConvertTimezone\Traits;

use Brainlet\LaravelConvertTimezone\Actions\ConvertTimezoneAction;
use Carbon\Carbon;

/**
 * Trait ConvertTZ - Automatically converts datetime attributes to configured timezone
 */
trait ConvertTZ
{
    use TimezoneScopes;

    /**
     * Cached datetime attributes for this model instance.
     *
     * @var array<string>
     */
    private array $dateTimeAttributes = [];

    /**
     * Override the parent's boot method so that we can register our event listener.
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
            if ($this->shouldConvertAttribute($key, $value)) {
                $this->attributes[$key] = $this->convertToTimezone($value);
            }
        }
    }

    /**
     * Check if an attribute should be converted.
     */
    protected function shouldConvertAttribute(string $key, $value): bool
    {
        // Skip if it has custom accessor
        if ($this->hasGetMutator($key)) {
            return false;
        }

        // Skip null values
        if ($value === null) {
            return false;
        }

        // Convert if already Carbon (definitely a datetime)
        if ($value instanceof Carbon) {
            return true;
        }

        // Convert if Laravel knows it's a date field
        return in_array($key, $this->dateTimeAttributes);
    }

    /**
     * Convert a value to the configured timezone.
     */
    protected function convertToTimezone($value): ?Carbon
    {
        return (new ConvertTimezoneAction)->execute($value, $this->getTz());
    }

    /**
     * Get all datetime attributes of the model.
     * Uses Laravel's built-in date detection - no database queries!
     *
     * @return array<string>
     */
    protected function getDateTimeAttributes(): array
    {
        // Use cached value if available
        if (!empty($this->dateTimeAttributes)) {
            return $this->dateTimeAttributes;
        }

        // Get dates from model configuration
        $dates = $this->getDates();

        // Add any fields cast as date/datetime
        foreach ($this->getCasts() as $key => $type) {
            if (str_contains($type, 'date') || str_contains($type, 'time')) {
                $dates[] = $key;
            }
        }

        return array_unique($dates);
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

        // Use cached datetime attributes
        if (empty($this->dateTimeAttributes)) {
            $this->dateTimeAttributes = $this->getDateTimeAttributes();
        }

        foreach ($this->dateTimeAttributes as $key) {
            if (isset($attributes[$key]) && $attributes[$key] !== null && !$this->hasGetMutator($key)) {
                $attributes[$key] = $this->convertToTimezone($attributes[$key])->toIso8601String();
            }
        }

        return $attributes;
    }
}
