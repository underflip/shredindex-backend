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

        $reddit->image()->create([
            'data' => base_path() .
                DIRECTORY_SEPARATOR .
                'plugins/underflip/resorts/updates/assets/supporters/reddit.png',
        ]);

        $productHunt = Supporter::create([
            'name' => 'ProductHunt',
            'url' => 'https://www.producthunt.com/posts/shred-index',
        ]);

        $productHunt->image()->create([
            'data' => base_path() .
                DIRECTORY_SEPARATOR .
                'plugins/underflip/resorts/updates/assets/supporters/product_hunt.png',
        ]);

        $teton = Supporter::create([
            'name' => 'Teton Gravity Research',
            'url' => 'https://www.tetongravity.com/story/news/ski-bum-life-affordability-and-more-an-index',
        ]);

        $teton->image()->create([
            'data' => base_path() .
                DIRECTORY_SEPARATOR .
                'plugins/underflip/resorts/updates/assets/supporters/teton.png',
        ]);

        $worldNomads = Supporter::create([
            'name' => 'World Nomads',
            'url' => 'https://www.worldnomads.com/Turnstile/AffiliateLink?partnerCode=thomash&source=link' .
                '&utm_source=thomash&utm_content=weblink&path=//www.worldnomads.com/ski-snowboard-travel-insurance',
        ]);

        $worldNomads->image()->create([
            'data' => base_path() .
                DIRECTORY_SEPARATOR .
                'plugins/underflip/resorts/updates/assets/supporters/world_nomads.png',
        ]);

        $safetyWing = Supporter::create([
            'name' => 'Safety wing',
            'url' => 'https://www.safetywing.com/a/shredindex-insurance',
        ]);

        $safetyWing->image()->create([
            'data' => base_path() .
                DIRECTORY_SEPARATOR .
                'plugins/underflip/resorts/updates/assets/supporters/safety_wing.png',
        ]);
    }

    public function down()
    {
        foreach (Supporter::all() as $supporter) {
            $supporter->image()->delete();
        }

        Supporter::query()->truncate();
    }
}
