<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateUnderflipResortResorts extends Migration
{
    public function up()
    {
        Schema::table('underflip_resort_resorts', function($table)
        {
            $table->string('title');
            $table->string('slug');
            $table->string('hemisphere');
            $table->string('continent');
            $table->integer('country_id');
            $table->integer('state_id');
            $table->string('mountainrange');
            $table->string('town');
            $table->string('weathermapcity');
            $table->string('geo_location_coordinates');
            $table->boolean('is_enabled');
            $table->integer('resort_type');
        });
    }
    
    public function down()
    {
        Schema::table('underflip_resort_resorts', function($table)
        {
            $table->dropColumn('title');
            $table->dropColumn('slug');
            $table->dropColumn('hemisphere');
            $table->dropColumn('continent');
            $table->dropColumn('country_id');
            $table->dropColumn('state_id');
            $table->dropColumn('mountainrange');
            $table->dropColumn('town');
            $table->dropColumn('weathermapcity');
            $table->dropColumn('geo_location_coordinates');
            $table->dropColumn('is_enabled');
            $table->dropColumn('resort_type');
        });
    }
}
