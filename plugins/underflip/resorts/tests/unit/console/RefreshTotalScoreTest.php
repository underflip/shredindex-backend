<?php

namespace Underflip\Resorts\Tests\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Model;
use Underflip\Resorts\Console\RefreshTotalScore;
use Underflip\Resorts\Models\Rating;
use Underflip\Resorts\Models\Resort;
use Underflip\Resorts\Models\TotalScore;
use Underflip\Resorts\Models\Type;
use Underflip\Resorts\Tests\BaseTestCase;

/**
 * {@see RefreshTotalScore}
 */
class RefreshTotalScoreTest extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * {@inheritDoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $digitalNomadScoreId = Type::where('name', 'digital_nomad_score')->first()->id;
        $sesonalWorkerScoreId = Type::where('name', 'seasonal_worker_score')->first()->id;

        Model::unguard();

        // Create resort: Foo
        $fooResort = Resort::create([
            'title' => 'Foo Resort',
            'url_segment' => 'foo-resort',
            'description' => 'Foo Description',
        ]);

        Rating::create([
            'value' => 99,
            'type_id' => $digitalNomadScoreId,
            'resort_id' => $fooResort->id,
        ]);

        Rating::create([
            'value' => 22,
            'type_id' => $sesonalWorkerScoreId,
            'resort_id' => $fooResort->id,
        ]);

        // Create resort: Bar
        $barResort = Resort::create([
            'title' => 'Bar Resort',
            'url_segment' => 'bar-resort',
            'description' => 'Bar Description',
        ]);

        Rating::create([
            'value' => 88,
            'type_id' => $digitalNomadScoreId,
            'resort_id' => $barResort->id,
        ]);

        Rating::create([
            'value' => 11,
            'type_id' => $sesonalWorkerScoreId,
            'resort_id' => $barResort->id,
        ]);

        // Create resort: Bin
        Resort::create([
            'title' => 'Bin Resort',
            'url_segment' => 'bin-resort',
            'description' => 'Bin Description',
        ]);

        Model::reguard();
    }

    /**
     * @return void
     */
    public function testRefreshTotalScore()
    {
        app(RefreshTotalScore::class)->refreshAll();

        /** @var Resort $fooResort */
        $fooResort = Resort::where('url_segment', 'foo-resort')->first();

        $this->assertNotNull(
            $fooResort->total_score,
            'No Total score has been created for "Foo Resort"'
        );

        $this->assertSame(
            TotalScore::class,
            get_class($fooResort->total_score),
            'Total score resort attribute should have been generated for "Foo Resort"'
        );

        $this->assertEquals(
            60.5,
            $fooResort->total_score->value,
            'Total score should be averaged correctly'
        );

        $barResort = Resort::where('url_segment', 'bar-resort')->first();

        $this->assertSame(
            TotalScore::class,
            get_class($fooResort->total_score),
            'Total score resort attribute should have been generated for "Bar Resort"'
        );

        $this->assertEquals(
            49.5,
            $barResort->total_score->value,
            'Total score should be averaged correctly'
        );

        $binResort = Resort::where('url_segment', 'bin-resort')->first();

        $this->assertNull(
            $binResort->total_score,
            'Total score should not exist for resorts with no ratings'
        );
    }
}
