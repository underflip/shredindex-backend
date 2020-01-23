<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateUnderflipResortStatPvt extends Migration
{
    public function up()
    {
        Schema::create('underflip_resort_stat_pvt', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('resorts_id');
            $table->integer('statistics_id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('underflip_resort_stat_pvt');
    }
}
