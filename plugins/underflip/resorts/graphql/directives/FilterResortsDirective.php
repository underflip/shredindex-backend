<?php

namespace Underflip\Resorts\GraphQL\Directives;

use Closure;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Nuwave\Lighthouse\Pagination\PaginationManipulator;
use Nuwave\Lighthouse\Pagination\PaginationType;
use Nuwave\Lighthouse\Pagination\PaginationUtils;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use PHPUnit\Util\Filter;
use Underflip\Resorts\Models\Resort;
use Underflip\Resorts\Models\Type;
use Underflip\Resorts\Plugin;
use Underflip\Resorts\Traits\Filterable;

/**
 * A Lighthouse Directive (GraphQL) for filtering resorts by meaningful metrics
 *
 * {@see Plugin} to find how this directive's namespace is configured.
 */
class FilterResortsDirective extends BaseDirective implements FieldResolver, FieldManipulator
{
    /**
     * @throws \Exception
     */
    public function __construct()
    {
        // Save us from some embarrassment with some quick validation
        foreach ($this->getFilterableScopes() as $scope) {
            if (count(array_diff(['relation', 'class', 'column'], array_keys($scope)))) {
                // Throw a helpful message
                throw new \Exception(
                    'A query scope has an unexpected structure. Expecting relation, class and column.'
                );
            }
        }
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return 'filterResorts';
    }

    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Query multiple entries as a paginated list.
"""
directive @filterResorts(
  """
  maxCount: Int

  """
  Use a default value for the amount of returned items
  in case the client does not request it explicitly
  """
  defaultCount: Int
) on FIELD_DEFINITION
SDL;
    }

    /**
     * @param  \Nuwave\Lighthouse\Schema\AST\DocumentAST  $documentAST
     * @param  \GraphQL\Language\AST\FieldDefinitionNode  $fieldDefinition
     * @param  \GraphQL\Language\AST\ObjectTypeDefinitionNode  $parentType
     * @return void
     */
    public function manipulateFieldDefinition(
        DocumentAST &$documentAST,
        FieldDefinitionNode &$fieldDefinition,
        ObjectTypeDefinitionNode &$parentType
    ): void {
        PaginationManipulator::transformToPaginatedField(
            $this->paginationType(),
            $fieldDefinition,
            $parentType,
            $documentAST,
            20,
            $this->paginateMaxCount()
        );
    }

    /**
     * The data needed to filter a resort's type relations,
     * structured by the type model's categories
     *
     * @return array
     */
    protected function getFilterableScopes(): array
    {
        $scopes = [];

        $relations = app(Resort::class)->hasMany + app(Resort::class)->hasOne;

        foreach ($relations as $name => $class) {
            if (!in_array(Filterable::class, class_uses_recursive($class))) {
                // Relation isn't filterable
                continue;
            }

            $scopes[] = [
                'relation' => $name,
                'class' => $class,
                'column' => app($class)->filterColumn
            ];
        }

        return $scopes;
    }

    /**
     * Resolve the field directive.
     *
     * @param FieldValue $fieldValue
     * @return FieldValue|void
     */
    public function resolveField(FieldValue $fieldValue): FieldValue
    {
        return $fieldValue->setResolver($this->getResolver());
    }

    /**
     * Get the wrapped resolver
     *
     * @return Closure
     */
    public function getResolver(): Closure
    {
        return function ($root, array $args): LengthAwarePaginator {
            // Setup builder
            $query = Resort::with(['ratings', 'numerics', 'generics']);

            if (array_key_exists('filter', $args)) {
                // Apply filters
                $this->augmentForFilters($query, $args['filter']);
            }

            if (array_key_exists('orderBy', $args)) {
                $this->orderByType($query, $args['orderBy']);
            }

            // Pagination
            [$first, $page] = PaginationUtils::extractArgs(
                $args,
                $this->paginationType(),
                $this->paginateMaxCount()
            );

            return $query->paginate($first, ['*'], 'page', $page);
        };
    }

    /**
     * @param Builder $query
     * @param array $filters
     * @throws \Exception
     */
    public function augmentForFilters(Builder &$query, array $filters): void
    {
        foreach ($this->getFilterableScopes() as $scope) {
            if (!array_key_exists($scope['class'], Type::getCategories())) {
                // Throw a helpful message
                throw new \Exception(sprintf(
                    'Can only filter relations that exist as a category of "%s"',
                    Type::class
                ));
            }

            foreach ($filters as $filter) {
                if (!Type::where('name', $filter['type_name'])->where('category', $scope['class'])->count()) {
                    // No types exist for the queried type/category
                    continue;
                }

                // We can assume that only types of the current scope's class reach this point
                $validOperators = app($scope['class'])->getValidOperators();
                if (!in_array($filter['operator'], $validOperators)) {
                    // Throw a helpful message
                    throw new \Exception(sprintf(
                        '"%s" is not a valid operator for "%s", available operators: "%s"',
                        $filter['operator'],
                        $filter['type_name'],
                        join(', ', $validOperators)
                    ));
                }

                $query->whereHas($scope['relation'], function (Builder $query) use ($filter, $scope) {
                    // Filter score's type name vs value
                    $query
                        ->where($scope['column'], $filter['operator'], $filter['value'])
                        ->whereHas('type', function (Builder $query) use ($filter) {
                            $query->where('name', $filter['type_name']);
                        });
                });
            }
        }
    }

    /**
     * Order a the builder by
     *
     * @param Builder $query
     * @param array $orderBy
     * @throws \Exception
     */
    protected function orderByType(Builder &$query, array $orderBy): void
    {
        $typeName = $orderBy['type_name']; // Assumes GraphQL input validation assures type_name exists
        $direction = $orderBy['direction'] ?: 'asc';

        // Determine which metric is being ordered
        $type = Type::where('name', '=', $typeName)->first();

        if (!$type || !$type->category) {
            throw new \Exception(sprintf(
                'Cannot order by type_name "%s". ' .
                'A Type does not exist with that name or Type does not have a category.',
                $typeName
            ));
        }

        $column = null;

        foreach ($this->getFilterableScopes() as $scope) {
            $column = $scope['class'] === $type->category ? $scope['column'] : $column;
        }

        if (!$column) {
            throw new \Exception(
                'Cannot order by a type with a category that has no column definition in getFilterableScopes()'
            );
        }

        // Sanity check that the type is in the expected category
        if (!app($type->category)->where('type_id', $type->id)->count()) {
            throw new \Exception(sprintf(
                'Unable to order by "%s": No "%s" was found with a type named "%s". ' .
                'Type "%s" probably has the wrong category.',
                $typeName,
                $type->category,
                $typeName,
                $typeName
            ));
        }

        // Order by values of that metric
        $query
            // Explicate the select to avoid fields being overridden by the join table
            ->select('underflip_resorts_resorts.*')
            // Attempt the join
            ->join(
                sprintf('%s as comparisons', app($type->category)->getTable()),
                function (JoinClause $join) use ($orderBy, $type) {
                    $join
                        ->on('underflip_resorts_resorts.id', '=', 'comparisons.resort_id')
                        ->where('comparisons.type_id', '=', $type->id);
                }
            )
            ->orderBy(sprintf('comparisons.%s', $column), $direction);
    }

    /**
     * @return PaginationType
     */
    protected function paginationType(): PaginationType
    {
        return new PaginationType('default');
    }

    /**
     * Get either the specific max or the global setting.
     *
     * @return int|null
     */
    protected function paginateMaxCount(): ?int
    {
        return $this->directiveArgValue('maxCount')
            ?? config('lighthouse.paginate_max_count');
    }
}
