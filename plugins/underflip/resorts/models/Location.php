<?php

namespace Underflip\Resorts\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Model;

/**
 * A location resort attribute for resort location that are
 * expressed as an object, e.g country, state , zip
 *
 * @property Resort $resort
 * @property string address
 * @property string city
 * @property string zip
 * @property int country_id
 * @property int state_id
 * @property float latitude
 * @property float longitude
 * @property string vicinity
 * @property int resort_id
 * @method BelongsTo resort()
 *
*/

class Location extends Model
{
    /*
     * Disable timestamps by default, to remove the need for updated_at and
     * created_at columns.
     */
    public $timestamps = false;

    /**
     * @var array
     */
    public $implement = ['RainLab.Location.Behaviors.LocationModel'];

    /**
     * @var string
     */
    public $table = 'underflip_resorts_location';

    /**
     * @var array
     */
    public $belongsTo = [
      'resort' => Resort::class,
    ];

    public function continent()
    {
        return $this->belongsTo(Continent::class);
    }
}
