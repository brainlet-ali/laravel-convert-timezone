<?php

namespace Brainlet\LaravelConvertTimezone\Traits;

use Brainlet\LaravelConvertTimezone\Exceptions\InvalidTimezone;
use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Exception;
use Illuminate\Support\Str;

trait ConvertTZ
{
    /**
     * The attributes of a model that should be converted to the configured timezone.
     */
    private array $dateTimeAttributes = [];

    public function __construct()
    {
        parent::__construct();

        $this->dateTimeAttributes = $this->getDateTimeAttributes();
    }

    /**
     * Override the parent getAttribute method so that we can allow for
     * mutation if the class itself don't have a get mutator for the attribute,
     * or it qualifies as a datetime attribute.
     */
    public function hasGetMutator($key): bool
    {
        return parent::hasGetMutator($key) || $this->isTzAttribute($key);
    }

    /**
     * Do the actual mutation if there is no mutator defined for the attribute or
     * the attribute qualifies as a datetime attribute.
     * @throws \Exception
     */
    protected function mutateAttribute($key, $value)
    {
        // if the class itself has a get mutator for the attribute don't mutate it
        if (parent::hasGetMutator($key)) {
            return parent::mutateAttribute($key, $value);
        }

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
        return in_array($this->generateAccessorName($key), $this->dateTimeAttributes);
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
                $datetimeKeys[] = $this->generateAccessorName($column);
            }
        }

        return $datetimeKeys;
    }

    /**
     * Generate the accessor name for the given key.
     */
    protected function generateAccessorName($key): string
    {
        return 'get'.Str::studly($key).'Attribute';
    }

    /**
     * Get the timezone from the config file.
     */
    protected function getTz(): string
    {
        return config('tz.timezone');
    }
}
