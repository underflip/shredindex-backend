<?php

namespace Underflip\Resorts\Updates;

use October\Rain\Database\Updates\Seeder;
use RainLab\Pages\Classes\Page;
use Underflip\Resorts\Models\Settings;

class SeedPages extends Seeder
{
    public function run()
    {
        foreach (Page::all() as $page) {
            // Clear previous pages
            $page->delete();
        }

        $homePage = new Page();
        $homePage->title = 'Home';
        $homePage->url = '/';
        $homePage->navigation_hidden = true;
        $homePage->meta_title = "Shred Index - Snow resort rankings";
        $homePage->meta_description = "Travel to top ranked luxury resorts that live by your lifestyle";
        $homePage->save();
    }
}
