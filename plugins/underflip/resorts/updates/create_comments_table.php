<?php namespace Underflip\Resorts\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;
use Underflip\Resorts\Models\Comment;

class CreateCommentsTable extends Migration
{
    public function up()
    {
        Schema::create(app(Comment::class)->getTable(), function ($table) {
            $table->increments('id');
            $table->integer('resort_id')->nullable();
            $table->text('comment');
            $table->string('author');
        });
    }

    /**
     * @codeCoverageIgnore
     */
    public function down()
    {
        Schema::dropIfExists(app(Comment::class)->getTable());
    }
}
