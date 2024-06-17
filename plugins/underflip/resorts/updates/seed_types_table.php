<?php

namespace Underflip\Resorts\Updates;

use October\Rain\Database\Updates\Seeder;
use Underflip\Resorts\Models\Generic;
use Underflip\Resorts\Models\Numeric;
use Underflip\Resorts\Models\Rating;
use Underflip\Resorts\Models\TotalScore;
use Underflip\Resorts\Models\Type;
use Underflip\Resorts\Models\TypeValue;
use Underflip\Resorts\Models\Unit;

class SeedTypesTable extends Seeder
{
    /**
     * Query an existing unit by name
     *
     * @throws \Exception
     */
    protected function getUnitByName(string $name)
    {
        $unit = Unit::where('name', $name)->first();

        if (!$unit) {
            throw new \Exception(sprintf('Unable to query required Underflip\Resorts\Models\Unit: "%s"', $name));
        }

        return $unit;
    }

    /**
     * Populate an enumerated set of type values, combining existing or new type values in the database
     *
     * @param Type $type
     * @param array $options e.g ['name' => 'title']
     * @param bool $distinct Will generate a new type value instead of using an existing one
     * @return array The TypeValues used for the enum set
     */
    protected function populateEnum(Type $type, array $options, bool $distinct = false): array
    {
        $ids = [];

        foreach ($options as $name => $title) {
            $typeValue = null;

            if (!$distinct) {
                // Query an existing type value
                $typeValue = TypeValue::where('name', $name)->first();
            }

            if (!$typeValue) {
                // Create a new type value
                $typeValue = TypeValue::create([
                    'name' => $name,
                    'title' => $title,
                ]);
            }

            // Attach the relationship
            $type->values()->attach($typeValue->id);

            $ids[] = $typeValue->id;
        }

        return TypeValue::whereIn('id', $ids)->get()->toArray();
    }

    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function run()
    {
        $this->seedRatingTypes();
        $this->seedNumericTypes();
        $this->seedGenericTypes();
        app(TotalScore::class)->findOrCreateType();
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function seedRatingTypes(): void
    {
        $score = $this->getUnitByName('score');

        $ratingTypes = [
            'digital_nomad_score' => 'Digital Nomad Score',
            'seasonal_worker_score' => 'Seasonal Worker Score',
            'family_vacation_score' => 'Family Vacation Score',
            'affordability' => 'Affordability',
            'money_saving_potential' => 'Money Saving Potential',
            'snow_quality' => 'Snow Quality',
            'backcountry_accessibility' => 'Backcountry Accessibility',
            'slackcountry_accessibility' => 'Slackcountry Accessibility',
            'nearby_vehicle_access' => 'Nearby Sled / Snowmobile Access',
            'avalanche_safety' => 'Avalanche Safety',
            'fresh_tracks' => 'Fresh Tracks',
            'lift_access' => 'Lift Access',
            'ski_in_ski_out' => 'Ski-In Ski-Out',
            'uncrowded' => 'Uncrowded',
            'expert_terrain' => 'Expert Terrain',
            'terrain_park' => 'Terrain Park',
            'child_friendly' => 'Child Friendly',
            'livability' => 'Livability',
            'housing_availability' => 'Housing Availability',
            'job_availability' => 'Job Availability',
            'summer_activities' => 'Summer Activities',
            'english_level' => 'English Level',
            'local_language_non_essential' => 'Local Language Non-Essential (for work)',
            'lgbt_friendly' => 'LGBT Friendly',
            'cannabis_friendly' => 'Cannabis Friendly',
            'international_ratio' => 'International Ratio',
            'gender_ratio' => 'Gender Ratio',
            'positive_vibes' => 'Positive Vibes',
            'camper_friendly' => 'Camper Friendly',
            'apres' => 'Apres',
            'night_life' => 'Night Life',
            'co_working_spaces' => 'Co-working Spaces',
            'connectivity' => 'Internet / Broadband',
            'cafes_to_work_from' => 'Cafes to work from',
            'co_working_culture' => 'Co-working Culture',
        ];

        foreach ($ratingTypes as $name => $title) {
            Type::create([
                'name' => $name,
                'title' => $title,
                'category' => Rating::class,
                'unit_id' => $score->id,
            ]);
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    protected function seedNumericTypes(): void
    {
        $meters = $this->getUnitByName('meter');
        $total = $this->getUnitByName('total');
        $percentage = $this->getUnitByName('percentage');

        $numericTypes = [
            'average_annual_snowfall' => ['title' => 'Average Annual Snowfall', 'unit' => $meters->id],
            'elevation_peak' => ['title' => 'Elevation Peak', 'unit' => $meters->id],
            'number_of_runs' => ['title' => 'Number of Runs', 'unit' => null],
            'number_of_lifts' => ['title' => 'Number of Lifts', 'unit' => $total->id],
            'skiable_terrain' => ['title' => 'Skiable Terrain', 'unit' => $meters->id],
            'vertical_drop' => ['title' => 'Vertical Drop', 'unit' => $meters->id],
            'longest_run' => ['title' => 'Longest Run', 'unit' => $meters->id],
            'terrain_expert' => ['title' => 'Terrain Expert', 'unit' => $percentage->id],
            'terrain_intermediate' => ['title' => 'Terrain Intermediate', 'unit' => $percentage->id],
            'terrain_beginner' => ['title' => 'Terrain Beginner', 'unit' => $percentage->id],
        ];

        foreach ($numericTypes as $name => $details) {
            Type::create([
                'name' => $name,
                'title' => $details['title'],
                'category' => Numeric::class,
                'unit_id' => $details['unit'],
            ]);
        }
    }

    /**
     * @return void
     */
    protected function seedGenericTypes(): void
    {
        $genericProperties = [
            'snow_making' => 'Snow Making',
            'has_helicopter_skiing' => 'Has Helicopter Skiing',
            'has_cross_country_skiing' => 'Has Cross Country Skiing',
            'night_skiing' => 'Has Night Skiing',
        ];

        $options = ['yes' => 'Yes', 'no' => 'No', 'maybe' => 'Maybe'];

        foreach ($genericProperties as $name => $title) {
            $property = Type::create([
                'name' => $name,
                'title' => $title,
                'category' => Generic::class,
            ]);

            $this->populateEnum($property, $options);
        }
    }
}
