<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableUpdateUnderflipResortResorts2 extends Migration
{
    public function up()
    {
        Schema::rename('underflip_resort_resort', 'underflip_resort_resorts');
    }
    
    public function down()
    {
        Schema::rename('underflip_resort_resorts', 'underflip_resort_resort');
    }
}
