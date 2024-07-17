<?php

    namespace Underflip\Resorts\GraphQL\Directives;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Underflip\Resorts\Models\Resort;
use Underflip\Resorts\Classes\ElasticSearchService;

class SearchResorts
{
    protected $elasticSearchService;

    public function __construct(ElasticSearchService $elasticSearchService)
    {
        $this->elasticSearchService = $elasticSearchService;
    }

    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        try {
            $query = $args['query'];
            $page = $args['page'] ?? 1;
            $perPage = $args['perPage'] ?? 10;

            $from = ($page - 1) * $perPage;

            \Log::info('Searching for resorts', ['query' => $query, 'from' => $from, 'size' => $perPage]);

            $results = $this->elasticSearchService->searchResorts($query, $from, $perPage);

            \Log::info('Search results', ['hits' => $results['hits']['total']['value']]);

            $resortIds = collect($results['hits']['hits'])->pluck('_id')->toArray();

            \Log::info('Resort IDs', ['ids' => $resortIds]);

            $resorts = Resort::whereIn('id', $resortIds)->get();

            \Log::info('Fetched resorts', ['count' => $resorts->count()]);

            $total = $results['hits']['total']['value'];
            $lastPage = ceil($total / $perPage);

            return [
                'data' => $resorts,
                'paginatorInfo' => [
                    'currentPage' => $page,
                    'perPage' => $perPage,
                    'total' => $total,
                    'lastPage' => $lastPage,
                ],
            ];
        } catch (\Exception $e) {
            \Log::error('Search error: ' . $e->getMessage());
            throw new \Exception('An error occurred while searching for resorts.');
        }
    }
}
