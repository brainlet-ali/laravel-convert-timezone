<?php

namespace Brainlet\LaravelConvertTimezone\Tests\Models;

use Brainlet\LaravelConvertTimezone\Traits\ConvertTZ;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use ConvertTZ;

    protected $fillable = ['title', 'body', 'created_at', 'updated_at'];

    public $timestamps = true;
}