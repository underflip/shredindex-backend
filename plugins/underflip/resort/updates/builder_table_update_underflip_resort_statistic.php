<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateUnderflipResortStatistic extends Migration
{
    public function up()
    {
        Schema::rename('underflip_resort_statistics', 'underflip_resort_statistic');
    }
    
    public function down()
    {
        Schema::rename('underflip_resort_statistic', 'underflip_resort_statistics');
    }
}
