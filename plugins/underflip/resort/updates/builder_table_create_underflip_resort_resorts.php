<?php namespace UnderFlip\Resort\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class BuilderTableCreateUnderflipResortResorts extends Migration
{
    public function up()
    {
        Schema::create('underflip_resort_resorts', function($table)
        {
            $table->engine = 'InnoDB';
            $table->integer('id');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('underflip_resort_resorts');
    }
}
