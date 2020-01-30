<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateUnderflipResortResort4 extends Migration
{
    public function up()
    {
        Schema::table('underflip_resort_resort', function($table)
        {
            $table->increments('id')->change();
        });
    }
    
    public function down()
    {
        Schema::table('underflip_resort_resort', function($table)
        {
            $table->integer('id')->change();
        });
    }
}
