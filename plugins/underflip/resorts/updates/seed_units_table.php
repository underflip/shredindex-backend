<?php

namespace Underflip\Resorts\Updates;

use Underflip\Resorts\Models\Unit;
use October\Rain\Database\Updates\Seeder;

class SeedUnitsTable extends Seeder
{

    public function run()
    {
        Unit::create([
            'name' => 'meter',
            'title' => 'Meter',
            'singular_title' => 'Meter',
            'plural_title' => 'Meters',
            'format' => '%sm',
        ]);

        Unit::create([
            'name' => 'total',
            'title' => 'Total',
            'singular_title' => 'Total',
            'plural_title' => 'Total',
            'format' => 'total',
        ]);

        Unit::create([
            'name' => 'percentage',
            'title' => 'Percentage',
            'singular_title' => 'Percent',
            'plural_title' => 'Percent',
            'format' => '%s%',
        ]);

        Unit::create([
            'name' => 'score',
            'title' => 'Score',
            'singular_title' => 'Score',
            'plural_title' => 'Scores',
        ]);
    }
}
