<?php namespace UnderFlip\Resort\Models;

use Model;

/**
 * Model
 */
class Statistic extends Model
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
    public $table = 'underflip_resort_statistic';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * @var array
     */
    public $belongsTo = [
        'resort' => [
            Resort::class,
            'table' => 'underflip_resort_resort',
            'order' => 'id',
        ]
    ];

     /**
     * @var array
     */
    public $hasOne = [
        'statisticschema' => [
            StatisticSchema::class,
            'table' => 'underflip_resort_statistic_schema',
            'order' => 'id',
        ]
    ];
}
