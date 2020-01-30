<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateUnderflipResortStatistic3 extends Migration
{
    public function up()
    {
        Schema::table('underflip_resort_statistic', function($table)
        {
            $table->dropColumn('type');
        });
    }
    
    public function down()
    {
        Schema::table('underflip_resort_statistic', function($table)
        {
            $table->integer('type')->nullable();
        });
    }
}
