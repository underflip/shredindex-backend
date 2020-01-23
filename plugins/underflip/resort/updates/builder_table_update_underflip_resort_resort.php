<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateUnderflipResortResort extends Migration
{
    public function up()
    {
        Schema::rename('underflip_resort_resorts', 'underflip_resort_resort');
    }
    
    public function down()
    {
        Schema::rename('underflip_resort_resort', 'underflip_resort_resorts');
    }
}
