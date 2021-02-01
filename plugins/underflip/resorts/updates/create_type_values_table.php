<?php namespace Underflip\Resorts\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateTypeValuesTable extends Migration
{

    /**
     * @var string
     */
    private static $table_name = 'underflip_resorts_type_values';


    public function up()
    {
        Schema::create(self::$table_name, function($table)
        {
            $table->increments('id');
            $table->string('name');
            $table->string('title');
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::$table_name);
    }
}