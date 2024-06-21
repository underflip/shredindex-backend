<?php

namespace Underflip\Resorts\Tests\Graphs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Model;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Underflip\Resorts\Console\RefreshTotalScore;
use Underflip\Resorts\Models\Generic;
use Underflip\Resorts\Models\Location;
use Underflip\Resorts\Models\Numeric;
use Underflip\Resorts\Models\Rating;
use Underflip\Resorts\Models\Resort;
use Underflip\Resorts\Models\Type;
use Underflip\Resorts\Tests\BaseTestCase;
use Mockery;

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

        $totalShredScoreId = Type::where('name', 'total_score')->first()->id;
        $avgAnnualSnowfallId = Type::where('name', 'average_annual_snowfall')->first()->id;
        $snowMakingId = Type::where('name', 'snow_making')->first()->id;
        $verticalDropId = Type::where('name', 'vertical_drop')->first()->id;
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
            'resort_id' => $fooResort->id,
        ]);
        Numeric::create([
            'value' => 500,
            'type_id' => $verticalDropId,
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
        ]);
        Numeric::create([
            'value' => 250,
            'type_id' => $verticalDropId,
            'resort_id' => $barResort->id,
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
            'resort_id' => $binResort->id,
        ]);
        Numeric::create([
            'value' => 150,
            'type_id' => $verticalDropId,
            'resort_id' => $binResort->id,
        ]);

        // Create resort: Baz
        $bazResort = Resort::create([
            'title' => 'Baz Resort',
            'url_segment' => 'baz-resort',
            'affiliate_url' => 'baz-resort',
            'description' => 'Baz Description',
        ]);
        Rating::create([
            'value' => 75,
            'type_id' => $totalShredScoreId,
            'resort_id' => $bazResort->id,
        ]);
        Numeric::create([
            'value' => 7.5,
            'type_id' => $avgAnnualSnowfallId,
            'resort_id' => $bazResort->id,
        ]);
        Numeric::create([
            'value' => 300,
            'type_id' => $verticalDropId,
            'resort_id' => $bazResort->id,
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
                'total_score'
            ],
            $response->json('data.resort.ratings.*.name'),
            'Should graph ratings with expected output'
        );
        $this->assertCount(
            2,
            $response->json('data.resort.numerics'),
            'Should graph numerics'
        );
        $this->assertSame(
            [
                'average_annual_snowfall',
                'vertical_drop'
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
            4,
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
                'baz-resort',
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
                'bar-resort',
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
                    filter: {groupedType: [{
                        type_name: "total_score",
                        operator: ">",
                        value: "75"
                    }]}
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
                    filter: {groupedType: [{
                        type_name: "average_annual_snowfall",
                        operator: ">",
                        value: "3"
                    }]}
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
        $responseByVerticalDrop = $this->graphQL('
            {
                 resorts(
                    first: 10
                    filter: {groupedType: [{
                        type_name: "vertical_drop",
                        operator: ">",
                        value: "350"
                    }]}
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
                    filter: {
                        groupedType: [
                            {
                                type_name: "total_score",
                                operator: ">",
                                value: "25"
                            },
                            {
                                type_name: "average_annual_snowfall",
                                operator: "<",
                                value: "7.5"
                            }
                        ]
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
                'foo-resort'
            ],
            $responseByShredScore->json("data.resorts.data.*.url_segment"),
            'Should return resorts with shred score above 75'
        );
        $this->assertSame(
            [
                'foo-resort',
                'baz-resort',
                'bar-resort',
            ],
            $responseBySnowFall->json("data.resorts.data.*.url_segment"),
            'Should return resorts with snowfall above 3m'
        );
        $this->assertSame(
            [
                'foo-resort'
            ],
            $responseByVerticalDrop->json("data.resorts.data.*.url_segment"),
            'Should return resorts with a vertical drop above 350m'
        );
        $this->assertSame(
            [
                'bar-resort',
            ],
            $responseByScoreAndSnowFall->json("data.resorts.data.*.url_segment"),
            'Should return resorts with total score above 25 and snowfall below 7.5m'
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
                    filter: {
                    groupedType: [{
                        type_name: "snow_making"
                        operator: ">",
                        value: "1"
                    }]}
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
        $debugMessages = $response->json('errors.*.extensions.debugMessage');
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
                        type_name: "total_score",
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
                'baz-resort',
                'foo-resort'
            ],
            $response->json("data.resorts.data.*.url_segment"),
            'Should return resorts ordered by score, ascending'
        );

        $responseDesc = $this->graphQL('
            {
                 resorts(
                    first: 10
                    orderBy: {
                        type_name: "total_score",
                        direction: "desc"
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
                'foo-resort',
                'baz-resort',
                'bar-resort',
                'bin-resort',
            ],
            $responseDesc->json("data.resorts.data.*.url_segment"),
            'Should return resorts ordered by score, descending'
        );
    }

    /**
     * @return void
     */
    public function testResortsWithOrderByVerticalDrop(): void
    {
        $responseAsc = $this->graphQL('
            {
                 resorts(
                    first: 10
                    orderBy: {
                        type_name: "vertical_drop",
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
                'baz-resort',
                'foo-resort',
            ],
            $responseAsc->json("data.resorts.data.*.url_segment"),
            'Should return resorts ordered by vertical drop, ascending'
        );
        $responseDesc = $this->graphQL('
            {
                 resorts(
                    first: 10
                    orderBy: {
                        type_name: "vertical_drop",
                        direction: "desc"
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
                'foo-resort',
                'baz-resort',
                'bar-resort',
                'bin-resort'
            ],
            $responseDesc->json("data.resorts.data.*.url_segment"),
            'Should return resorts ordered by vertical drop, descending'
        );
    }

    /**
     * @return void
     */
    public function testResortsWithOrderByWithInvalidTypeName(): void
    {
        $response = $this->graphQL('
            {
                 resorts(
                    first: 10
                    orderBy: {
                        type_name: "non-existent-type",
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
        $this->assertArrayHasKey(
            'errors',
            $response->json(),
            'Should return an error if ordering by invalid type name'
        );
        // Adjust the assertion to match the specific exception message
        $this->assertStringContainsString(
            'A Type does not exist with that name',
            $response->json('errors.*.extensions.debugMessage')[0],
            'Error message should contain the expected message'
        );
    }

    /**
     * @return void
     */
    public function testResortsWithOrderByByNonCategorizedType(): void
    {
        $response = $this->graphQL('
            {
                 resorts(
                    first: 10
                    orderBy: {
                        type_name: "created_at",
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
        $this->assertArrayHasKey(
            'errors',
            $response->json(),
            'Should return an error if ordering by non categorized type name'
        );
        // Adjust the assertion to match the specific exception message
        $this->assertStringContainsString(
            'Type does not have a category',
            $response->json('errors.*.extensions.debugMessage')[0],
            'Error message should contain the expected message'
        );
    }

    /**
     * @return void
     */
    public function testCreateResort(): void
    {
        $resort = Resort::create([
            'title' => 'Test Resort',
            'url_segment' => 'test-resort',
            'affiliate_url' => 'test-resort-affiliate',
            'description' => 'Test Description',
        ]);

        $this->assertDatabaseHas('underflip_resorts_resorts', [
            'title' => 'Test Resort',
            'url_segment' => 'test-resort',
            'affiliate_url' => 'test-resort-affiliate',
            'description' => 'Test Description',
        ]);
    }
}
