<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateUnderflipResortStatPvt extends Migration
{
    public function up()
    {
        Schema::table('underflip_resort_stat_pvt', function($table)
        {
            $table->primary(['resorts_id','statistics_id']);
        });
    }
    
    public function down()
    {
        Schema::table('underflip_resort_stat_pvt', function($table)
        {
            $table->dropPrimary(['resorts_id','statistics_id']);
        });
    }
}
