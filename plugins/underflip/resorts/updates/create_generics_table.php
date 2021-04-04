<?php

namespace Underflip\Resorts\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;
use Underflip\Resorts\Models\Generic;

class CreateGenericsTable extends Migration
{
    public function up()
    {
        Schema::create(app(Generic::class)->getTable(), function ($table) {
            $table->increments('id');
            $table->string('value');
            $table->integer('resort_id')->nullable();
            $table->integer('type_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(app(Generic::class)->getTable());
    }
}
