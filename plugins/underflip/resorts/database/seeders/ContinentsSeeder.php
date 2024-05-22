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
            ['name' => 'Africa', 'code' => 'AF', 'continent_id' => 1],
            ['name' => 'Antarctica', 'code' => 'AN', 'continent_id' => 2],
            ['name' => 'Asia', 'code' => 'AS', 'continent_id' => 3],
            ['name' => 'Oceania', 'code' => 'OC', 'continent_id' => 4],
            ['name' => 'Europe', 'code' => 'EU', 'continent_id' => 5],
            ['name' => 'North America', 'code' => 'NA', 'continent_id' => 6],
            ['name' => 'South America', 'code' => 'SA', 'continent_id' => 7],
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
