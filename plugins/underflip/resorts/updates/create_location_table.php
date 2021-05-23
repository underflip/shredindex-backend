<?php

namespace Underflip\Resorts\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;
use Underflip\Resorts\Models\Location;

class CreateLocationTable extends Migration
{
    public function up()
    {
        Schema::create(app(Location::class)->getTable(), function ($table) {
                    $table->increments('id');
                    $table->integer('resort_id')->nullable();
                    $table->integer('country_id')->nullable();;
                    $table->integer('state_id')->unique()->nullable();;
        });
    }

    public function down()
    {
        Schema::dropIfExists(app(Location::class)->getTable());
    }
}
