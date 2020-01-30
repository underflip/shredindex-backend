<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateUnderflipResortStatisticSchema2 extends Migration
{
    public function up()
    {
        Schema::table('underflip_resort_statistic_schema', function($table)
        {
            $table->string('name', 191)->nullable()->change();
            $table->string('type', 191)->nullable()->change();
        });
    }
    
    public function down()
    {
        Schema::table('underflip_resort_statistic_schema', function($table)
        {
            $table->string('name', 191)->nullable(false)->change();
            $table->string('type', 191)->nullable(false)->change();
        });
    }
}
