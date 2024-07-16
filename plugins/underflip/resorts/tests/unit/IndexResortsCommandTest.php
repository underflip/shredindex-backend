<?php

namespace Tests\Unit\Console;

use PHPUnit\Framework\TestCase;
use Mockery;
use Underflip\Resorts\Console\IndexResorts;
use Underflip\Resorts\Classes\ElasticSearchService;
use Underflip\Resorts\Models\Resort;
use Illuminate\Support\Facades\Artisan;

class IndexResortsTest extends TestCase
{
    protected $mockElasticSearchService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockElasticSearchService = Mockery::mock(ElasticSearchService::class);
        $this->app->instance(ElasticSearchService::class, $this->mockElasticSearchService);
    }

    public function testHandle()
    {
        $mockClient = Mockery::mock('Elastic\Elasticsearch\Client');
        $this->mockElasticSearchService->shouldReceive('getClient')->andReturn($mockClient);

        $resort = new Resort(['id' => 1, 'title' => 'Test Resort', 'description' => 'Test Description']);
        $resort->location = (object)[
            'continent' => (object)['name' => 'Test Continent'],
            'country' => (object)['name' => 'Test Country'],
            'state' => (object)['name' => 'Test State'],
            'city' => 'Test City'
        ];

        Resort::shouldReceive('with')->andReturnSelf();
        Resort::shouldReceive('get')->andReturn(collect([$resort]));

        $mockClient->shouldReceive('index')
            ->once()
            ->withArgs(function ($params) use ($resort) {
                return $params['index'] === 'resorts' &&
                       $params['id'] === $resort->id &&
                       $params['body']['title'] === $resort->title;
            });

        Artisan::call('resorts:index');

        $this->assertTrue(true); // If we get here without exceptions, the test passes
    }
}
