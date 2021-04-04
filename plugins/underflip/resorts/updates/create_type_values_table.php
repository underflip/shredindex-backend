<?php namespace Underflip\Resorts\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Underflip\Resorts\Models\TypeValue;

class CreateTypeValuesTable extends Migration
{
    public function up()
    {
        Schema::create(app(TypeValue::class)->getTable(), function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('title');
        });
    }

    public function down()
    {
        Schema::dropIfExists(app(TypeValue::class)->getTable());
    }
}
