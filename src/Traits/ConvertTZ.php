<?php

namespace Brainlet\LaravelConvertTimezone\Traits;

use Brainlet\LaravelConvertTimezone\Exceptions\InvalidTimezone;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Exception;

trait ConvertTZ
{
    /**
     * The attributes of a model that should be converted to the configured timezone.
     */
    private array $dateTimeAttributes = [];

    /**
     * Override the parent's boot method so that we can register our event listener.
     * @throws \Exception
     */
    public static function bootConvertTZ()
    {
        static::retrieved(function ($model) {
            $model->dateTimeAttributes = $model->getDateTimeAttributes();
            foreach ($model->attributes as $key => $value) {
                if ($model->isTzAttribute($key) && !$model->hasGetMutator($key)) {
                    $model->attributes[$key] = $model->mutateAttribute($key, $value);
                }
            }
        });
    }

    /**
     * Mutate the given attribute to a Carbon instance w.r.t. the configured timezone.
     * @throws \Exception
     */
    protected function mutateAttribute($key, $value): Carbon
    {
        $tz = $this->getTz();
        try {
            return (new Carbon($value))->setTimezone(new CarbonTimeZone($tz));
        } catch (Exception $e) {
            throw InvalidTimezone::make($tz);
        }
    }

    /**
     * Determine if the given attribute is a datetime attribute.
     */
    protected function isTzAttribute($key): bool
    {
        return in_array($key, $this->dateTimeAttributes);
    }

    /**
     * Get all datetime attributes of the model.
     */
    protected function getDateTimeAttributes(): array
    {
        $table = $this->getTable();
        $builder = $this->getConnection()->getSchemaBuilder();
        $columns = $builder->getColumnListing($table);

        $datetimeKeys = [];
        foreach ($columns as $column) {
            $type = $builder->getColumnType($table, $column);

            if ($type === 'datetime') {
                $datetimeKeys[] = $column;
            }
        }

        return $datetimeKeys;
    }

    /**
     * Get the timezone from the config file.
     */
    protected function getTz(): string
    {
        return config('tz.timezone');
    }

    /**
     * @throws \Exception
     */
    public function toArray(): array
    {
        $attributes = parent::toArray();
        foreach ($this->getDateTimeAttributes() as $key) {
            if ($this->isTzAttribute($key) && !$this->hasGetMutator($key)) {
                $attributes[$key] = $this->mutateAttribute($key, $attributes[$key])->toIso8601String();
            }
        }
        return $attributes;
    }
}
