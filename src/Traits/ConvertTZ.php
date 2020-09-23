<?php


namespace Brainlet\LaravelConvertTimezone\Traits;

use Carbon\Carbon;
use Carbon\CarbonTimeZone;
use Illuminate\Support\Str;

trait ConvertTZ
{
    public function hasGetMutator($key)
    {
        return parent::hasGetMutator($key) || $this->isTZAttribute($key);
    }

    protected function mutateAttribute($key, $value)
    {
        if (parent::hasGetMutator($key)) {
            return parent::mutateAttribute($key, $value);
        }

        try {
            return (new Carbon($value))
                ->setTimezone(new CarbonTimeZone($this->getTZ()));
        } catch (\Exception $e) {
            return 'Invalid DateTime Exception: '.$e->getMessage();
        }
    }

    protected function isTZAttribute($key)
    {
        return in_array(
            $this->generateAccessorName($key),
            $this->getDateTimeAttributes()
        );
    }

    protected function getDateTimeAttributes()
    {
        $table = $this->getTable();
        $builder = $this->getConnection()->getSchemaBuilder();
        $columns = $builder->getColumnListing($this->getTable());
        $columnsWithType
                 = collect($columns)
            ->mapWithKeys(function ($item, $key) use ($builder, $table) {
                $type = $builder->getColumnType($table, $item);

                return [$item => $type];
            })->toArray();

        return array_map(
            fn (
                $key
            ) => $this->generateAccessorName($key),
            array_keys(
                array_filter(
                    $columnsWithType,
                    fn ($type) => $type === 'datetime'
                )
            )
        );
    }

    protected function generateAccessorName($key)
    {
        return 'get'.Str::studly($key).'Attribute';
    }

    /**
     * @return \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */
    protected function getTZ()
    {
        return config('tz.timezone');
    }
}
