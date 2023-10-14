<?php

namespace Brainlet\LaravelConvertTimezone\Tests\Models;

use Brainlet\LaravelConvertTimezone\Traits\ConvertTZ;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestModelWithAccessor extends Model
{
    protected $table = 'models';

    use HasFactory, ConvertTZ;

    protected $guarded = [];

    public function getCreatedAtAttribute($value)
    {
        return $value;
    }
}
