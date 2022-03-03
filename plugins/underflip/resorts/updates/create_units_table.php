<?php namespace Underflip\Resorts\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Underflip\Resorts\Models\Unit;

class CreateUnitsTable extends Migration
{
    public function up()
    {
        Schema::create(app(Unit::class)->getTable(), function ($table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('title');
            $table->string('singular_title');
            $table->string('plural_title');
            $table->string('format')->nullable();
            $table->string('plural_format')->nullable();
        });
    }

    /**
     * @codeCoverageIgnore
     */
    public function down()
    {
        Schema::dropIfExists(app(Unit::class)->getTable());
    }
}
