<?php

namespace Underflip\Resorts\Models;

use October\Rain\Database\Traits\Validation;
use Underflip\Resorts\Traits\Filterable;

/**
 * A numeric resort attribute for resort characteristics that are
 * expressed as a number, e.g 2.8m of Snowfall
 *
 * @property float $value
 */
class Numeric extends ResortAttribute
{
    use Filterable;
    use Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'underflip_resorts_numerics';

    /**
     * @var string
     */
    public $filterColumn = 'value';

    /**
     * @var array
     */
    protected $validOperators = [
        '=', '<', '>', '<=', '>=', '<>', '!=',
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'value' => 'required|numeric',
    ];
}
