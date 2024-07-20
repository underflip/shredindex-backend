<?php namespace Underflip\Resorts\Models;

use Illuminate\Database\Eloquent\Collection;
use Model;
use October\Rain\Database\Traits\Validation;
use ReflectionClass;

/**
 * The grouping of an attribute that applies to a resort, usually via
 *
 * @property int $id
 * @property string $name The unique name of the type group
 * @property string $title The title of the type group
 * @method Collection values()
 */
class TypeGroup extends Model
{
    use Validation;

    protected $fillable = ['name', 'title'];

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'underflip_resorts_types_group';

    /**
     * @var array Validation rules
     */
    public $rules = [];

    /**
     * @var array
     */
    public $belongsTo = [
        'type' => Type::class,
    ];
}
