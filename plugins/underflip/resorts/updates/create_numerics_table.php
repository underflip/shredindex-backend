<?php

namespace Underflip\Resorts\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;
use Underflip\Resorts\Models\Numeric;

class CreateNumericsTable extends Migration
{
    public function up()
    {
        Schema::create(app(Numeric::class)->getTable(), function ($table) {
            $table->increments('id');
            $table->decimal('value');
            $table->integer('resort_id')->nullable();
            $table->integer('type_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(app(Numeric::class)->getTable());
    }
}
