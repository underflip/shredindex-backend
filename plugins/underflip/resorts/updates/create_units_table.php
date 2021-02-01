<?php namespace Underflip\Resorts\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateUnitsTable extends Migration
{
    /**
     * @var string
     */
    private static $table_name = 'underflip_resorts_units';

    public function up()
    {
        Schema::create(self::$table_name, function($table)
        {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('title');
            $table->string('singular_title');
            $table->string('plural_title');
            $table->string('format')->nullable();
            $table->string('plural_format')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::$table_name);
    }
}
