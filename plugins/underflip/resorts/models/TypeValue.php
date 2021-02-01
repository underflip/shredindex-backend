<?php namespace Underflip\Resorts\Models;

use Model;
use October\Rain\Database\Traits\Validation;

/**
 * Used to hold records for a set of values that make up an enum-like structure (e.g Yes, No, or Maybe)
 */
class TypeValue extends Model
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
    public $table = 'underflip_resorts_type_values';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'name' => 'required|unique:underflip_resorts_type_values'
    ];

    /**
     * @var array
     */
    public $belongsToMany = [
        'types' => [
            Type::class,
            'table' => 'underflip_type_type_value_relation',
        ],
    ];
}
