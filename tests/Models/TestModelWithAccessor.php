<?php

namespace Brainlet\LaravelConvertTimezone\Tests\Models;

use Brainlet\LaravelConvertTimezone\Traits\ConvertTZ;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TestModelWithAccessor extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'models';

    use HasFactory, ConvertTZ;

    protected $guarded = [];

    public function getCreatedAtAttribute($value)
    {
        return $value;
    }
}
