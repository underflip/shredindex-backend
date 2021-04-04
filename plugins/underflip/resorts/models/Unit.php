<?php namespace Underflip\Resorts\Models;

use Model;
use October\Rain\Database\Builder;
use October\Rain\Database\Traits\Validation;

/**
 * @property int $id
 * @property string $name The unique name of the type
 * @property string $title
 * @property string $singular_title
 * @property string $plural_title
 * @property string $format The symbolic representation of the unit e.g 'm' for meters
 * @property string $plural_format
 * @method Builder types()
 */
class Unit extends Model
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
    public $table = 'underflip_resorts_units';

    /**
     * @var array Validation rules
     */
    public $rules = [];

    /**
     * @var array
     */
    public $hasMany = [
        'types' => Type::class,
    ];
}
