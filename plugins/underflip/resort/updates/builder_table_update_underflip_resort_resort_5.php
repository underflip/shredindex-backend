<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateUnderflipResortResort5 extends Migration
{
    public function up()
    {
        Schema::table('underflip_resort_resort', function($table)
        {
            $table->string('hemisphere', 191)->nullable()->change();
            $table->string('continent', 191)->nullable()->change();
            $table->integer('country_id')->nullable()->change();
            $table->integer('state_id')->nullable()->change();
            $table->string('mountainrange', 191)->nullable()->change();
            $table->string('town', 191)->nullable()->change();
            $table->string('weathermapcity', 191)->nullable()->change();
            $table->string('geo_location_coordinates', 191)->nullable()->change();
            $table->boolean('is_enabled')->nullable()->change();
            $table->integer('resort_type')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('underflip_resort_resort', function($table)
        {
            $table->string('hemisphere', 191)->nullable(false)->change();
            $table->string('continent', 191)->nullable(false)->change();
            $table->integer('country_id')->nullable(false)->change();
            $table->integer('state_id')->nullable(false)->change();
            $table->string('mountainrange', 191)->nullable(false)->change();
            $table->string('town', 191)->nullable(false)->change();
            $table->string('weathermapcity', 191)->nullable(false)->change();
            $table->string('geo_location_coordinates', 191)->nullable(false)->change();
            $table->boolean('is_enabled')->nullable(false)->change();
            $table->integer('resort_type')->nullable(false)->change();
        });
    }
}
