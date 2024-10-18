<?php namespace Underflip\Resorts\Models;

use Model;
use RainLab\User\Models\User as UserRailLab;

/**
 * User Model
 *
 * @link https://docs.octobercms.com/3.x/extend/system/models.html
 */
class User extends UserRailLab {
	protected $fillable = ['name', 'email', 'provider', 'provider_id'];

	public function __construct() {
		parent::__construct();
		$this->hasOne['shred_profile'] = [ShredProfiles::class, 'delete' => true];

	}
}
