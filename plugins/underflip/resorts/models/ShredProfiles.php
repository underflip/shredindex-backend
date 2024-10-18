<?php namespace Underflip\Resorts\Models;

use Model;

/**
 * ShredProfiles Model
 *
 * @link https://docs.octobercms.com/3.x/extend/system/models.html
 */
class ShredProfiles extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string table name
     */
    public $table = 'shred_profiles';

    /**
     * @var array rules for validation
     */
    public $rules = [];

    public $belongsTo = [
        'user' => User::class
    ];

    /*protected $casts = [
        // 'visited_resorts' => 'array',  
        // 'preferred_lessons' => 'array',  
        // 'achievements' => 'array',  
    ];*/
}
