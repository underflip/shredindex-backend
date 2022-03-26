<?php

namespace Underflip\Resorts\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Model;
use October\Rain\Database\Relations\HasMany;

/**
 * Resort model
 *
 * @property int $id
 * @property string $title
 * @property string $url_segment
 * @property string $affiliate_url
 * @property string $description
 * @property Location $location
 * @property int $location_id
 * @property TotalScore $total_score
 * @property int $total_score_id
 * @method HasOne location()
 * @method HasOne total_score()
 * @method HasMany ratings()
 * @method HasMany numerics()
 * @method HasMany generics()
 * @method HasMany resort_images()
 * @method HasMany comments()
 */
class Resort extends Model
{
    /*
     * Disable timestamps by default, to remove the need for updated_at and
     * created_at columns.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'underflip_resorts_resorts';

    /**
     * @var array
     */
    public $hasOne = [
        'location' => Location::class,
        'total_score' => TotalScore::class,
    ];

    /**
     * @var array
     */
    public $hasMany = [
        'ratings' => Rating::class,
        'numerics' => Numeric::class,
        'generics' => Generic::class,
        'resort_images' => ResortImage::class,
        'comments' => Comment::class,
    ];

    /**
     * The resort's URL
     *
     * @return string
     */
    public function getUrlAttribute()
    {


        return sprintf('resorts/%s', $this->url_segment);
    }

    /**
     * The best ratings
     *
     * @return Collection
     */
    public function getHighlightsAttribute()
    {
        return $this->ratings()
            ->orderBy('value', 'desc')
            ->limit(5)
            ->get();
    }

    /**
     * The best ratings
     *
     * @return Collection
     */
    public function getLowlightsAttribute()
    {
        return $this->ratings()
            ->orderBy('value', 'asc')
            ->limit(3)
            ->get();
    }

    /**
     * @return int|string
     */
    public function getCmsTotalScoreAttribute()
    {
        return $this->total_score ? $this->total_score->value : '(No ratings)';
    }

    /**
     * The "total score" that represents all ratings of this resort
     */
    public function updateTotalScore()
    {
        // Get the ratings on which we'll base the total score
        $values = $this->ratings()->pluck('value');

        if (!$values->count()) {
            // Resort has no ratings
            return null;
        }

        // Calculate the average rating to use as our total score
        $average = round($values->sum()/$values->count(), 1);

        // Get the current total score
        $totalScore = $this->total_score;

        if ($totalScore && $totalScore->value == $average) {
            // Nothing to do - This is important to avoid events looping this call stack
            return null;
        }

        if (!$totalScore) {
            // Ensure there is a total score related to this resort
            $totalScore = new TotalScore();
            $totalScore->value = 0; // The `value` field is required for validation
            $this->total_score()->save($totalScore);
        }

        // Update the score with the new rating average
        $totalScore->value = $average;

        if (!$totalScore->type) {
            // Make sure the total score has a type (bad things happen if it doesn't)
            /** @var TotalScore $type */
            $type = app(TotalScore::class)->findOrCreateType();

            $totalScore->type_id = $type->id;
        }

        $totalScore->save();
    }
}
