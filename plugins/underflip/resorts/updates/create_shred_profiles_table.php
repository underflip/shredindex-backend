<?php namespace Underflip\Resorts\Updates;

use Schema;
use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;

/**
 * CreateShredProfilesTable Migration
 *
 * @link https://docs.octobercms.com/3.x/extend/database/structure.html
 */
return new class extends Migration
{
    /**
     * up builds the migration
     */
    public function up()
    {
        Schema::create('shred_profiles', function(Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('member_tier', ['free', 'pro', 'life-time', 'pro-2'])->nullable();
            $table->enum('preferred_sport', ['skiing', 'snowboarding', 'both', 'other'])->nullable();
            $table->enum('skill_level', ['beginner', 'intermediate', 'advanced', 'expert'])->nullable();
            $table->integer('years_experience')->nullable();
            $table->string('favorite_resort')->nullable();
            $table->string('current_resort_location')->nullable();
            $table->json('visited_resorts')->nullable();
            $table->enum('preferred_terrain', ['groomed', 'off-piste', 'park', 'half-pipe', 'tree-runs' ,'all'])->nullable();
            $table->enum('preferred_resort_type', ['family', 'seasonal_worker', 'hardcore', 'helicoptor', 'ski-bum', 'average-joe', 'racer', 'moguls', 'freestyle'])->nullable();
            $table->string('equipment_brand')->nullable();
            $table->boolean('owns_equipment')->default(false);
            $table->date('season_pass_type')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->text('bio')->nullable();
            $table->string('profile_picture')->nullable();
            $table->json('preferred_lessons')->nullable(); // e.g., ['group', 'private', 'off-piste']
            $table->boolean('interested_in_competitions')->default(false);
            $table->json('achievements')->nullable(); // e.g., ['black diamond mastered', 'completed park course']
            $table->timestamps();
        });
    }

    /**
     * down reverses the migration
     */
    public function down()
    {
        Schema::dropIfExists('shred_profiles');
    }
};
