<?php namespace Underflip\Resorts\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Underflip\Resorts\Models\Resort;

class CreateResortsTable extends Migration
{
    public function up()
    {
        Schema::create(app(Resort::class)->getTable(), function ($table) {
            $table->increments('id');
            $table->string('title');
            $table->string('url_segment')->unique();
            $table->text('affiliate_url');
            $table->text('description');
        });
    }

    /**
     * @codeCoverageIgnore
     */
    public function down()
    {
        Schema::dropIfExists(app(Resort::class)->getTable());
    }
}
