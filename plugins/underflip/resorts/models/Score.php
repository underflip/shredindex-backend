<?php namespace Underflip\Resorts\Models;

use Model;
use October\Rain\Database\Traits\Validation;

/**
 * Model
 */
class Score extends Model
{
    use Validation;

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'underflip_resorts_scores';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'score' => 'required|numeric|max:100',
    ];

    /**
     * @var array
     */
    public $belongsTo = [
        'resort' => Resort::class,
        'type' => Type::class,
    ];
}
