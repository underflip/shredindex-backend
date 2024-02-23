<?php namespace Underflip\Resorts\Models;

use Illuminate\Database\Eloquent\Collection;
use Model;
use October\Rain\Database\Traits\Validation;
use ReflectionClass;

/**
 * The descriptor of an attribute that applies to a resort, usually via
 * something like a {@see Score} or a {@see Stat}
 *
 * @property int $id
 * @property string $name The unique name of the type
 * @property string $title
 * @property string $category A broad "belongs to" value of the type of class that can be related
 * @property string $default The default value
 * @property int $unit_id
 * @method Unit unit() The unit of measurement or denomination
 * @method Collection values()
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
        'ratings' => Rating::class,
        'numerics' => Numeric::class,
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
     * Express each resort attribute as a category for types, so that
     * we can reference types categorically
     *
     * @return array ['Underflip/Resorts/Models/ClassName' => 'ClassName']
     * @throws \ReflectionException
     */
    public static function getCategories(): array
    {
        $catagoricalAttributeClasses = [
            Rating::class,
            Numeric::class,
            Generic::class,
            TotalScore::class,
        ];

        $categories = [];

        foreach ($catagoricalAttributeClasses as $class) {
            $categories[$class] = (new ReflectionClass($class))->getShortName();
        }

        return $categories;
    }

    /**
     * See plugins/underflip/resorts/models/type/fields.yml
     *
     * @return array
     */
    public function getCategoryOptions(): array
    {
        return static::getCategories();
    }

    public function numeric()
    {
        return $this->hasOne(\Underflip\Resorts\Models\Numeric::class);
    }
}
