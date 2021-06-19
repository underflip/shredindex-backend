<?php

namespace Underflip\Resorts\Updates;

use October\Rain\Database\Updates\Seeder;
use Underflip\Resorts\Models\Settings;

class SeedSettings extends Seeder
{
    public function run()
    {
        /** @var Settings $settings */
        $settings = Settings::instance();

        $settings->copyright_message =
            '[Shredindex](https://shredindex.com/) © 2021 Developed by [Underflip](https://github.com/underflip/)';

        $settings->save();
    }
}
