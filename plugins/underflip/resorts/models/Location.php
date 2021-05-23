<?php

namespace Underflip\Resorts\Models;

use Model;

class Location extends Model
{
    /*
     * Disable timestamps by default, to remove the need for updated_at and
     * created_at columns.
     */
    public $timestamps = false;

    /**
     * @var Location plugin used by the model.
     */
    public $implement = ['RainLab.Location.Behaviors.LocationModel'];


    public $table = 'underflip_resorts_location';


}

