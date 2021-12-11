<?php

namespace Underflip\Resorts\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Model;
use October\Rain\Database\Traits\Validation;

/**
 * Resort model
 *
 * @property int $id
 * @property string $title
 * @property string $url_segment
 * @property Location location
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
     * The resort URL.
     * Uses beforeSave function to eliminate duplicate error on save.
     */
     public function beforeSave()
     {
         return $this->url = sprintf('resort/%s', $this->url_segment);
     }

     /**
      * The total score
      *
      * @return \October\Rain\Database\Relations\HasOne
      */
     public function getTotalScoreAttribute()
     {
         $ratingsWithScore = $this->ratings()
         ->where('value', '>=', 0)
         ->orderBy('value', 'desc')
         ->get();

         $totalValue = 0;

          if (count($ratingsWithScore)){
              foreach ($ratingsWithScore as $rating) {
                  $totalValue = $totalValue + $rating->value;
              }
              return round($totalValue/count($ratingsWithScore),1);
          } else {
              return $totalValue;
          }
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
}
