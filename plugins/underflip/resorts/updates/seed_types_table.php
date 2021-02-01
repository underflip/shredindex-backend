<?php

namespace Underflip\Resorts\Updates;

use Underflip\Resorts\Models\Score;
use Underflip\Resorts\Models\Stat;
use Underflip\Resorts\Models\Type;
use Underflip\Resorts\Models\Unit;
use Underflip\Resorts\Models\TypeValue;
use October\Rain\Database\Updates\Seeder;

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
     */
    public function run()
    {
        $this->seedStatTypes();
        $this->seedScoreTypes();
    }

    /**
     * Seed the types used for Stat records
     *
     * @return void
     */
    protected function seedStatTypes()
    {
        $meters = $this->getUnitByName('meter');
        $total = $this->getUnitByName('total');
        $percentage = $this->getUnitByName('percentage');

        Type::create([
            'name' => 'average_annual_snow_fall',
            'title' => 'Average Annual Snow Fall',
            'category' => Stat::class,
            'unit_id' => $meters->id,
        ]);

        Type::create([
            'name' => 'elevation_peak',
            'title' => 'Elevation Peak',
            'category' => Stat::class,
            'unit_id' => $meters->id,
        ]);

        Type::create([
            'name' => 'number_of_runs',
            'title' => 'Number of Runs',
            'category' => Stat::class,
        ]);

        Type::create([
            'name' => 'number_of_lifts',
            'title' => 'Number of Lifts',
            'category' => Stat::class,
            'unit_id' => $total->id,
        ]);

        $snowMaking = Type::create([
            'name' => 'snow_making',
            'title' => 'Snow Making',
            'category' => Stat::class,
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
            'category' => Stat::class,
        ]);

        $this->populateEnum(
            $crossCountry,
            [
                'yes' => 'Yes',
                'no' => 'No',
                'maybe' => 'Maybe',
            ]
        );

        Type::create([
            'name' => 'skiable_terrain',
            'title' => 'Skiable Terrain',
            'category' => Stat::class,
            'unit_id' => $meters->id,
        ]);

        Type::create([
            'name' => 'vertical_drop',
            'title' => 'Vertical Drop',
            'category' => Stat::class,
            'unit_id' => $meters->id,
        ]);

        Type::create([
            'name' => 'longest_run',
            'title' => 'Longest Run',
            'category' => Stat::class,
            'unit_id' => $meters->id,
        ]);

        Type::create([
            'name' => 'terrain_expert',
            'title' => 'Terrain Expert',
            'category' => Stat::class,
            'unit_id' => $percentage->id,
        ]);

        Type::create([
            'name' => 'terrain_intermediate',
            'title' => 'Terrain Intermediate',
            'category' => Stat::class,
            'unit_id' => $percentage->id,
        ]);

        Type::create([
            'name' => 'terrain_beginner',
            'title' => 'Terrain Beginner',
            'category' => Stat::class,
            'unit_id' => $percentage->id,
        ]);

        $nightSkiing = Type::create([
            'name' => 'night_skiing',
            'title' => 'Has Night Skiing',
            'category' => Stat::class,
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

    /**
     * See the types need for Score records
     *
     * @return void
     */
    protected function seedScoreTypes()
    {
        $score = $this->getUnitByName('score');

        Type::create([
            'name' => 'total_shred_score',
            'title' => 'Total Shred Score',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'digital_nomad_score',
            'title' => 'Digital Nomad Score',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'seasonal_worker_score',
            'title' => 'Seasonal Worker Score',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'family_vacation_score',
            'title' => 'Family Vacation Score',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'affordability',
            'title' => 'Affordability',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'money_saving_potential',
            'title' => 'Money Saving Potential',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'snow_quality',
            'title' => 'Snow Quality',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'backcountry_accessibility',
            'title' => 'Backcountry Accessibility',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'slackcountry_accessibility',
            'title' => 'Slackcountry Accessibility',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'nearby_vehicle_access',
            'title' => 'Nearby Sled / Snowmobile Access',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'avalanche_safety',
            'title' => 'Avalanche Safety',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'fresh_tracks',
            'title' => 'Fresh Tracks',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'lift_access',
            'title' => 'Lift Access',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'ski_in_ski_out',
            'title' => 'Ski-In Ski-Out',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'uncrowded',
            'title' => 'Uncrowded',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'expert_terrain',
            'title' => 'Expert Terrain',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'terrain_park',
            'title' => 'Terrain Park',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'child_friendly',
            'title' => 'Child Friendly',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'livability',
            'title' => 'Livability',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'housing_availability',
            'title' => 'Housing Availability',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'job_availability',
            'title' => 'Job Availability',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'summer_activities',
            'title' => 'Summer Activities',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'english_level',
            'title' => 'English Level',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'local_language_non_essential',
            'title' => 'Local Language Non-Essential (for work)',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'lgbt_friendly',
            'title' => 'LGBT Friendly',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'cannabis_friendly',
            'title' => 'Cannabis Friendly',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'international_ratio',
            'title' => 'International Ratio',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'gender_ratio',
            'title' => 'gender Ratio',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'positive_vibes',
            'title' => 'Positive Vibes',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'camper_friendly',
            'title' => 'Camper Friendly',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'apres',
            'title' => 'Apres',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'night_life',
            'title' => 'Night Life',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'co_working_spaces',
            'title' => 'Co-working Spaces',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'connectivity',
            'title' => 'Internet / Broadband',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'cafes_to_work_from',
            'title' => 'Cafes to work from',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
        Type::create([
            'name' => 'co_working_culture',
            'title' => 'Co-working Culture',
            'category' => Score::class,
            'unit_id' => $score->id,
        ]);
    }
}
