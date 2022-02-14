<?php

namespace Underflip\Resorts\Database\Seeders;

use Underflip\Resorts\Console\SeedTestData;

/**
 * Add this to {@see Seeder}s to make give them the ability to have their
 * fixtures torn down as well (typically for a fresh seed)
 *
 * @codeCoverageIgnore
 */
interface Downable
{
    /**
     * Tear down the fixtures created by this Seeder. Tear down is invoked
     * by {@see SeedTestData::down()}
     *
     * @return void
     */
    public function down();
}
