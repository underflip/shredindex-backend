<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateUnderflipResortStatistics extends Migration
{
    public function up()
    {
        Schema::table('underflip_resort_statistics', function($table)
        {
            $table->integer('statistics_name_id');
            $table->string('value');
            $table->integer('type')->nullable();
            $table->dropColumn('average_annual_snowfall');
            $table->dropColumn('total_terrain');
            $table->dropColumn('elevation');
            $table->dropColumn('vertical_drop');
            $table->dropColumn('number_of_runs');
            $table->dropColumn('terrain_expert');
            $table->dropColumn('terrain_intermediate');
            $table->dropColumn('terrain_beginner');
            $table->dropColumn('backcountry_access');
            $table->dropColumn('park');
            $table->dropColumn('snow_making');
            $table->dropColumn('has_terrain_park');
            $table->dropColumn('has_night_skiing');
            $table->dropColumn('has_cross_country');
            $table->dropColumn('has_helicopter_skiing');
        });
    }
    
    public function down()
    {
        Schema::table('underflip_resort_statistics', function($table)
        {
            $table->dropColumn('statistics_name_id');
            $table->dropColumn('value');
            $table->dropColumn('type');
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
            $table->string('has_terrain_park', 191);
            $table->string('has_night_skiing', 191);
            $table->string('has_cross_country', 191);
            $table->string('has_helicopter_skiing', 191);
        });
    }
}
