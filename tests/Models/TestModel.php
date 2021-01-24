<?php


namespace Brainlet\LaravelConvertTimezone\Tests\Models;

use Brainlet\LaravelConvertTimezone\Traits\ConvertTZ;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TestModel extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory, ConvertTZ;

    protected $guarded = [];
}
