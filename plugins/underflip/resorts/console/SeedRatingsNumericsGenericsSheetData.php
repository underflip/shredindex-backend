<?php

namespace Underflip\Resorts\Console;

use Illuminate\Console\Command;
use Model;
use ReflectionClass;
use Seeder;
use Symfony\Component\Console\Input\InputOption;
use Underflip\Resorts\Database\Seeders\Downable;
use Underflip\Resorts\Database\Seeders\RatingsNumericsGenericsSeeder;
use Illuminate\Support\Facades\Log;

/**
 * A command that seeds data from Google Sheets data only (to be run outside of our plugin's
 * version.yaml roster)
 *
 * @codeCoverageIgnore
 */
class SeedRatingsNumericsGenericsSheetData extends Command
{
    protected $name = 'resorts:seed_ratings_numerics_generics_sheet_data';

    protected $description = 'Seed a range of ratings, numerics, and generics from sheet data';

    /**
     * The sheet data seeders. Must be in topological order (dependencies first)
     *
     * @var Seeder[]
     */
    protected $seeders = [
        RatingsNumericsGenericsSeeder::class,
    ];

    protected function down()
    {
        Log::info('Tearing down fixtures for a fresh seed...');

        foreach ($this->seeders as $seeder) {
            Log::info(sprintf('Tearing down %s...', $seeder));

            $class = new ReflectionClass($seeder);

            if (!is_subclass_of($seeder, Downable::class)) {
                Log::info(sprintf('Skipping %s (does not have the %s interface).', $seeder, Downable::class));

                continue;
            }

            if (!method_exists($seeder, 'down')) {
                Log::info(sprintf('%s::down() does not exist, nothing to tear down.', $seeder));

                continue;
            }

            app($seeder)->down();
        }

        Log::info('Teardown complete.');
    }

    protected function seed()
    {
        Log::info('Starting seeding process...');

        Model::unguarded(function () {
            foreach ($this->seeders as $seeder) {
                Log::info(sprintf('Seeding %s...', $seeder));

                try {
                    $seederInstance = app($seeder);
                    Log::info('Seeder instance created: ' . get_class($seederInstance));
                    $seederInstance->run();
                    Log::info('Seeder run method executed: ' . get_class($seederInstance));
                } catch (\Exception $e) {
                    Log::error('Error seeding ' . $seeder . ': ' . $e->getMessage());
                    Log::error($e->getTraceAsString());
                }
            }
        });

        Log::info('Seeding process complete.');
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

