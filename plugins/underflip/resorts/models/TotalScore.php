<?php

namespace Underflip\Resorts\Models;

use October\Rain\Database\Traits\Validation;
use Underflip\Resorts\Plugin;
use Underflip\Resorts\Traits\Filterable;

/**
 * An empirical score, usually out of 100 e.g 99.9
 *
 * Typically total score attributes are generated automagically and not by a CMS
 * author
 *
 * @property int $value
 */
class TotalScore extends ResortAttribute
{
    use Filterable;
    use Validation;

    public const TYPE_NAME_TOTAL_SCORE = 'total_score'; // WARNING: Changing this will NOT update existing records

    /**
     * @var string The database table used by the model.
     */
    public $table = 'underflip_resorts_total_scores';

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

    /**
     * @return Type
     * @throws \Exception
     */
    public function findOrCreateType()
    {
        $type = Type::get()->where('name', self::TYPE_NAME_TOTAL_SCORE)->first();

        if (!$type) {
            // Create the total score type
            // Get the unit for the total score
            $score = Unit::where('name', Plugin::UNIT_NAME_SCORE)->first();

            if (!$score) {
                // We can't denormalize without the unit
                throw new \Exception(sprintf(
                    'Unable to query required Underflip\Resorts\Models\Unit: "%s"',
                    Plugin::UNIT_NAME_SCORE
                ));
            }

            // Create the type for our total shred score rating
            $type = new Type();
            $type->name = self::TYPE_NAME_TOTAL_SCORE;
            $type->title = 'Total Score';
            $type->category = TotalScore::class;
            $type->unit_id = $score->id;
            $type->save();
        }

        return $type;
    }
}
