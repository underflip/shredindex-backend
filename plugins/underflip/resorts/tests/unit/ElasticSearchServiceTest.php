<?php

namespace Tests\Unit\Classes;

use Tests\TestCase;
use Mockery;
use Underflip\Resorts\Classes\ElasticSearchService;
use Elastic\Elasticsearch\Client;

class ElasticSearchServiceTest extends TestCase
{
    protected $mockClient;
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockClient = Mockery::mock(Client::class);
        $this->service = new ElasticSearchService();
        $this->service->setClient($this->mockClient);
    }

    public function testSearchResorts()
    {
        $query = 'test resort';
        $from = 0;
        $size = 10;

        $mockResponse = [
            'hits' => [
                'total' => ['value' => 1],
                'hits' => [
                    ['_id' => 1, '_source' => ['title' => 'Test Resort']]
                ]
            ]
        ];

        $this->mockClient->shouldReceive('search')
            ->once()
            ->withArgs(function ($params) use ($query, $from, $size) {
                return $params['index'] === 'resorts' &&
                       $params['body']['from'] === $from &&
                       $params['body']['size'] === $size &&
                       $params['body']['query']['bool']['should'][0]['match_phrase_prefix']['title']['query'] === $query;
            })
            ->andReturn($mockResponse);

        $result = $this->service->searchResorts($query, $from, $size);

        $this->assertEquals($mockResponse, $result);
    }

    public function testSearchResortsException()
    {
        $this->mockClient->shouldReceive('search')
            ->andThrow(new \Exception('Test exception'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('An error occurred while searching for resorts: Test exception');

        $this->service->searchResorts('test', 0, 10);
    }
}
