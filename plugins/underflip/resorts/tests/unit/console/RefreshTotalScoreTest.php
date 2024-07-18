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
use RainLab\User\Models\User;

class RefreshTotalScoreTest extends BaseTestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        // Load and migrate the RainLab.User plugin
        $this->loadPlugin('RainLab.User');
        $this->migratePlugin('RainLab.User');

        $digitalNomadScoreId = Type::where('name', 'co_working')->first()->id;
        $seasonalWorkerScoreId = Type::where('name', 'seasonal_worker')->first()->id;

        Model::unguard();

        // Create a test user
        $this->user = User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'username' => 'johndoe',
            'activated_at' => now(),
        ]);

        // Create resort: Foo
        $fooResort = Resort::create([
            'title' => 'Foo Resort',
            'url_segment' => 'foo-resort',
            'affiliate_url' => 'foo-resort',
            'description' => 'Foo Description',
        ]);

        Rating::create([
            'value' => 99,
            'type_id' => $digitalNomadScoreId,
            'resort_id' => $fooResort->id,
            'user_id' => $this->user->id,
        ]);

        Rating::create([
            'value' => 22,
            'type_id' => $seasonalWorkerScoreId,
            'resort_id' => $fooResort->id,
            'user_id' => $this->user->id,
        ]);

        // Create resort: Bar
        $barResort = Resort::create([
            'title' => 'Bar Resort',
            'url_segment' => 'bar-resort',
            'affiliate_url' => 'bar-resort',
            'description' => 'Bar Description',
        ]);

        Rating::create([
            'value' => 88,
            'type_id' => $digitalNomadScoreId,
            'resort_id' => $barResort->id,
            'user_id' => $this->user->id,
        ]);

        Rating::create([
            'value' => 11,
            'type_id' => $seasonalWorkerScoreId,
            'resort_id' => $barResort->id,
            'user_id' => $this->user->id,
        ]);

        // Create resort: Bin
        Resort::create([
            'title' => 'Bin Resort',
            'url_segment' => 'bin-resort',
            'affiliate_url' => 'bin-resort',
            'description' => 'Bin Description',
        ]);

        Model::reguard();
    }

    public function testRefreshTotalScore()
    {
        app(RefreshTotalScore::class)->refreshAll();

        /** @var Resort $fooResort */
        $fooResort = Resort::where('url_segment', 'foo-resort')->first();

        $this->assertNotNull(
            $fooResort->total_score,
            'No Total score has been created for "Foo Resort"'
        );

        $this->assertInstanceOf(
            TotalScore::class,
            $fooResort->total_score,
            'Total score resort attribute should have been generated for "Foo Resort"'
        );

        $this->assertEquals(
            60.5,
            $fooResort->total_score->value,
            'Total score should be averaged correctly'
        );

        $barResort = Resort::where('url_segment', 'bar-resort')->first();

        $this->assertInstanceOf(
            TotalScore::class,
            $barResort->total_score,
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
