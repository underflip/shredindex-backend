<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateUnderflipResortStatisticSchema extends Migration
{
    public function up()
    {
        Schema::rename('underflip_resort_statistic_name', 'underflip_resort_statistic_schema');
    }
    
    public function down()
    {
        Schema::rename('underflip_resort_statistic_schema', 'underflip_resort_statistic_name');
    }
}
