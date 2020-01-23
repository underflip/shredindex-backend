<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateUnderflipResortStatistic2 extends Migration
{
    public function up()
    {
        Schema::table('underflip_resort_statistic', function($table)
        {
            $table->integer('resort_id');
            $table->integer('statistic_name_id');
            $table->dropColumn('resorts_id');
            $table->dropColumn('statistics_name_id');
        });
    }
    
    public function down()
    {
        Schema::table('underflip_resort_statistic', function($table)
        {
            $table->dropColumn('resort_id');
            $table->dropColumn('statistic_name_id');
            $table->integer('resorts_id');
            $table->integer('statistics_name_id');
        });
    }
}
