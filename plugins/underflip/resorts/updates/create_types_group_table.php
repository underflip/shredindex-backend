<?php

namespace Underflip\Resorts\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Underflip\Resorts\Models\TypeGroup;

class CreateTypesGroupTable extends Migration
{
    /**
     * @return void
     */
    public function up()
    {
        Schema::create(app(TypeGroup::class)->getTable(), function ($table) {
            $table->increments('id');
            $table->string('name')->unique();
            $table->string('title');
        });
    }

    /**
     * @codeCoverageIgnore
     */
    public function down()
    {
        Schema::dropIfExists(app(TypeGroup::class)->getTable());
    }
}
