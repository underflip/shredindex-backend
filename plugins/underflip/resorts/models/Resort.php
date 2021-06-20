<?php

namespace Underflip\Resorts\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Model;
use October\Rain\Database\Traits\Validation;

/**
 * Resort model
 *
 * @property int $id
 * @property string $title
 * @property string $url_segment
 * @method Collection ratings()
 * @method Collection stats()
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
