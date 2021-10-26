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
use Underflip\Resorts\Plugin;

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
     * @return List|TypeValue[] The TypeValues used for the enum set
     */
    protected function populateEnum($type, $options, $distinct = false)
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

        return TypeValue::whereIn('id', $ids)->get();
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

        Type::create([
            'name' => 'digital_nomad_score',
            'title' => 'Digital Nomad Score',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'seasonal_worker_score',
            'title' => 'Seasonal Worker Score',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'family_vacation_score',
            'title' => 'Family Vacation Score',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'affordability',
            'title' => 'Affordability',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'money_saving_potential',
            'title' => 'Money Saving Potential',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'snow_quality',
            'title' => 'Snow Quality',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'backcountry_accessibility',
            'title' => 'Backcountry Accessibility',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'slackcountry_accessibility',
            'title' => 'Slackcountry Accessibility',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'nearby_vehicle_access',
            'title' => 'Nearby Sled / Snowmobile Access',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'avalanche_safety',
            'title' => 'Avalanche Safety',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'fresh_tracks',
            'title' => 'Fresh Tracks',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'lift_access',
            'title' => 'Lift Access',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'ski_in_ski_out',
            'title' => 'Ski-In Ski-Out',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'uncrowded',
            'title' => 'Uncrowded',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'expert_terrain',
            'title' => 'Expert Terrain',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'terrain_park',
            'title' => 'Terrain Park',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'child_friendly',
            'title' => 'Child Friendly',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'livability',
            'title' => 'Livability',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'housing_availability',
            'title' => 'Housing Availability',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'job_availability',
            'title' => 'Job Availability',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'summer_activities',
            'title' => 'Summer Activities',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'english_level',
            'title' => 'English Level',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'local_language_non_essential',
            'title' => 'Local Language Non-Essential (for work)',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'lgbt_friendly',
            'title' => 'LGBT Friendly',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'cannabis_friendly',
            'title' => 'Cannabis Friendly',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'international_ratio',
            'title' => 'International Ratio',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'gender_ratio',
            'title' => 'Gender Ratio',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'positive_vibes',
            'title' => 'Positive Vibes',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'camper_friendly',
            'title' => 'Camper Friendly',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'apres',
            'title' => 'Apres',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'night_life',
            'title' => 'Night Life',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'co_working_spaces',
            'title' => 'Co-working Spaces',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'connectivity',
            'title' => 'Internet / Broadband',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'cafes_to_work_from',
            'title' => 'Cafes to work from',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'co_working_culture',
            'title' => 'Co-working Culture',
            'category' => Rating::class,
            'unit_id' => $score->id,
        ]);
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

        Type::create([
            'name' => 'average_annual_snowfall',
            'title' => 'Average Annual Snowfall',
            'category' => Numeric::class,
            'unit_id' => $meters->id,
        ]);

        Type::create([
            'name' => 'elevation_peak',
            'title' => 'Elevation Peak',
            'category' => Numeric::class,
            'unit_id' => $meters->id,
        ]);

        Type::create([
            'name' => 'number_of_runs',
            'title' => 'Number of Runs',
            'category' => Numeric::class,
        ]);

        Type::create([
            'name' => 'number_of_lifts',
            'title' => 'Number of Lifts',
            'category' => Numeric::class,
            'unit_id' => $total->id,
        ]);

        Type::create([
            'name' => 'skiable_terrain',
            'title' => 'Skiable Terrain',
            'category' => Numeric::class,
            'unit_id' => $meters->id,
        ]);

        Type::create([
            'name' => 'vertical_drop',
            'title' => 'Vertical Drop',
            'category' => Numeric::class,
            'unit_id' => $meters->id,
        ]);

        Type::create([
            'name' => 'longest_run',
            'title' => 'Longest Run',
            'category' => Numeric::class,
            'unit_id' => $meters->id,
        ]);

        Type::create([
            'name' => 'terrain_expert',
            'title' => 'Terrain Expert',
            'category' => Numeric::class,
            'unit_id' => $percentage->id,
        ]);

        Type::create([
            'name' => 'terrain_intermediate',
            'title' => 'Terrain Intermediate',
            'category' => Numeric::class,
            'unit_id' => $percentage->id,
        ]);

        Type::create([
            'name' => 'terrain_beginner',
            'title' => 'Terrain Beginner',
            'category' => Numeric::class,
            'unit_id' => $percentage->id,
        ]);
    }

    /**
     * @return void
     */
    protected function seedGenericTypes(): void
    {
        $snowMaking = Type::create([
            'name' => 'snow_making',
            'title' => 'Snow Making',
            'category' => Generic::class,
        ]);

        $this->populateEnum(
            $snowMaking,
            [
                'yes' => 'Yes',
                'no' => 'No',
                'maybe' => 'Maybe',
            ]
        );

        $crossCountry = Type::create([
            'name' => 'has_cross_country_skiiing',
            'title' => 'Has Cross Country Skiing',
            'category' => Generic::class,
        ]);

        $this->populateEnum(
            $crossCountry,
            [
                'yes' => 'Yes',
                'no' => 'No',
                'maybe' => 'Maybe',
            ]
        );

        $nightSkiing = Type::create([
            'name' => 'night_skiing',
            'title' => 'Has Night Skiing',
            'category' => Generic::class,
        ]);

        $this->populateEnum(
            $nightSkiing,
            [
                'yes' => 'Yes',
                'no' => 'No',
                'maybe' => 'Maybe',
            ]
        );
    }
}
