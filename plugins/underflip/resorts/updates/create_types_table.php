<?php namespace Underflip\Resorts\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateTypesTable extends Migration
{
    /**
     * @var string
     */
    private static $table_name = 'underflip_resorts_types';

    /**
     * @return void
     */
    public function up()
    {
        Schema::create(self::$table_name, function($table)
        {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('title');
            $table->string('category');
            $table->string('default')->nullable();
            $table->integer('unit_id')->nullable();
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(self::$table_name);
    }
}
