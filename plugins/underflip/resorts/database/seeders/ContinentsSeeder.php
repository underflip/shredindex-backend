<?php

namespace Underflip\Resorts\Database\Seeders;

use Seeder;
use Underflip\Resorts\Models\Continent;

class ContinentsSeeder extends Seeder
{
    public function run()
    {
        // List of continents with names and codes
        $continents = [
            ['name' => 'Africa', 'code' => 'AF'],
            ['name' => 'Antarctica', 'code' => 'AN'],
            ['name' => 'Asia', 'code' => 'AS'],
            ['name' => 'Oceania', 'code' => 'OC'],
            ['name' => 'Europe', 'code' => 'EU'],
            ['name' => 'North America', 'code' => 'NA'],
            ['name' => 'South America', 'code' => 'SA'],
        ];

        // Create continent records
        foreach ($continents as $continentData) {
            Continent::create($continentData);
        }
    }

    public function down()
    {
        Continent::query()->truncate(); // Directly truncate the table
    }
}
