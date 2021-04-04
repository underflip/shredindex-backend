<?php


namespace Underflip\Resorts\Models;

use October\Rain\Database\Traits\Validation;
use Underflip\Resorts\Traits\Filterable;

/**
 * A string-based resort attribute for resort characteristics that are
 * expressed in a mixture of ways that do not require empirical filters
 *
 * @property string $value
 */
class Generic extends ResortAttribute
{
    use Filterable;
    use Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'underflip_resorts_generics';

    /**
     * @var string
     */
    public $filterColumn = 'value';

    /**
     * @var array
     */
    protected $validOperators = [
        '=', '!=', 'like', 'not like', 'ilike',
    ];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'value' => 'required',
    ];
}
