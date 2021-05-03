<?php

namespace Underflip\Resorts\Updates;

use October\Rain\Database\Updates\Migration;
use Schema;
use Underflip\Resorts\models\TeamMember;

class CreateTeamMembersTable extends Migration
{
    public function up()
    {
        Schema::create(app(TeamMember::class)->getTable(), function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('url');
            $table->integer('sort_order')->nullable();
        });
    }

    public function down()
    {
        foreach (TeamMember::all() as $member) {
            if ($member->image) {
                // Delete image to avoid stale data on next up
                $member->image->delete();
            }
        }

        Schema::dropIfExists(app(TeamMember::class)->getTable());
    }
}
