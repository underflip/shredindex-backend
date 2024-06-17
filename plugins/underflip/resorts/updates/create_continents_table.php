<?php

namespace Underflip\Resorts\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;
use Underflip\Resorts\Models\Continent;

class CreateContinentsTable extends Migration
{
    public function up()
    {
        Schema::create(app(Continent::class)->getTable(), function ($table) {
            $table->increments('id'); // Primary key
            $table->string('name');
            $table->string('code');
            $table->timestamps(); // Add created_at and updated_at columns if needed
        });
    }

    public function down()
    {
        Schema::dropIfExists(app(Continent::class)->getTable()); // Drop continents table
    }
}
