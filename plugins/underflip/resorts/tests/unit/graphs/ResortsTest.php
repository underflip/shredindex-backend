<?php

namespace Underflip\Resorts\Tests\Graphs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Model;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Underflip\Resorts\Console\RefreshTotalScore;
use Underflip\Resorts\Models\Generic;
use Underflip\Resorts\Models\Numeric;
use Underflip\Resorts\Models\Rating;
use Underflip\Resorts\Models\Resort;
use Underflip\Resorts\Models\Type;
use Underflip\Resorts\Tests\BaseTestCase;

/**
 * {@see \Underflip\Resorts\GraphQL\Directives\FilterResortsDirective}
 */
class ResortsTest extends BaseTestCase
{
    use MakesGraphQLRequests;
    use RefreshDatabase;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $totalShredScoreId = Type::where('name', 'digital_nomad_score')->first()->id;
        $avgAnnualSnowfallId = Type::where('name', 'average_annual_snowfall')->first()->id;
        $snowMakingId = Type::where('name', 'snow_making')->first()->id;

        Model::unguard();

        // Create resort: Foo
        $fooResort = Resort::create([
            'title' => 'Foo Resort',
            'url_segment' => 'foo-resort',
            'affiliate_url' => 'foo-resort',
            'description' => 'Foo Description',
        ]);

        Rating::create([
            'value' => 100,
            'type_id' => $totalShredScoreId,
            'resort_id' => $fooResort->id,
        ]);

        Numeric::create([
            'value' => 10,
            'type_id' => $avgAnnualSnowfallId,
            'max_value' => 100,
            'resort_id' => $fooResort->id,
        ]);

        Generic::create([
            'value' => 'yes',
            'type_id' => $snowMakingId,
            'resort_id' => $fooResort->id,
        ]);

        // Create resort: Bar
        $barResort = Resort::create([
            'title' => 'Bar Resort',
            'url_segment' => 'bar-resort',
            'affiliate_url' => 'bar-resort',
            'description' => 'Bar Description',
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
            'max_value' => 100,
        ]);

        // Create resort: Bin
        $binResort = Resort::create([
            'title' => 'Bin Resort',
            'url_segment' => 'bin-resort',
            'affiliate_url' => 'bin-resort',
            'description' => 'Bin Description',
        ]);

        Rating::create([
            'value' => 25,
            'type_id' => $totalShredScoreId,
            'resort_id' => $binResort->id,
        ]);

        Numeric::create([
            'value' => 2.5,
            'type_id' => $avgAnnualSnowfallId,
            'max_value' => 100,
            'resort_id' => $binResort->id,
        ]);

        Model::reguard();
    }

    /**
     * @return void
     */
    public function testResort(): void
    {
        $response = $this->graphQL('
            {
                 resort(id: 1) {
                    id
                    title
                    url_segment
                    affiliate_url
                    description
                    ratings {
                        id
                        name
                        title
                        value
                    }
                    numerics {
                        id
                        name
                        title
                        value
                    }
                    generics {
                        id
                        name
                        title
                        value
                    }
                }
            }
        ');

        $this->assertSame(
            'Foo Resort',
            $response->json('data.resort.title'),
            'Should graph resort'
        );

        $this->assertSame(
            'Foo Description',
            $response->json('data.resort.description'),
            'Should graph description'
        );

        $this->assertCount(
            1,
            $response->json('data.resort.ratings'),
            'Should graph ratings'
        );

        $this->assertSame(
            [
                'digital_nomad_score'
            ],
            $response->json('data.resort.ratings.*.name'),
            'Should graph ratings with expected output'
        );

        $this->assertCount(
            1,
            $response->json('data.resort.numerics'),
            'Should graph numerics'
        );

        $this->assertSame(
            [
                'average_annual_snowfall'
            ],
            $response->json('data.resort.numerics.*.name'),
            'Should graph numerics with expected output'
        );

        $this->assertCount(
            1,
            $response->json('data.resort.generics'),
            'Should graph generics'
        );

        $this->assertSame(
            [
                'snow_making'
            ],
            $response->json('data.resort.generics.*.name'),
            'Should graph generics with expected output'
        );
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
                        type_name: "shred_score"
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
                            type_name: "digital_nomad_score"
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
    public function testInvalidOperator(): void
    {
        $response = $this->graphQL('
            {
                 resorts(
                    first: 10
                    filter: [
                        {
                            type_name: "snow_making"
                            operator: ">",
                            value: "1"
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

        $debugMessages = $response->json('errors.*.debugMessage');

        $this->assertStringContainsString(
            'is not a valid operator',
            array_shift($debugMessages),
            'Should throw an invalid operator validation message'
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
                        type_name: "digital_nomad_score",
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
