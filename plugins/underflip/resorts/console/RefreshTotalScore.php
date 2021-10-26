<?php

namespace Underflip\Resorts\Console;

use Exception;
use Illuminate\Console\Command;
use Underflip\Resorts\Models\Resort;
use Underflip\Resorts\Models\TotalScore;

class RefreshTotalScore extends Command
{
    /**
     * @var string
     */
    protected $name = 'resorts:refresh_total_score';

    /**
     * @var string
     */
    protected $description = 'Derive the total score of a Resort and store it';

    /**
     * @throws Exception
     */
    public function refreshAll()
    {
        $resorts = Resort::all();
        $count = 0;

        if (!$resorts->count()) {
            // Nothing to refresh
            return $count;
        }

        foreach ($resorts as $resort) {
            $resort->updateTotalScore();

            if (!$resort->total_score) {
                // Resort has no ratings, so no score was changed
                continue;
            }

            $count += 1;
        }

        return $count;
    }

    /**
     * Create/update a Resort's total score based on average rating
     *
     * @throws Exception
     * @codeCoverageIgnore
     */
    public function handle()
    {
        $resorts = Resort::all();

        if (!$resorts->count()) {
            $this->info('No resorts to refresh.');

            // Nothing to do
            return;
        }

        $count = $this->refreshAll();

        // Output a helpful message
        $this->info(sprintf('%s of %s resorts\' total scores refreshed.', $count, $resorts->count()));
    }
}
