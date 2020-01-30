<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateUnderflipResortStatistic4 extends Migration
{
    public function up()
    {
        Schema::table('underflip_resort_statistic', function($table)
        {
            $table->string('value', 191)->nullable()->change();
            $table->integer('resort_id')->nullable()->change();
            $table->integer('statistic_name_id')->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('underflip_resort_statistic', function($table)
        {
            $table->string('value', 191)->nullable(false)->change();
            $table->integer('resort_id')->nullable(false)->change();
            $table->integer('statistic_name_id')->nullable(false)->change();
        });
    }
}
