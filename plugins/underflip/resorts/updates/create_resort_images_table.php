<?php

namespace Underflip\Resorts\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;
use Underflip\Resorts\Models\ResortImage;
use Underflip\Resorts\Models\Generic;

class CreateResortImagesTable extends Migration
{
    public function up()
    {
        Schema::create(app(ResortImage::class)->getTable(), function ($table) {
            $table->increments('id');
            $table->integer('resort_id')->nullable();
            $table->string('name');
            $table->string('url');
            $table->integer('sort_order')->nullable();
        });
    }

    public function down()
    {
        foreach (ResortImage::all() as $resort_image) {
            if ($resort_image->image) {
                // Delete image to avoid stale data on next up
                $resort_image->image->delete();
            }
        }

        Schema::dropIfExists(app(ResortImage::class)->getTable());
    }
}
