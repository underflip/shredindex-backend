<?php

namespace Underflip\Resorts\Classes;

use Underflip\Resorts\Classes\ElasticSearchService;

class GetAllResortUrlSegments
{
    protected $elasticSearchService;

    public function __construct(ElasticSearchService $elasticSearchService)
    {
        $this->elasticSearchService = $elasticSearchService;
    }

    public function __invoke($rootValue, array $args, $context, $resolveInfo)
    {
        try {
            \Log::info('Starting getAllResortUrlSegments resolver');
            $segments = $this->elasticSearchService->getAllResortUrlSegments();
            \Log::info('Retrieved URL segments in resolver', [
                'count' => count($segments),
                'first_few' => array_slice($segments, 0, 5)
            ]);
            return $segments;
        } catch (\Exception $e) {
            \Log::error('Error in getAllResortUrlSegments resolver: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return [];
        }
    }
}
