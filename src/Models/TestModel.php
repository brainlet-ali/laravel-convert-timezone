<?php

namespace Brainlet\LaravelConvertTimezone\Models;

use Brainlet\LaravelConvertTimezone\Traits\ConvertTZ;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    protected $table = 'models';

    use HasFactory, ConvertTZ;

    protected $guarded = [];
}
