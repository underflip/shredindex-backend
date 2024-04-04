<?php

namespace Underflip\Resorts\Database\Seeders;

use Seeder;
use Underflip\Resorts\Models\Supporter;

/**
 * @codeCoverageIgnore
 */
class SupportersSeeder extends Seeder implements Downable
{
    public function run()
    {
        $reddit = Supporter::create([
            'name' => 'Reddit',
            'url' => 'https://www.reddit.com/r/skiing/comments/dbafm7/so_i_have_been_building_this_what_do_you_guys',
        ]);

        $name = str_replace('.', '', uniqid('', true));
        $diskName = $name.'.png';

        $reddit->image()->createFromFile( base_path() .
            DIRECTORY_SEPARATOR .
            'plugins/underflip/resorts/updates/assets/supporters/reddit.png',
            ['file_name' => 'reddit.png', 'content_type' => 'image/png']
        );


        $productHunt = Supporter::create([
            'name' => 'ProductHunt',
            'url' => 'https://www.producthunt.com/posts/shred-index',
        ]);

        $productHunt->image()->createFromFile( base_path() .
            DIRECTORY_SEPARATOR .
            'plugins/underflip/resorts/updates/assets/supporters/product_hunt.png',
            ['file_name' => 'product_hunt.png', 'content_type' => 'image/png']
        );

        $teton = Supporter::create([
            'name' => 'Teton Gravity Research',
            'url' => 'https://www.tetongravity.com/story/news/ski-bum-life-affordability-and-more-an-index',
        ]);

        $teton->image()->createFromFile( base_path() .
            DIRECTORY_SEPARATOR .
            'plugins/underflip/resorts/updates/assets/supporters/teton.png',
            ['file_name' => 'teton.png', 'content_type' => 'image/png']
        );

        $worldNomads = Supporter::create([
            'name' => 'World Nomads',
            'url' => 'https://www.worldnomads.com/Turnstile/AffiliateLink?partnerCode=thomash&source=link' .
                '&utm_source=thomash&utm_content=weblink&path=//www.worldnomads.com/ski-snowboard-travel-insurance',
        ]);

        $worldNomads->image()->createFromFile( base_path() .
            DIRECTORY_SEPARATOR .
            'plugins/underflip/resorts/updates/assets/supporters/world_nomads.png',
            ['file_name' => 'world_nomads.png', 'content_type' => 'image/png']
        );

        $safetyWing = Supporter::create([
            'name' => 'Safety wing',
            'url' => 'https://www.safetywing.com/a/shredindex-insurance',
        ]);

        $safetyWing->image()->createFromFile( base_path() .
            DIRECTORY_SEPARATOR .
            'plugins/underflip/resorts/updates/assets/supporters/safety_wing.png',
            ['file_name' => 'safety_wing.png', 'content_type' => 'image/png']
        );
    }

    public function down()
    {
        foreach (Supporter::all() as $supporter) {
            $supporter->image()->delete();
        }

        Supporter::query()->truncate();
    }
}
