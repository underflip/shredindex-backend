<?php

namespace Underflip\Resorts\GraphQL\Directives;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Underflip\Resorts\Models\Resort;
use Underflip\Resorts\Models\Type;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class RheostatDirective
{
    const CACHE_TTL = 3600; // 1 hour cache
    const TICKERS = 100;

    public function __invoke($rootValue, array $args, GraphQLContext $context, ResolveInfo $resolveInfo)
    {
        try {
            $typeName = $args['typeName'];
            $cacheKey = "rheostat_data_{$typeName}";

            return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($typeName) {
                Log::info('Calculating Rheostat data', ['typeName' => $typeName]);

                $type = Type::where('name', $typeName)->first();

                if (!$type) {
                    Log::error("Type not found", ['typeName' => $typeName]);
                    throw new \Exception("Type '{$typeName}' not found");
                }

                Log::info("Type found", ['type' => $type->toArray()]);

                $isNumeric = $type->category === 'Underflip\\Resorts\\Models\\Numeric';
                $relation = $isNumeric ? 'numerics' : 'ratingScores';

                $resorts = Resort::with([$relation => function ($query) use ($type) {
                    $query->where('type_id', $type->id);
                }])->get();

                $values = $resorts->pluck($relation)->flatten()->pluck('value')->filter();

                Log::info("Values extracted", ['count' => $values->count(), 'sample' => $values->take(5)]);

                $min = $values->min() ?: 0;
                $max = $isNumeric ? ($type->max_value ?: $values->max() ?: 100) : 100;

                Log::info("Min and Max calculated", ['min' => $min, 'max' => $max]);

                $tickers = array_fill(0, self::TICKERS, 0);
                foreach ($values as $value) {
                    if ($value !== null) {
                        $index = min(floor(($value - $min) / ($max - $min) * (self::TICKERS - 1)), self::TICKERS - 1);
                        $tickers[$index]++;
                    }
                }

                Log::info("Tickers calculated", ['nonZeroCount' => count(array_filter($tickers))]);

                return [
                    'min' => $min,
                    'max' => $max,
                    'tickers' => $tickers,
                    'totalResorts' => $resorts->count(),
                    'type' => [
                        'name' => $type->name,
                        'category' => $type->category,
                        'max_value' => $type->max_value,
                    ],
                ];
            });
        } catch (\Exception $e) {
            Log::error('Rheostat error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
