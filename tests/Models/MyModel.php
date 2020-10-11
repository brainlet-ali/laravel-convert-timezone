<?php


namespace Brainlet\LaravelConvertTimezone\Tests\Models;

use Brainlet\LaravelConvertTimezone\Traits\ConvertTZ;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MyModel extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory, ConvertTZ;

    protected $guarded = [];
}
