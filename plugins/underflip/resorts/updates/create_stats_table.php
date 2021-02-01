<?php namespace Underflip\Resorts\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateStatsTable extends Migration
{

    /**
     * @var string
     */
    private static $table_name = 'underflip_resorts_stats';


    public function up()
    {
        Schema::create(self::$table_name, function($table)
        {
            $table->increments('id');
            $table->string('value');
            $table->integer('resort_id')->nullable();
            $table->integer('type_id')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::$table_name);
    }
}