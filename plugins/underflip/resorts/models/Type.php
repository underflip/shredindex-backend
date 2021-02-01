<?php namespace Underflip\Resorts\Models;

use Model;
use October\Rain\Database\Traits\Validation;

/**
 * Model
 */
class Type extends Model
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
    public $table = 'underflip_resorts_types';

    /**
     * @var array Validation rules
     */
    public $rules = [];

    /**
     * @var array
     */
    public $belongsTo = [
        'unit' => Unit::class,
    ];

    /**
     * @var array
     */
    public $hasMany = [
        'scores' => Score::class,
        'stats' => Stat::class,
    ];

    /**
     * @var array
     */
    public $belongsToMany = [
        'values' => [
            TypeValue::class,
            'table' => 'underflip_type_type_value_relation',
        ],
    ];

    /**
     * Prescribes the set of category options
     */
    public function getCategoryOptions()
    {
        return [
            Cost::class => 'Cost',
            Insight::class => 'Insight',
            Score::class => 'Score',
            Stat::class => 'Statistic',
        ];
    }
}
