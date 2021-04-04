<?php

namespace Underflip\Resorts\Tests\GraphQL\Directives;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Model;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Underflip\Resorts\Models\Numeric;
use Underflip\Resorts\Models\Rating;
use Underflip\Resorts\Models\Resort;
use Underflip\Resorts\Models\Stat;
use Underflip\Resorts\Models\Type;
use Underflip\Resorts\Tests\BaseTestCase;

/**
 * {@see \Underflip\Resorts\GraphQL\Directives\FilterResortsDirective}
 */
class FilterResortsDirectiveTest extends BaseTestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $totalShredScoreId = Type::where('name', 'total_shred_score')->first()->id;
        $avgAnnualSnowfallId = Type::where('name', 'average_annual_snowfall')->first()->id;

        Model::unguard();

        // Create resort: Foo
        $fooResort = Resort::create([
            'title' => 'Foo Resort',
            'url_segment' => 'foo-resort'
        ]);

        Rating::create([
            'value' => 100,
            'type_id' => $totalShredScoreId,
            'resort_id' => $fooResort->id,
        ]);

        Numeric::create([
            'value' => 10,
            'type_id' => $avgAnnualSnowfallId,
            'resort_id' => $fooResort->id,
        ]);

        // Create resort: Bar
        $barResort = Resort::create([
            'title' => 'Bar Resort',
            'url_segment' => 'bar-resort'
        ]);

        Rating::create([
            'value' => 50,
            'type_id' => $totalShredScoreId,
            'resort_id' => $barResort->id,
        ]);

        Numeric::create([
            'value' => 5,
            'type_id' => $avgAnnualSnowfallId,
            'resort_id' => $barResort->id,
        ]);

        // Create resort: Bin
        $binResort = Resort::create([
            'title' => 'Bin Resort',
            'url_segment' => 'bin-resort'
        ]);

        Rating::create([
            'value' => 25,
            'type_id' => $totalShredScoreId,
            'resort_id' => $binResort->id,
        ]);

        Numeric::create([
            'value' => 2.5,
            'type_id' => $avgAnnualSnowfallId,
            'resort_id' => $binResort->id,
        ]);

        Model::reguard();
    }

    /**
     * @return void
     */
    public function testResorts(): void
    {
        $response = $this->graphQL('
            {
                 resorts(first: 10) {
                    data {
                        id
                        title
                        url_segment
                    }
                    paginatorInfo {
                        currentPage
                        lastPage
                    }
                 }
            }
        ');

        $this->assertCount(
            3,
            $response->json("data.resorts.data"),
            'Should return all resorts without any filters applied'
        );
    }

    /**
     * @return void
     */
    public function testResortsByPage(): void
    {
        $responsePageOne = $this->graphQL('
            {
                 resorts(first: 2) {
                    data {
                        id
                        title
                        url_segment
                    }
                    paginatorInfo {
                        currentPage
                        lastPage
                    }
                 }
            }
        ');

        $responsePageTwo = $this->graphQL('
            {
                 resorts(first: 2, page: 2) {
                    data {
                        id
                        title
                        url_segment
                    }
                    paginatorInfo {
                        currentPage
                        lastPage
                    }
                 }
            }
        ');

        $this->assertSame(
            [
                'foo-resort',
                'bar-resort',
            ],
            $responsePageOne->json("data.resorts.data.*.url_segment"),
            'Should return first page of resorts'
        );

        $this->assertSame(
            2,
            $responsePageOne->json("data.resorts.paginatorInfo.lastPage"),
            'Should provide total number of pages'
        );

        $this->assertSame(
            [
                'bin-resort'
            ],
            $responsePageTwo->json("data.resorts.data.*.url_segment"),
            'Should return second page of resorts'
        );

        $this->assertSame(
            2,
            $responsePageTwo->json("data.resorts.paginatorInfo.currentPage"),
            'Should progress current page to match query'
        );
    }

    /**
     * @return void
     */
    public function testResortsByFilter(): void
    {
        $responseByShredScore = $this->graphQL('
            {
                 resorts(
                    first: 10
                    filter: [{
                        type_name: "total_shred_score"
                        operator: ">",
                        value: "75"
                    }]
                 ) {
                    data {
                        id
                        title
                        url_segment
                    }
                    paginatorInfo {
                        currentPage
                        lastPage
                    }
                 }
            }
        ');

        $responseBySnowFall = $this->graphQL('
            {
                 resorts(
                    first: 10
                    filter: [{
                        type_name: "average_annual_snowfall"
                        operator: ">",
                        value: "3"
                    }]
                 ) {
                    data {
                        id
                        title
                        url_segment
                    }
                    paginatorInfo {
                        currentPage
                        lastPage
                    }
                 }
            }
        ');

        $responseByScoreAndSnowFall = $this->graphQL('
            {
                 resorts(
                    first: 10
                    filter: [
                        {
                            type_name: "total_shred_score"
                            operator: ">",
                            value: "25"
                        },
                        {
                            type_name: "average_annual_snowfall"
                            operator: "<",
                            value: "7.5"
                        }
                    ]
                 ) {
                    data {
                        id
                        title
                        url_segment
                    }
                    paginatorInfo {
                        currentPage
                        lastPage
                    }
                 }
            }
        ');

        $this->assertSame(
            [
                'foo-resort',
            ],
            $responseByShredScore->json("data.resorts.data.*.url_segment"),
            'Should return resorts with shred score above 75'
        );

        $this->assertSame(
            [
                'foo-resort',
                'bar-resort',
            ],
            $responseBySnowFall->json("data.resorts.data.*.url_segment"),
            'Should return resorts with snowfall above 3m'
        );

        $this->assertSame(
            [
                'bar-resort',
            ],
            $responseByScoreAndSnowFall->json("data.resorts.data.*.url_segment"),
            'Should return resorts with shred score above 25 and snowfall below 7.5m'
        );
    }

    /**
     * @return void
     */
    public function testResortsWithOrderBy(): void
    {
        $response = $this->graphQL('
            {
                 resorts(
                    first: 10
                    orderBy: {
                        type_name: "total_shred_score",
                        direction: "asc"
                    }
                 ) {
                    data {
                        id
                        title
                        url_segment
                    }
                    paginatorInfo {
                        currentPage
                        lastPage
                    }
                 }
            }
        ');

        $this->assertSame(
            [
                'bin-resort',
                'bar-resort',
                'foo-resort',
            ],
            $response->json("data.resorts.data.*.url_segment"),
            'Should return resorts ordered by score, descending'
        );
    }
}
