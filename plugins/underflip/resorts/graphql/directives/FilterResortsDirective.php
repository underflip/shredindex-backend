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
use Underflip\Resorts\Models\Unit;
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
        return function (mixed $root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) {
            Log::info('$args', $args);
            // Setup builder
            $query = Resort::with(['numerics.type.unit', 'generics']);

            try {
                if (array_key_exists('filter', $args)) {
                    // Apply filters
                    $this->augmentForFilters($query, $args['filter']);
                }

                if (!array_key_exists('orderBy', $args)) {
                    $this->orderByType($query, ['type_name' => 'total_score', 'direction' => 'desc']);
                } else {
                    $this->orderByType($query, $args['orderBy']);
                }
            } catch (InvalidFilterOperatorException | MissingTypeException | InvalidOrderByTypeException $e) {
                reportGraphQLError($e->getMessage(), extensions: ['code' => $e->getCode()]);
            }
            // Pagination
            $pageArg = PaginationArgs::extractArgs(
                $args,
                $resolveInfo,
                $this->paginationType(),
                $this->paginateMaxCount()
            );
            $first = $pageArg->first;
            $page = $pageArg->page;

            // Ensure the unit is properly resolved
            $resorts = $query->paginate($first, ['*'], 'page', $page);

            $resorts->getCollection()->transform(function ($resort) {
                $resort->ratingScores = $this->resolveRatingScores($resort);
                $resort->highlights = $this->resolveHighlights($resort);
                $resort->lowlights = $this->resolveLowlights($resort);
                $resort->totalScore = $resort->total_score ? $resort->total_score->value : null;
                return $resort;
            });


            return $resorts;
        };
    }

    protected function resolveRatingScores($resort)
    {
        return $resort->ratingScores()->get()->map(function ($rating) {
            return [
                'name' => $rating->type->name,
                'title' => $rating->type->title,
                'type' => $rating->type,
                'value' => $rating->score,
            ];
        });
    }

    protected function resolveHighlights($resort)
    {
        return $resort->highlights->map(function ($rating) {
            return [
                'id' => $rating->id,
                'name' => $rating->type->name,
                'title' => $rating->type->title,
                'type' => $rating->type,
                'value' => $rating->score,
            ];
        });
    }

    protected function resolveLowlights($resort)
    {
        return $resort->lowlights->map(function ($rating) {
            return [
                'id' => $rating->type_id,
                'name' => $rating->type->name,
                'title' => $rating->type->title,
                'type' => $rating->type,
                'value' => $rating->score,
            ];
        });
    }


    /**
     * @param Builder $query
     * @param array $filters
     * @throws \Exception
     */
    public function augmentForFilters(Builder &$query, array $filters): void
    {

        /** @codeCoverageIgnoreStart */

        if (isset($filters['locationType'])) {
            $locationFilter = $filters['locationType'];

            if (isset($locationFilter['countryId'])) {
                $countryIds = (array) $locationFilter['countryId'];
                $query->whereHas('location.country', function (Builder $query) use ($countryIds) {
                    $query->whereIn('code', $countryIds);
                });
            }

            if (isset($locationFilter['continentId'])) {
                $continentIds = (array) $locationFilter['continentId'];
                $query->whereHas('location.continent', function (Builder $query) use ($continentIds) {
                    $query->whereIn('id', $continentIds);
                });
            }
        }

        /** @codeCoverageIgnoreEnd */

        foreach ($this->getFilterableScopes() as $scope) {
            if (!array_key_exists($scope['class'], Type::getCategories())) {
                // Throw a helpful message
                /** @codeCoverageIgnoreStart */
                throw new \Exception(sprintf(
                    'Can only filter relations that exist as a category of "%s"',
                    Type::class
                ));
                /** @codeCoverageIgnoreEnd */
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
        $typeName = $orderBy['type_name'];
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

        // Order by values of that metric, including resorts without scores at the end
        $query
        ->select('underflip_resorts_resorts.*')
        ->leftJoinSub(function ($subquery) use ($type, $column) {
            $subquery
                ->from(app($type->category)->getTable() . ' as comparisons')
                ->select('resort_id', \DB::raw("AVG($column) as avg_value"))
                ->where('type_id', '=', $type->id)
                ->groupBy('resort_id');
        }, 'comparisons', 'comparisons.resort_id', '=', 'underflip_resorts_resorts.id')
        ->orderByRaw("CASE WHEN comparisons.avg_value IS NULL THEN 1 ELSE 0 END, comparisons.avg_value " . $direction)
        ->orderBy('underflip_resorts_resorts.id', 'asc');
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
