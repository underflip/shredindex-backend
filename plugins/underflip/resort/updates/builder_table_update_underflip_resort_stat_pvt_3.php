<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateUnderflipResortStatPvt3 extends Migration
{
    public function up()
    {
        Schema::table('underflip_resort_stat_pvt', function($table)
        {
            $table->dropPrimary(['resort_id','statistics_id']);
            $table->renameColumn('resort_id', 'resorts_id');
            $table->primary(['resorts_id','statistics_id']);
        });
    }
    
    public function down()
    {
        Schema::table('underflip_resort_stat_pvt', function($table)
        {
            $table->dropPrimary(['resorts_id','statistics_id']);
            $table->renameColumn('resorts_id', 'resort_id');
            $table->primary(['resort_id','statistics_id']);
        });
    }
}
