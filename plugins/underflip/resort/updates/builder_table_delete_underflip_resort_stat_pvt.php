<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableDeleteUnderflipResortStatPvt extends Migration
{
    public function up()
    {
        Schema::dropIfExists('underflip_resort_stat_pvt');
    }
    
    public function down()
    {
        Schema::create('underflip_resort_stat_pvt', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('resorts_id');
            $table->integer('statistics_id');
            $table->primary(['resorts_id','statistics_id']);
        });
    }
}
