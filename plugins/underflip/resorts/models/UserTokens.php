<?php namespace Underflip\Resorts\Models;

use Model;
use October\Rain\Database\Builder;
use October\Rain\Database\Traits\Validation;
use RainLab\User\Models\User;

/**
 * Used to hold records for a set of values that make up an enum-like structure (e.g Yes, No, or Maybe)
 *
 * @property int $id
 * @property string $name The unique name of the value
 * @property string $title
 * @method Builder types()
 */
class UserTokens extends Model
{
    use Validation;

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    // public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'user_tokens';

    /**
     * @var array Validation rules
     */
    public $rules = [
        // 'user_id' => 'required',
        // 'token' => 'required'
    ];

    protected $fillable = ['user_id', 'token','revoke'];

    /**
     * @var array
     */
    public $belongsTo = [
        'user' => [
            User::class
        ],
    ];
}
