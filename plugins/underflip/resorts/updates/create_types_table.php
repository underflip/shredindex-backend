<?php namespace Underflip\Resorts\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Underflip\Resorts\Models\Type;

class CreateTypesTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        Schema::create(app(Type::class)->getTable(), function ($table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('title');
            $table->string('category');
            $table->string('default')->nullable();
            $table->integer('unit_id')->nullable();
            $table->string('icon')->nullable();
            $table->decimal('max_value')->nullable();
        });
    }

    /**
     * @codeCoverageIgnore
     */
    public function down()
    {
        Schema::dropIfExists(app(Type::class)->getTable());
    }
}
