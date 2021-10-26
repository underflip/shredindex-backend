<?php

namespace Underflip\Resorts\Models;

use Model;
use October\Rain\Database\Traits\Sortable;
use System\Models\File;

/**
 * An image of a Resort
 *
 * @property string name
 * @property string url
 * @property File image
 * @method File image()
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
}
