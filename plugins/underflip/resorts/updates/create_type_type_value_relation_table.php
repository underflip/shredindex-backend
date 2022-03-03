<?php namespace Underflip\Resorts\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateTypeTypeValueRelationTable extends Migration
{
    /**
     * @var string
     */
    private static $table_name = 'underflip_type_type_value_relation';

    public function up()
    {
        Schema::create(self::$table_name, function ($table) {
            $table->integer('type_id')->unsigned();
            $table->integer('type_value_id')->unsigned();
            $table->primary(['type_id', 'type_value_id']);
        });
    }

    /**
     * @codeCoverageIgnore
     */
    public function down()
    {
        Schema::dropIfExists(self::$table_name);
    }
}
