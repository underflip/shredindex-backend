<?php

namespace Underflip\Resorts\Models;

use Model;
use October\Rain\Database\Relations\BelongsTo;
use System\Models\File;

/**
 * Continents for the Resorts
 *
 * @property Resort $resort
 * @method BelongsTo resort()
 */
class Continent extends Model
{

    public $timestamps = false;
    public $table = 'underflip_resorts_continents';

    public $fillable = ['name', 'code', 'continent_id'];

    public function countries()
    {
        return $this->hasMany(Country::class);
    }
}
