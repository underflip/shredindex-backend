<?php namespace Underflip\Resorts\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateScoresTable extends Migration
{

    /**
     * @var string
     */
    private static $table_name = 'underflip_resorts_scores';

    public function up()
    {
        Schema::create(self::$table_name, function($table)
        {
            $table->increments('id');
            $table->smallInteger('score');
            $table->integer('resort_id')->nullable();;
            $table->integer('type_id')->nullable();;
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::$table_name);
    }
}
