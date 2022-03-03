<?php

namespace Underflip\Resorts\Models;

use Model;
use October\Rain\Database\Relations\AttachOne;
use October\Rain\Database\Traits\Sortable;
use System\Models\File;

/**
 * A supporter of Shredindex
 *
 * @property string name
 * @property string url
 * @property File image
 * @method AttachOne image()
 */
class Supporter extends Model
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
    public $table = 'underflip_resorts_supporters';

    /**
     * @var array
     */
    public $attachOne = [
        'image' => File::class,
    ];
}
