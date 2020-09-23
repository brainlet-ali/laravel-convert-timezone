<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMyModelsTable extends Migration
{
    public function up()
    {
        Schema::create('my_models', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->timestamps();
        });
    }
}
