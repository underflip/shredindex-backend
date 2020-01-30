<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateUnderflipResortStatisticName extends Migration
{
    public function up()
    {
        Schema::create('underflip_resort_statistic_name', function($table)
        {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('underflip_resort_statistic_name');
    }
}
