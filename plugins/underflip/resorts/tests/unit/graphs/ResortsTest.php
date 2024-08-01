<?php

namespace Underflip\Resorts\Tests\Graphs;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Model;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Underflip\Resorts\Console\RefreshTotalScore;
use Underflip\Resorts\GraphQL\Directives\SearchResorts;

use Underflip\Resorts\Classes\ElasticSearchService;
use Underflip\Resorts\Console\IndexResorts;
use RainLab\Location\Models\Country;
use Underflip\Resorts\Models\Generic;
use Underflip\Resorts\Models\Location;
use Underflip\Resorts\Models\Numeric;
use Underflip\Resorts\Models\Rating;
use Underflip\Resorts\Models\Resort;
use Underflip\Resorts\Models\Type;
use Underflip\Resorts\Tests\BaseTestCase;
use RainLab\User\Models\User;
use Mockery;
use DB;


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

        $this->loadPlugin('RainLab.User');
        // Migrate core modules
        $this->migrateModules();

        // Migrate the blog plugin
        $this->migratePlugin('RainLab.User');

        $totalShredScoreId = Type::where('name', 'total_score')->first()->id;
        $avgAnnualSnowfallId = Type::where('name', 'average_annual_snowfall')->first()->id;
        $snowMakingId = Type::where('name', 'snow_making')->first()->id;
        $verticalDropId = Type::where('name', 'vertical_drop')->first()->id;
        Model::unguard();

        // Create test users
        $user1 = User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'username' => 'johndoe',
            'activated_at' => now(),
        ]);

        $user2 = User::create([
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
            'password' => bcrypt('password'),
            'username' => 'janesmith',
            'activated_at' => now(),
        ]);

        // Create resort: Foo
        $fooResort = Resort::create([
            'title' => 'Foo Resort',
            'url_segment' => 'foo-resort',
            'affiliate_url' => 'foo-resort',
            'description' => 'Foo Description',
        ]);

        Location::create([
            'resort_id' => $fooResort->id,
            'continent_id' => 1,
            'country_id' => 1,
            'state_id' => 1,
            'latitude' => 1,
            'longitude' => 1,
            'vicinity' => 'Unknown vicinity',
            'city' => 'TestCity',
            'zip' => '12345',
            'address' => 'Unknown address'
        ]);

        Rating::create([
            'value' => 90,
            'type_id' => $avgAnnualSnowfallId,
            'resort_id' => $fooResort->id,
            'user_id' => $user1->id,
        ]);
        Rating::create([
            'value' => 100,
            'type_id' => $avgAnnualSnowfallId,
            'resort_id' => $fooResort->id,
            'user_id' => $user2->id,
        ]);
        Rating::create([
            'value' => 100,
            'type_id' => $totalShredScoreId,
            'resort_id' => $fooResort->id,
            'user_id' => $user2->id,
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
            'user_id' => $user1->id,
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
            'user_id' => $user1->id,
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
            'user_id' => $user1->id,
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

        $fooResort->updateTotalScore();
        $barResort->updateTotalScore();
        $binResort->updateTotalScore();
        $bazResort->updateTotalScore();

        Model::reguard();
    }

    public function testContinent(): void
    {
        $resort = Resort::first();
        $this->assertNotEmpty($resort->continent());
    }
    public function testCmsTotalScoreAttribute(): void
    {
        $this->assertNotEmpty( Resort::first()->getCmsTotalScoreAttribute());
    }


    public function testSearchInElasticsearch(): void
    {
        // $response = Resort::searchInElasticsearch("resort");
        // $this->assertNotEmpty($response->asArray()['took'] );
        $esClient = new ElasticSearchService();
        $searchResorts = new SearchResorts($esClient);
        $this->assertIsObject($searchResorts);

        $esIndex = new IndexResorts();
        // $esIndex->handle();
        $this->assertClassHasAttribute('signature', IndexResorts::class);
        $esResult = $esClient->searchResorts('resorts');
        $this->assertNotEmpty($esResult->asArray()['took'] );

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
                    ratingScores {
                        id
                        name
                        title
                        value
                        type {
                            id
                            name
                        }
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

        // Log the response for debugging
        \Log::info('Resort Response:', $response->json('data.resort'));

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
            2,
            $response->json('data.resort.ratingScores'),
            'Should graph two rating scores'
        );

        $ratingScores = $response->json('data.resort.ratingScores');
        $ratingValues = array_column($ratingScores, 'value');
        $ratingTypes = array_column(array_column($ratingScores, 'type'), 'name');

        // Check for the presence of average_annual_snowfall rating
        $snowfallRating = array_values(array_filter($ratingScores, function($score) {
            return $score['type']['name'] === 'average_annual_snowfall';
        }))[0] ?? null;

        $this->assertNotNull($snowfallRating, 'Should have a rating for average annual snowfall');
        $this->assertEquals(95.0, $snowfallRating['value'], 'Average annual snowfall rating should be 95.0');

        // Check for the presence of total_score rating
        $totalScoreRating = array_values(array_filter($ratingScores, function($score) {
            return $score['type']['name'] === 'total_score';
        }))[0] ?? null;

        $this->assertNotNull($totalScoreRating, 'Should have a rating for total score');
        $this->assertEquals(100.0, $totalScoreRating['value'], 'Total score rating should be 100.0');

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

    public function testRatingsByUser(): void
    {
        $response = $this->graphQL('
            {
                resort(id: 1) {
                    id
                    title
                    ratingScores {
                        id
                        name
                        title
                        value
                        type {
                            id
                            name
                        }
                    }
                }
            }
        ');

        // Log the response for debugging
        \Log::info('RatingsByUser Response:', $response->json('data.resort'));

        $ratingScores = $response->json('data.resort.ratingScores');
        $this->assertCount(2, $ratingScores, 'Should have two aggregated rating scores');

        // Check for the presence of average_annual_snowfall rating
        $snowfallRating = array_values(array_filter($ratingScores, function($score) {
            return $score['type']['name'] === 'average_annual_snowfall';
        }))[0] ?? null;

        $this->assertNotNull($snowfallRating, 'Should have a rating for average annual snowfall');
        $this->assertEquals(95.0, $snowfallRating['value'], 'Average annual snowfall rating should be 95.0');

        // Check for the presence of total_score rating
        $totalScoreRating = array_values(array_filter($ratingScores, function($score) {
            return $score['type']['name'] === 'total_score';
        }))[0] ?? null;

        $this->assertNotNull($totalScoreRating, 'Should have a rating for total score');
        $this->assertEquals(100.0, $totalScoreRating['value'], 'Total score rating should be 100.0');
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

        $this->assertEquals('resorts/test-resort', Resort::find($resort->id)->getUrlAttribute());


    }

    /**
     * @return void
     */
    public function testResortsPaginationWithFilters(): void
    {
        $response = $this->graphQL('
            {
                resorts(
                    first: 2
                    page: 1
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
                        total
                    }
                }
            }
        ');

        $this->assertCount(
            2,
            $response->json('data.resorts.data'),
            'Should return correct number of resorts per page'
        );
        $this->assertSame(
            1,
            $response->json('data.resorts.paginatorInfo.currentPage'),
            'Should be on the first page'
        );
        $this->assertSame(
            2,
            $response->json('data.resorts.paginatorInfo.lastPage'),
            'Should have correct number of pages'
        );
        $this->assertSame(
            3,
            $response->json('data.resorts.paginatorInfo.total'),
            'Should have correct total number of filtered resorts'
        );
    }

    /**
     * @return void
     */
    public function testNonExistentResort(): void
    {
        $response = $this->graphQL('
            {
                resort(id: 9999) {
                    id
                    title
                }
            }
        ');

        $this->assertNull(
            $response->json('data.resort'),
            'Should return null for non-existent resort'
        );
    }

    /**
     * @return void
     */
    public function testEmptyResultSet(): void
    {
        $response = $this->graphQL('
            {
                resorts(
                    first: 10
                    filter: {groupedType: [{
                        type_name: "total_score",
                        operator: ">",
                        value: "1000"
                    }]}
                ) {
                    data {
                        id
                        title
                        url_segment
                    }
                    paginatorInfo {
                        total
                    }
                }
            }
        ');

        $this->assertCount(
            0,
            $response->json('data.resorts.data'),
            'Should return an empty data array'
        );
        $this->assertSame(
            0,
            $response->json('data.resorts.paginatorInfo.total'),
            'Should have a total count of 0'
        );
    }

    public function testResortsByMultipleGroupedTypeFilters(): void
    {
        $response = $this->graphQL('
            {
                resorts(
                    first: 10
                    filter: {groupedType: [
                        {
                            type_name: "total_score",
                            operator: ">",
                            value: "50"
                        },
                        {
                            type_name: "vertical_drop",
                            operator: ">",
                            value: "200"
                        }
                    ]}
                ) {
                    data {
                        id
                        title
                        url_segment
                    }
                }
            }
        ');

        $this->assertCount(
            2,
            $response->json('data.resorts.data'),
            'Should return resorts matching both grouped type filters'
        );
        $this->assertContains(
            'foo-resort',
            $response->json('data.resorts.data.*.url_segment'),
            'Should include resort matching both criteria'
        );
        $this->assertContains(
            'baz-resort',
            $response->json('data.resorts.data.*.url_segment'),
            'Should include resort matching both criteria'
        );
    }

    public function testResortsByLessThanFilter(): void
    {
        $response = $this->graphQL('
            {
                resorts(
                    first: 10
                    filter: {groupedType: [{
                        type_name: "average_annual_snowfall",
                        operator: "<",
                        value: "5"
                    }]}
                ) {
                    data {
                        id
                        title
                        url_segment
                    }
                }
            }
        ');

        $this->assertCount(
            1,
            $response->json('data.resorts.data'),
            'Should return resort with snowfall less than 5m'
        );
        $this->assertContains(
            'bin-resort',
            $response->json('data.resorts.data.*.url_segment'),
            'Should include resort with snowfall less than 5m'
        );
    }

    public function testResortsByEqualToFilter(): void
    {
        $response = $this->graphQL('
            {
                resorts(
                    first: 10
                    filter: {groupedType: [{
                        type_name: "total_score",
                        operator: "=",
                        value: "75"
                    }]}
                ) {
                    data {
                        id
                        title
                        url_segment
                    }
                }
            }
        ');

        $this->assertCount(
            1,
            $response->json('data.resorts.data'),
            'Should return resort with total score equal to 75'
        );
        $this->assertSame(
            'baz-resort',
            $response->json('data.resorts.data.0.url_segment'),
            'Should return the correct resort with total score of 75'
        );
    }

    public function testResortsWithOrderByAndFilter(): void
    {
        $response = $this->graphQL('
            {
                resorts(
                    first: 10
                    filter: {groupedType: [{
                        type_name: "total_score",
                        operator: ">",
                        value: "25"
                    }]}
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
                }
            }
        ');

        $this->assertSame(
            ['foo-resort', 'baz-resort', 'bar-resort'],
            $response->json('data.resorts.data.*.url_segment'),
            'Should return filtered resorts ordered by vertical drop descending'
        );
    }


    public function testResortsWithValidFilterTypeNoResults(): void
    {
        $response = $this->graphQL('
            {
                resorts(
                    first: 10
                    filter: {groupedType: [{
                        type_name: "total_score",
                        operator: ">",
                        value: "1000"
                    }]}
                ) {
                    data {
                        id
                        title
                        url_segment
                    }
                }
            }
        ');

        $this->assertCount(
            0,
            $response->json('data.resorts.data'),
            'Should return no resorts when filter criteria matches no resorts'
        );
    }

}
