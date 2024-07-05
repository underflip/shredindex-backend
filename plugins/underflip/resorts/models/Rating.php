<?php

namespace Underflip\Resorts\Models;

use Event;
use October\Rain\Database\Traits\Validation;
use Underflip\Resorts\Traits\Filterable;

/**
 * An empirical score, usually out of 100
 *
 * @property int $value
 */
class Rating extends ResortAttribute
{
    use Filterable;
    use Validation;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'underflip_resorts_ratings';

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
        'value' => 'required|numeric|max:100',
    ];

    protected function afterSave()
    {
        parent::afterUpdate();

        Event::fire('rating.save', [$this]);
    }

    protected function afterDelete()
    {
        parent::afterDelete();

        Event::fire('rating.delete', [$this]);
    }
}
