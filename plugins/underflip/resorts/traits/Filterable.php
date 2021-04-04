<?php

namespace Underflip\Resorts\Traits;

use Exception;
use Underflip\Resorts\Models\ResortAttribute;

/**
 * Decorate a {@see ResortAttribute} to support filtering by that attribute
 */
trait Filterable
{
    /**
     * @var string The column to use for comparisons/filtering
     *
     * public $filterColumn = 'value';
     */

    /**
     * @var array Eloquent operators that can be applied to this
     * attribute's operative column
     *
     * protected $validOperators = [
     *  '=', '<', '>', '<=', '>=', '<>', '!=', 'like', 'not like', 'ilike', '&', '|', '<<', '>>',
     * ];
     */

    /**
     * When we use a trait with an Eloquent model, we get some extra magic that
     * boots the trait by name
     *
     * @return void
     * @throws Exception
     */
    public static function bootFilterable(): void
    {
        if (!property_exists(get_called_class(), 'filterColumn')) {
            throw new Exception(sprintf(
                'You must define a $filterColumn property in %s to use the Filterable trait.',
                get_called_class()
            ));
        }

        if (!property_exists(get_called_class(), 'validOperators')) {
            throw new Exception(sprintf(
                'You must define a $validOperators property in %s to use the Filterable trait.',
                get_called_class()
            ));
        }
    }

    /**
     * @return array
     */
    public function getValidOperators(): array
    {
        return $this->validOperators;
    }

    /**
     * @param string $operator
     * @return bool
     */
    public function isValidOperator(string $operator): bool
    {
        return in_array($operator, $this->getValidOperators());
    }
}
