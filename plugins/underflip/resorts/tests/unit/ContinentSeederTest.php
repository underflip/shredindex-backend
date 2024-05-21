<?php

namespace Underflip\Resorts\Tests\Database\Seeders;

use Underflip\Resorts\Database\Seeders\ContinentsSeeder;
use Underflip\Resorts\Models\Continent;
use PluginTestCase;
use October\Rain\Support\Facades\Config;

class ContinentsSeederTest extends PluginTestCase
{
    public function testDownTruncatesContinentsTable()
    {
        $seeder = new ContinentsSeeder();
        $seeder->run();

        $seeder->down();

        $this->assertCount(0, Continent::all());
    }
}
