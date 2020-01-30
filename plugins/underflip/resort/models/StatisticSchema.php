<?php namespace UnderFlip\Resort\Models;

use Model;

/**
 * Model
 */
class StatisticSchema extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;


    /**
     * @var string The database table used by the model.
     */
    public $table = 'underflip_resort_statistic_schema';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * @var array
     */
    public $hasMany = [
        'statistic' => [
            Statistic::class,
            'table' => 'underflip_resort_statistic',
            'order' => 'id',
        ]
    ];
}
