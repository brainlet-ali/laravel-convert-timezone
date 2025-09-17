<?php

namespace Brainlet\LaravelConvertTimezone\Actions;

use Illuminate\Database\Eloquent\Model;

class GetDateTimeAttributesAction
{
    /**
     * Get all datetime columns from a model's table.
     */
    public function execute(Model $model): array
    {
        $table = $model->getTable();
        $schema = $model->getConnection()->getSchemaBuilder();
        $columns = $schema->getColumnListing($table);

        $datetimeColumns = [];
        
        foreach ($columns as $column) {
            if ($this->isDatetimeColumn($schema, $table, $column)) {
                $datetimeColumns[] = $column;
            }
        }

        return $datetimeColumns;
    }
    
    /**
     * Check if a column is a datetime type.
     */
    private function isDatetimeColumn($schema, string $table, string $column): bool
    {
        return $schema->getColumnType($table, $column) === 'datetime';
    }
}