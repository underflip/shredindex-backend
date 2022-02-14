<?php

namespace Underflip\Resorts\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Model;
use October\Rain\Database\Traits\Validation;

/**
 * Resort model
 *
 * @property int $id
 * @property string $title
 * @property string $url_segment
 * @property string $description
 * @property Location $location
 * @property int location_id
 * @method HasOne location()
 * @method Collection ratings()
 * @method Collection numerics()
 * @method Collection generics()
 */
class Resort extends Model
{
    /*
     * Disable timestamps by default, to remove the need for updated_at and
     * created_at columns.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'underflip_resorts_resorts';

    /**
     * @var array
     */
    public $hasOne = [
        'location' => Location::class,
    ];

    /**
     * @var array
     */
    public $hasMany = [
        'ratings' => Rating::class,
        'numerics' => Numeric::class,
        'generics' => Generic::class,
    ];

}
