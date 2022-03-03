<?php

namespace Underflip\Resorts\Models;

use Model;
use October\Rain\Database\Relations\AttachOne;
use October\Rain\Database\Relations\BelongsTo;
use October\Rain\Database\Traits\Sortable;
use System\Models\File;

/**
 * An image of a Resort
 *
 * @property string $name
 * @property string $alt
 * @property File $image
 * @property Resort $resort
 * @method AttachOne image()
 * @method BelongsTo resort();
 */
class ResortImage extends Model
{
    use Sortable;

    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'underflip_resorts_resort_images';

    /**
     * @var array
     */
    public $attachOne = [
        'image' => File::class,
    ];

    /**
     * @var array
     */
    public $belongsTo = [
        'resort' => Resort::class,
    ];
}
