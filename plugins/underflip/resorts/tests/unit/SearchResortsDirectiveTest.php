<?php

namespace Tests\Unit\GraphQL\Directives;

use PHPUnit\Framework\TestCase;
use Mockery;
use Underflip\Resorts\GraphQL\Directives\SearchResorts;
use Underflip\Resorts\Classes\ElasticSearchService;
use Underflip\Resorts\Models\Resort;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use GraphQL\Type\Definition\ResolveInfo;

class SearchResortsTest extends TestCase
{
    protected $mockElasticSearchService;
    protected $directive;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockElasticSearchService = Mockery::mock(ElasticSearchService::class);
        $this->directive = new SearchResorts($this->mockElasticSearchService);
    }

    public function testInvoke()
    {
        $args = ['query' => 'test', 'page' => 1, 'perPage' => 10];
        $context = Mockery::mock(GraphQLContext::class);
        $resolveInfo = Mockery::mock(ResolveInfo::class);

        $mockSearchResults = [
            'hits' => [
                'total' => ['value' => 1],
                'hits' => [
                    ['_id' => 1]
                ]
            ]
        ];

        $this->mockElasticSearchService->shouldReceive('searchResorts')
            ->with($args['query'], 0, $args['perPage'])
            ->andReturn($mockSearchResults);

        $resort = new Resort(['id' => 1, 'title' => 'Test Resort']);
        Resort::shouldReceive('whereIn')->with('id', [1])->andReturnSelf();
        Resort::shouldReceive('get')->andReturn(collect([$resort]));

        $result = ($this->directive)(null, $args, $context, $resolveInfo);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('paginatorInfo', $result);
        $this->assertCount(1, $result['data']);
        $this->assertEquals($resort, $result['data'][0]);
        $this->assertEquals(1, $result['paginatorInfo']['total']);
    }

    public function testInvokeWithException()
    {
        $args = ['query' => 'test'];
        $context = Mockery::mock(GraphQLContext::class);
        $resolveInfo = Mockery::mock(ResolveInfo::class);

        $this->mockElasticSearchService->shouldReceive('searchResorts')
            ->andThrow(new \Exception('Test exception'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('An error occurred while searching for resorts.');

        ($this->directive)(null, $args, $context, $resolveInfo);
    }
}
