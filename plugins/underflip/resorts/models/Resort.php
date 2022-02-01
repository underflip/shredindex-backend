<?php

namespace Underflip\Resorts\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Fluent;
use Model;
use October\Rain\Database\Traits\Validation;

/**
 * Resort model
 *
 * @property int $id
 * @property string $title
 * @property string $url_segment
 * @property Location location
 * @property TotalScore total_score
 * @method Collection ratings()
 * @method Collection numerics()
 * @method Collection generics()
 * @method Collection resort_images()
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
        return sprintf('resort/%s', $this->url_segment);
    }

    /**
     * The best ratings
     *
     * @return \October\Rain\Database\Relations\HasMany
     */
    public function getHighlightsAttribute()
    {
        return $this->ratings()
            ->orderBy('value', 'desc')
            ->limit(3)
            ->get();
    }

    /**
     * The best ratings
     *
     * @return \October\Rain\Database\Relations\HasMany
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
}
