<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateUnderflipResortResort3 extends Migration
{
    public function up()
    {
        Schema::table('underflip_resort_resort', function($table)
        {
            $table->primary(['id']);
        });
    }
    
    public function down()
    {
        Schema::table('underflip_resort_resort', function($table)
        {
            $table->dropPrimary(['id']);
        });
    }
}
