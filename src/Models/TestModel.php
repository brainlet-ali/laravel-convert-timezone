<?php

namespace Brainlet\LaravelConvertTimezone\Models;

use Brainlet\LaravelConvertTimezone\Traits\ConvertTZ;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TestModel extends \Illuminate\Database\Eloquent\Model
{
    protected $table = 'models';

    use HasFactory, ConvertTZ;

    protected $guarded = [];
}
