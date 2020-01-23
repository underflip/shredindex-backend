<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateUnderflipResortStatisticName extends Migration
{
    public function up()
    {
        Schema::table('underflip_resort_statistic_name', function($table)
        {
            $table->string('value');
        });
    }
    
    public function down()
    {
        Schema::table('underflip_resort_statistic_name', function($table)
        {
            $table->dropColumn('value');
        });
    }
}
