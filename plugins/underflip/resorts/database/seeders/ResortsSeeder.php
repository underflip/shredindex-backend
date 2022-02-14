<?php

namespace Underflip\Resorts\Database\Seeders;

use Exception;
use Faker\Factory;
use RainLab\Location\Models\Country;
use RainLab\Location\Models\State;
use Seeder;
use Underflip\Resorts\Models\Location;
use Underflip\Resorts\Models\Rating;
use Underflip\Resorts\Models\Resort;
use Underflip\Resorts\Models\Type;

/**
 * @codeCoverageIgnore
 */
class ResortsSeeder extends Seeder implements Downable
{
    /**
     * @throws Exception
     */
    public function run()
    {
        $quantity = 12;
        $faker = Factory::create();

        // Ordinarily we would like to use Laravel's factories
        // https://laravel.com/docs/6.x/database-testing#writing-factories
        // but sadly, October CMS hasn't exposed those to us. So let's just do
        // it manually

        for ($i = 0; $i < $quantity; $i += 1) {
            $resort = new Resort();

            // Properties
            $resort->title = sprintf('%s %s', $faker->unique()->city, $faker->citySuffix);
            $resort->url_segment = trim(
                strtolower(
                    preg_replace("/[^A-Za-z0-9]/", '-', $resort->title)
                ),
                '-'
            );
            $resort->description = $faker->realText();
            $resort->save();

            // Relations

            // Location
            $location = new Location();
            $location->address = $faker->address;
            $location->city = $faker->city;
            $location->zip = $faker->postcode;
            $location->country_id = Country::inRandomOrder()->pluck('id')->first();
            $location->state_id = rand(0, 9) ? State::inRandomOrder()->pluck('id')->first() : null;
            $location->latitude = $faker->latitude;
            $location->longitude = $faker->longitude;
            $location->vicinity = $faker->state;
            $location->resort_id = $resort->id;
            $location->save();

            // Ratings
            $types = Type::where('category', Rating::class);
            $typesCount = $types->count();

            if (!$typesCount) {
                throw new Exception(sprintf(
                    'There are no existing Types (%s) to rate. Try refreshing the Resorts plugin to seed Types.',
                    Type::class
                ));
            }

            $ratingsQuantity = rand(1, $typesCount);

            for ($r = 0; $r < $ratingsQuantity; $r += 1) {
                $rating = new Rating();
                $rating->value = rand(0, 100);
                $rating->type_id = $types->inRandomOrder()->pluck('id')->first(); // Assign a random type
                $rating->resort_id = $resort->id;
                $rating->save();
            }
        }
    }

    public function down()
    {
        Resort::query()->truncate();
        Rating::query()->truncate();
        Location::query()->truncate();
    }
}
