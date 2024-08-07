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
use Illuminate\Support\Facades\Log;

class RefreshTotalScoreTest extends BaseTestCase
{
    use RefreshDatabase;

    protected $user;

    private function createTypes(): void
    {
        $types = [
            'digital_nomad_score' => ['title' => 'Digital Nomad Score', 'category' => 'Numeric'],
            'seasonal_worker_score' => ['title' => 'Seasonal Worker Score', 'category' => 'Numeric'],
            'family_friendly_score' => ['title' => 'Family Friendly Score', 'category' => 'Numeric'],
            'total_score' => ['title' => 'Total Score', 'category' => 'Numeric'],
            'average_annual_snowfall' => ['title' => 'Average Annual Snowfall', 'category' => 'Numeric'],
            'snow_making' => ['title' => 'Snow Making', 'category' => 'Generic'],
            'vertical_drop' => ['title' => 'Vertical Drop', 'category' => 'Numeric']
        ];

        foreach ($types as $name => $data) {
            $type = Type::where('name', $name)->first();
            if (!$type) {
                $type = new Type();
                $type->name = $name;
                $type->title = $data['title'];
                $type->category = $data['category'];
                $type->save();
            }
        }
    }

public function setUp(): void
    {
        parent::setUp();

        $this->loadPlugin('RainLab.User');
        $this->migrateModules();
        $this->migratePlugin('RainLab.User');

        $this->createTypes();

        Model::unguard();

        $this->user = User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
            'username' => 'johndoe',
            'activated_at' => now(),
        ]);

        $this->createResortsAndRatings();

        Model::reguard();
    }

    private function createResortsAndRatings(): void
    {
        $resorts = [
            'Foo Resort' => ['digital_nomad_score' => 90, 'seasonal_worker_score' => 80, 'family_friendly_score' => 70],
            'Bar Resort' => ['digital_nomad_score' => 70, 'seasonal_worker_score' => 60, 'family_friendly_score' => 50],
            'Bin Resort' => ['digital_nomad_score' => 50, 'seasonal_worker_score' => 40],
        ];

        foreach ($resorts as $resortName => $ratings) {
            $resort = Resort::create([
                'title' => $resortName,
                'url_segment' => strtolower(str_replace(' ', '-', $resortName)),
                'affiliate_url' => strtolower(str_replace(' ', '-', $resortName)),
                'description' => "$resortName Description",
            ]);

            Log::info("Created resort: {$resort->title}");

            foreach ($ratings as $typeName => $value) {
                $type = Type::where('name', $typeName)->firstOrFail();
                Rating::create([
                    'value' => $value,
                    'type_id' => $type->id,
                    'resort_id' => $resort->id,
                    'user_id' => $this->user->id,
                ]);
                Log::info("Created rating for {$resort->title}: {$typeName} = {$value}");
            }

            $resort->updateTotalScore();
            Log::info("Updated total score for {$resort->title}");
        }
    }

    public function testRatingDelete()
    {
        $resort = Resort::first();
        $this->assertNotNull($resort, 'No resort found. Check if resorts are being created correctly.');

        $seasonalWorkerScoreId = Type::where('name', 'seasonal_worker_score')->first()->id;
        $rating = Rating::create([
            'value' => 12,
            'type_id' => $seasonalWorkerScoreId,
            'resort_id' => $resort->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('underflip_resorts_ratings', [
            'id' => $rating->id
        ]);

        $rating->delete();

        $this->assertDatabaseMissing('underflip_resorts_ratings', [
            'id' => $rating->id,
        ]);
    }

    public function testRefreshTotalScore()
    {
        app(RefreshTotalScore::class)->refreshAll();

        /** @var Resort $fooResort */
        $fooResort = Resort::where('url_segment', 'foo-resort')->first();
        $this->assertNotNull($fooResort, 'Foo Resort not found. Check if resorts are being created correctly.');

        $this->assertNotNull(
            $fooResort->total_score,
            'Total score should be created for "Foo Resort" with 3 ratings'
        );

        $this->assertInstanceOf(
            TotalScore::class,
            $fooResort->total_score,
            'Total score resort attribute should have been generated for "Foo Resort"'
        );

        $this->assertEquals(
            80.0,
            $fooResort->total_score->value,
            'Total score should be averaged correctly for "Foo Resort"'
        );

        $barResort = Resort::where('url_segment', 'bar-resort')->first();
        $this->assertNotNull($barResort, 'Bar Resort not found. Check if resorts are being created correctly.');

        $this->assertNotNull(
            $barResort->total_score,
            'Total score should exist for "Bar Resort" with 3 ratings'
        );

        $binResort = Resort::where('url_segment', 'bin-resort')->first();
        $this->assertNotNull($binResort, 'Bin Resort not found. Check if resorts are being created correctly.');

        $this->assertNull(
            $binResort->total_score,
            'Total score should not exist for "Bin Resort" with only 2 ratings'
        );
    }

    public function testGetTypeIdOptions(): void
    {
        $resortAttribute = new Rating();
        $this->assertNotEmpty($resortAttribute->getTypeIdOptions());
    }
}
