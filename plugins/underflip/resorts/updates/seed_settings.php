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

        $settings->copyright_message = sprintf(
            '[Shredindex](https://shredindex.com/) © %s Developed by [Underflip](https://github.com/underflip/)',
            date('Y')
        );

        $settings->save();
    }
}
