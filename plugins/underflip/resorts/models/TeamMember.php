<?php

namespace Underflip\Resorts\models;

use Model;
use October\Rain\Database\Traits\Sortable;
use System\Models\File;

/**
 * A team member behind Shredindex
 *
 * @property string name
 * @property string url
 * @property File image
 * @method File image()
 */
class TeamMember extends Model
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
    public $table = 'underflip_resorts_team_members';

    /**
     * @var array
     */
    public $attachOne = [
        'image' => File::class,
    ];
}
