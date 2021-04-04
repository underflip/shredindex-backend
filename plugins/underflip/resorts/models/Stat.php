<?php namespace Underflip\Resorts\Models;

use Model;
use October\Rain\Database\Traits\Validation;

/**
 * A statistical value
 *
 * @property int $id
 * @property string $value
 * @method Resort resort()
 * @method Type type()
 */
class Stat extends Model
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
    public $table = 'underflip_resorts_stats';

    /**
     * @var array Validation rules
     */
    public $rules = [];

    /**
     * @var array
     */
    public $belongsTo = [
        'resort' => Resort::class,
        'type' => Type::class,
    ];
}
