<?php

namespace Underflip\Resorts\Models;

use Model;
use System\Models\File;

/**
 * Comments for the Resorts
 *
 */
class Comment extends Model
{
    /*
     * Disable timestamps by default, to remove the need for updated_at and
     * created_at columns.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'underflip_resorts_comments';

    /**
     * @var array Validation rules
     */
    public $rules = [
        'value' => 'required',
    ];

    /**
     * @var array The class it belongsTo
     */
    public $belongsTo = [
      'resort' => Resort::class,
    ];
}
