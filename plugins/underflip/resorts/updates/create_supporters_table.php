<?php

namespace Underflip\Resorts\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;
use Underflip\Resorts\Models\Supporter;

class CreateSupportersTable extends Migration
{
    public function up()
    {
        Schema::create(app(Supporter::class)->getTable(), function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('url');
            $table->integer('sort_order')->nullable();
        });
    }

    public function down()
    {
        foreach (Supporter::all() as $supporter) {
            if ($supporter->image) {
                // Delete image to avoid stale data on next up
                $supporter->image->delete();
            }
        }

        Schema::dropIfExists(app(Supporter::class)->getTable());
    }
}
