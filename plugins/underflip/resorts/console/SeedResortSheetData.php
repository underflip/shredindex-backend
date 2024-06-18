<?php

// @codeCoverageIgnore

namespace Underflip\Resorts\Console;

use Illuminate\Console\Command;
use Model;
use ReflectionClass;
use Seeder;
use Symfony\Component\Console\Input\InputOption;
use Underflip\Resorts\Database\Seeders\Downable;
use Underflip\Resorts\Database\Seeders\ResortsSeederFromSheets;
use Underflip\Resorts\Database\Seeders\TypesSeeder;
use Underflip\Resorts\Database\Seeders\RatingsNumericsGenericsSeeder;
use Underflip\Resorts\Database\Seeders\SupportersSeeder;
use Underflip\Resorts\Database\Seeders\ContinentsSeeder;
use Underflip\Resorts\Database\Seeders\TeamMembersSeeder;

/**
 * A command that seeds data from google sheets data only (to be run outside of our plugin's
 * version.yaml roster)
 *
 * @codeCoverageIgnore
 */
class SeedResortSheetData extends Command
{
    protected $name = 'resorts:seed_resort_sheet_data';

    protected $description = 'Seed a range of resort sheet data';

    /**
     * The sheet data seeders. Must be in topological order (dependencies first)
     *
     * @var Seeder[]
     */
    protected $seeders = [
        ContinentsSeeder::class,
        TeamMembersSeeder::class,
        SupportersSeeder::class,
        TypesSeeder::class,
        ResortsSeederFromSheets::class,
        RatingsNumericsGenericsSeeder::class,
    ];

    protected function down()
    {
        $this->info('Tearing down fixtures for a fresh seed...');

        foreach ($this->seeders as $seeder) {
            $this->info(sprintf('Tearing down %s...', $seeder));

            $class = new ReflectionClass($seeder);

            if (!is_subclass_of($seeder, Downable::class)) {
                $this->info(sprintf('Skipping %s (does not have the %s interface).', $seeder, Downable::class));

                continue;
            }

            if (!method_exists($seeder, 'down')) {
                $this->info(sprintf('%s::down() does not exist, nothing to tear down.', $seeder));

                continue;
            }

            app($seeder)->down();
        }

        $this->info('Teardown complete.');
    }

    protected function seed()
    {
        $this->info('Seeding fixtures...');

        Model::unguarded(function () {
            foreach ($this->seeders as $seeder) {
                $this->info(sprintf('Seeding %s...', $seeder));
                $this->call($seeder);
            }
        });

        $this->info('Seed complete.');
    }

    public function handle()
    {
        $fresh = (bool) $this->option('fresh');

        if ($fresh) {
            $this->down();
        }

        $this->seed();
    }

    protected function getOptions()
    {
        return [
            ['fresh', null, InputOption::VALUE_NONE, 'Clear fixtures before seeding', null],
        ];
    }
}
