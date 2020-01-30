<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateUnderflipResortStatistics extends Migration
{
    public function up()
    {
        Schema::create('underflip_resort_statistics', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('resorts_id');
            $table->integer('average_annual_snowfall');
            $table->integer('total_terrain');
            $table->integer('elevation');
            $table->integer('vertical_drop');
            $table->integer('number_of_runs');
            $table->integer('terrain_expert');
            $table->integer('terrain_intermediate');
            $table->integer('terrain_beginner');
            $table->integer('backcountry_access');
            $table->integer('park');
            $table->integer('snow_making');
            $table->string('has_terrain_park');
            $table->string('has_night_skiing');
            $table->string('has_cross_country');
            $table->string('has_helicopter_skiing');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('underflip_resort_statistics');
    }
}
