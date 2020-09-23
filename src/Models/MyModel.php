<?php


namespace Brainlet\LaravelConvertTimezone\Models;

use Brainlet\LaravelConvertTimezone\Traits\ConvertTZ;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MyModel extends \Illuminate\Database\Eloquent\Model
{

    use HasFactory, ConvertTZ;

    protected $guarded = [];
}
