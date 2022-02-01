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
     * The "total score" that represents all ratings of this resort
     *
     * @param Resort $resort
     * @return Float|null
     */
    public function calculate(Resort $resort)
    {
        // Get the ratings we'll base the total score on
        $values = $resort->ratings()->pluck('value');

        if (!$values->count()) {
            // Resort has no scores
            return null;
        }

        return round($values->sum()/$values->count(), 1);
    }

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
            // Capture the value we want to denormalize
            $score = $this->calculate($resort);

            if (is_null($score)) {
                // Resort is un-scored
                continue;
            }

            // Clear any existing total score
            $resort->total_score()->delete();

            // Find or create a total score record
            $totalScore = new TotalScore();
            $totalScore->value = $score;

            if (!$totalScore->type) {
                $type = app(TotalScore::class)->findOrCreateType();

                $totalScore->type_id = $type->id;
            }

            $resort->total_score()->save($totalScore);

            $count += 1;
        }

        return $count;
    }

    /**
     * Create/update a Resort's total score based on average rating
     *
     * @throws Exception
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
