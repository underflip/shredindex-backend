<?php namespace Underflip\Resorts\Updates;

use October\Rain\Database\Schema\Blueprint;
use October\Rain\Database\Updates\Migration;
use Schema;

/**
 * CreateUserTokensTable Migration
 *
 * @link https://docs.octobercms.com/3.x/extend/database/structure.html
 */
return new class extends Migration {
	/**
	 * up builds the migration
	 */
	public function up() {
		Schema::table('users', function (Blueprint $table) {
			$table->string('provider')->nullable();
			$table->string('provider_id')->nullable();
		});
	}

	/**
	 * down reverses the migration
	 */
	public function down() {
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn(['provider', 'provider_id']);
		});
	}
};
