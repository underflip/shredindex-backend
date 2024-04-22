<?php

namespace Underflip\Resorts\GraphQL\Directives;

use Closure;
use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\Log;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use Nuwave\Lighthouse\Pagination\PaginationArgs;
use Nuwave\Lighthouse\Pagination\PaginationManipulator;
use Nuwave\Lighthouse\Pagination\PaginationType;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Schema\Directives\RelationDirectiveHelpers;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
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
    use RelationDirectiveHelpers;

    /** @var array<string, mixed> */
    protected array $lighthouseConfig = [];
    /**
     * @throws \Exception
     */
    public function __construct(
        protected ConnectionResolverInterface $database,
        ConfigRepository $configRepository
    ) {
        //$this->lighthouseConfig = $configRepository->get('lighthouse');
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


    public function manipulateFieldDefinition(
        DocumentAST &$documentAST,
        FieldDefinitionNode &$fieldDefinition,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode &$parentType,
    ): void {
        $paginate = new PaginationManipulator($documentAST);
        $paginate->transformToPaginatedField(
            $this->paginationType(),
            $fieldDefinition,
            $parentType,
            20,
            $this->paginateMaxCount()
        );
    }

    protected function paginationMaxCount(): ?int
    {
        return $this->directiveArgValue('maxCount', $this->lighthouseConfig['pagination']['max_count']);
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


    public function resolveField(FieldValue $fieldValue): callable
    {
        return function (mixed $root, array $args,GraphQLContext $context, ResolveInfo $resolveInfo) {
            Log::info('$args', $args);
            // Setup builder
            $query = Resort::with(['ratings', 'numerics', 'generics']);

            if (array_key_exists('filter', $args)) {
                // Apply filters
                $this->augmentForFilters($query, $args['filter']);
            }

            if (!array_key_exists('orderBy', $args)) {
                $this->orderByType($query, ['type_name' => 'total_score', 'direction' => 'desc']); // Assuming 'total_score' is the type_name
            } else {
                $this->orderByType($query, $args['orderBy']);
            }
            // Pagination
            $pageArg = PaginationArgs::extractArgs(
                $args,
                $resolveInfo,
                $this->paginationType(),
                $this->paginateMaxCount() //this line 153
            );
            $first = $pageArg->first;
            $page = $pageArg->page;

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

                    if (isset($filters['locationType'])) {
                        $locationFilter = $filters['locationType'];

                        if (isset($locationFilter['countryId'])) {
                            $query->whereHas('location.country', function (Builder $query) use ($locationFilter) {
                                $query->whereIn('id', $locationFilter['countryId']);  // used whereIn instead of where
                            });
                        }

        // A temporary way of filtering by continents
                        $regions = [
                            'worldwide' => [],
                            'africa' => [5, 6, 7, 8, 11, 15, 16, 17, 18, 19, 21, 22, 23, 24, 25, 27, 32, 33, 35, 36, 38, 39, 43, 46, 49, 51, 52, 53, 54, 69, 75, 76, 77, 90, 95, 96, 97, 99, 102, 103, 104, 105, 117, 118, 119, 122, 126, 135, 136, 161, 162, 164, 166, 172, 173, 174, 175, 191, 196, 197, 198, 201, 220, 234, 236, 239, 248, 249],
                            'asia' => [5, 14, 20, 40, 41, 42, 45, 47, 48, 55, 56, 57, 58, 59, 60, 61, 62, 67, 68, 70, 71, 74, 78, 79, 80, 83, 84, 85, 86, 87, 89, 91, 92, 93, 94, 98, 100, 101, 108, 109, 110, 112, 113, 114, 115, 116, 120, 121, 123, 124, 125, 127, 132, 133, 134, 137, 138, 139, 140, 141, 150, 155, 157, 159, 160, 163, 165, 167, 168, 169, 170, 171, 176, 181, 182, 184, 185, 187, 189, 190, 192, 193, 194, 195, 199, 200, 202, 203, 204, 206, 207, 209, 210, 215, 217, 218, 221, 222, 223, 225, 227, 228, 229, 230, 231, 232, 233, 237, 240, 241, 247],
                            'europe' => [3, 13, 26, 28, 29, 30, 31, 34, 37, 44, 50, 63, 64, 65, 66, 72, 73, 81, 82, 88, 106, 107, 111, 128, 129, 130, 131, 142, 143, 144, 145, 146, 147, 148, 149, 151, 152, 153, 154, 156, 158, 177, 178, 179, 180, 183, 186, 188, 205, 208, 211, 212, 213, 214, 216, 219, 224, 226, 235, 238, 242, 243, 244, 245, 246],
                            'northAmerica' => [2, 4, 9, 10, 12, 50, 72, 111, 128, 154, 177, 205, 208, 213, 214, 219, 224],
                            'oceania' => [1, 6, 14, 24, 60, 66, 81, 102, 108, 115, 130, 153, 172, 185, 218, 227],
                            'southAmerica' => [20, 30, 45, 47, 48, 62, 74, 78, 79, 83, 84, 85, 86, 89, 91, 92, 93, 94, 98, 100, 101, 116, 121, 123, 133, 137, 138, 143, 144, 150, 157, 159, 163, 165, 167, 168, 169, 170, 171, 176, 182, 187, 190, 192, 193, 194, 195, 199, 203, 206, 209, 210, 215, 221, 223, 231, 232, 240, 241]
                        ];

                        if(isset($locationFilter['continentId'])) {
                            // Assuming 'continentId' could be an array, we'll take the first element for simplicity.
                            // You might need a different logic if multiple continentIds can be handled at once.
                            $continentIds = $locationFilter['continentId'];
                            $name = is_array($continentIds) ? $continentIds[0] : $continentIds;

                            // Ensure $name is a valid string or integer key for the $regions array
                            if (is_string($name) || is_int($name)) {
                                if(array_key_exists($name, $regions)) {
                                    if( $name !== 'worldwide' ) {
                                        // Assuming $name now correctly holds a single continent ID that exists in $regions
                                            $query->whereHas('location.country', function($query) use ($regions, $name) {
                                                // Use $regions[$name] directly expecting it to be an array of IDs
                                                $query->whereIn('id', $regions[$name]);
                                            });
                                    } else {
                                        $query->Raw(1===1);
                                    }
                                }

                            }
                        }

                        if (isset($locationFilter['city'])) {
                            $query->whereHas('location', function (Builder $query) use ($locationFilter) {
                                $query->where('city', 'like', '%'.$locationFilter['city'].'%');
                            });
                        }

                        if (isset($locationFilter['zip'])) {
                            $query->whereHas('location', function (Builder $query) use ($locationFilter) {
                                $query->where('zip', 'like', '%'.$locationFilter['zip'].'%');
                            });
                        }
                    }

            foreach ($this->getFilterableScopes() as $scope) {
                if (!array_key_exists($scope['class'], Type::getCategories())) {
                    // Throw a helpful message
                    throw new \Exception(sprintf(
                        'Can only filter relations that exist as a category of "%s"',
                        Type::class
                    ));
                }

                foreach ($filters['groupedType'] as $filter) {
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
        // Join with a subquery that aggregates comparison values
        ->joinSub(function ($subquery) use ($type, $column) {$subquery
        ->from(app($type->category)
        ->getTable() . ' as comparisons')
        ->select('resort_id', \DB::raw("AVG($column) as avg_value"))
        ->where('type_id', '=', $type->id)
        ->groupBy('resort_id');    }, 'comparisons', 'comparisons.resort_id', '=', 'underflip_resorts_resorts.id')
        // Order by the aggregated value
        ->orderBy('comparisons.avg_value', $orderBy['direction'] ?: 'asc');
    }

    /**
     * @return PaginationType
     */
    protected function paginationType(): PaginationType
    {
        return new PaginationType(PaginationType::PAGINATOR);
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
