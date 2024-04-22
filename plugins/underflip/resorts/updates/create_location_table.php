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
            $table->string('address');
            $table->string('city');
            $table->string('zip')->nullable();
            $table->integer('country_id');
            $table->integer('state_id')->nullable();
            $table->decimal('latitude');
            $table->decimal('longitude');
            $table->string('vicinity');
            $table->integer('continent_id')->nullable();
        });
    }

    /**
     * @codeCoverageIgnore
     */
    public function down()
    {
        Schema::dropIfExists(app(Location::class)->getTable());
    }
}
