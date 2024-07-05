<?php namespace Underflip\Resorts\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;
use Underflip\Resorts\Models\Rating;

class CreateRatingsTable extends Migration
{
    public function up()
    {
        Schema::create(app(Rating::class)->getTable(), function ($table) {
            $table->increments('id');
            $table->smallInteger('value');
            $table->integer('resort_id')->nullable();
            $table->integer('type_id')->nullable();
        });
    }

    /**
     * @codeCoverageIgnore
     */
    public function down()
    {
        Schema::dropIfExists(app(Rating::class)->getTable());
    }
}
