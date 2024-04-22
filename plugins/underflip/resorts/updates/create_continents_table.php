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
            $table->increments('id'); // Add primary key
            $table->string('name');
            $table->string('code');
            $table->integer('continent_id')->unsigned();

             // Foreign key constraints
            $table->foreign('continent_id')->references('id')->on('underflip_resorts_continents'); // Replace with your actual continents table name
        });
    }

    public function down()
    {
        Schema::dropIfExists(app(Continent::class)->getTable()); // Drop continents table
    }
}
