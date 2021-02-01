<?php namespace Underflip\Resorts\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class CreateResortsTable extends Migration
{

    /**
     * @var string
     */
    private static $table_name = 'underflip_resorts_resorts';
    
    public function up()
    {
        Schema::create(self::$table_name, function($table)
        {
            $table->increments('id');
            $table->string('title');
            $table->string('url_segment')->unique();        
        });
    }

    public function down()
    {
        Schema::dropIfExists(self::$table_name);
    }
}