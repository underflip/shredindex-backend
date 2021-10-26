<?php namespace Underflip\Resorts\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;
use Underflip\Resorts\Models\TotalScore;

class CreateTotalScoresTable extends Migration
{
    public function up()
    {
        Schema::create(app(TotalScore::class)->getTable(), function ($table) {
            $table->increments('id');
            $table->decimal('value', 4, 1);
            $table->integer('resort_id')->nullable();
            $table->integer('type_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(app(TotalScore::class)->getTable());
    }
}
