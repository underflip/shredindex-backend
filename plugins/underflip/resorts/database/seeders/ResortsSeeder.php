<?php

namespace Underflip\Resorts\Database\Seeders;

use Exception;
use Faker\Factory;
use RainLab\Location\Models\Country;
use RainLab\Location\Models\State;
use Seeder;
use Underflip\Resorts\Models\Comment;
use Underflip\Resorts\Models\Location;
use Underflip\Resorts\Models\Rating;
use Underflip\Resorts\Models\Resort;
use Underflip\Resorts\Models\ResortImage;
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
        $images = [
            'plugins/underflip/resorts/updates/assets/resort-images/gondola-1.jpg',
            'plugins/underflip/resorts/updates/assets/resort-images/gondola-2.jpg',
            'plugins/underflip/resorts/updates/assets/resort-images/mountain-1.jpg',
            'plugins/underflip/resorts/updates/assets/resort-images/mountain-2.jpg',
            'plugins/underflip/resorts/updates/assets/resort-images/mountain-3.jpg',
            'plugins/underflip/resorts/updates/assets/resort-images/ski-1.jpg',
            'plugins/underflip/resorts/updates/assets/resort-images/ski-2.jpg',
            'plugins/underflip/resorts/updates/assets/resort-images/snowboard-1.jpg',
        ];

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
            $bias = rand(0, 9) * 10; // 0, 10, 20, ..., 80, 90.

            if (!$typesCount) {
                throw new Exception(sprintf(
                    'There are no existing Types (%s) to rate. Try refreshing the Resorts plugin to seed Types.',
                    Type::class
                ));
            }

            $ratingsQuantity = rand(1, $typesCount);

            // Create a random number of ratings, but no more than the number of types
            for ($r = 0; $r < $ratingsQuantity; $r += 1) {
                $applyBias = !!rand(0, 9); // We want 1:10 to be unbiased
                $value = $applyBias
                    ? $bias + rand(0, 10) // Pick a number within 10 above the bias
                    : rand(0, 100); // Pick a number between 0 and 100

                // Create a new rating
                $rating = new Rating();
                $rating->value = $value;
                $rating->type_id = $types->inRandomOrder()->pluck('id')->first(); // Assign a random type
                $rating->resort_id = $resort->id;
                $rating->save();
            }

            // Images
            $imagesCount = rand(0, count($images)/2); // Make sure we don't exceed the available images
            $hasImages = !!rand(0, 99);

            if ($hasImages) {
                shuffle($images);

                for ($x = 0; $x < $imagesCount; $x += 1) {
                    // Create a resort image
                    $image = new ResortImage();
                    $image->name = $faker->words(3, true);
                    $image->alt = $faker->words(3, true);
                    $resort->resort_images()->add($image);

                    // Hook it up with a file
                    $image->image()->create([
                        'data' => base_path() .
                            DIRECTORY_SEPARATOR .
                            $images[$x],
                    ]);
                }
            }

            // Comments
            $commentsCount = rand(1, 3);
            $hasComments = !!rand(0, 99);

            if ($hasComments) {
                for ($c = 0; $c < $commentsCount; $c += 1) {
                    // Create a comment
                    $comment = new Comment();
                    $comment->comment = $faker->realText();
                    $comment->author = $faker->name;

                    $resort->comments()->add($comment);
                }
            }
        }
    }

    public function down()
    {
        Resort::query()->truncate();
        Rating::query()->truncate();
        Location::query()->truncate();

        foreach (ResortImage::all() as $resortImage) {
            if ($resortImage->image) {
                // Delete image to avoid stale data after refresh
                $resortImage->image->delete();
            }
        }

        ResortImage::query()->truncate();
        Comment::query()->truncate();
    }
}
