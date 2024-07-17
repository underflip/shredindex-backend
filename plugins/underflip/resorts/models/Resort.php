<?php

namespace Underflip\Resorts\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Model;
use October\Rain\Database\Relations\HasMany;
use Underflip\Resorts\Classes\ElasticSearchService;


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
     * @var array Fillable fields
     */
    protected $fillable = [
        'title',
        'url_segment',
        'affiliate_url',
        'description',
    ];


    /**
     * @var string The database table used by the model.
     */
    public $table = 'underflip_resorts_resorts';

    /**
     * @var array
     */
    public $hasOne = [
        'location' => Location::class,
        'continent' => Continent::class,
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

    public function continent()
    {
        return $this->hasOneThrough(
            Continent::class,
            Location::class,
            'resort_id', // Foreign key on Location table referencing Resort
            'id', // Primary key on Continents table
            'id', // Local key on Resort table (primary key)
            'country_id' // Foreign key on the pivot table referencing Location's country_id
        );
    }

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
     * @return int|string
     */
    public function getCmsTotalScoreAttribute()
    {
        return $this->total_score ? $this->total_score->value : '(No ratings)';
    }

    /**
     * The "total score" that represents all ratings of this resort
     */
   public function ratingScores()
    {
        return $this->ratings()
            ->selectRaw('resort_id, type_id, AVG(value) as value, CONCAT(resort_id, 1234567890 , type_id ) as id')
            ->groupBy('resort_id', 'type_id')
            ->with('type');
    }

    public function getHighlightsAttribute()
    {
        return $this->ratingScores()
            ->orderBy('value', 'desc')
            ->limit(5)
            ->get();
    }

    public function getLowlightsAttribute()
    {
        return $this->ratingScores()
            ->orderBy('value', 'asc')
            ->limit(3)
            ->get();
    }

    public function updateTotalScore()
    {
        $averageRatings = $this->ratingScores()->get();

        if ($averageRatings->isEmpty()) {
            // Resort has no ratings
            return null;
        }

        // Calculate the average of all average ratings
        $totalAverage = round($averageRatings->avg('value'), 1);

        $totalScore = $this->total_score;

        if ($totalScore && $totalScore->value == $totalAverage) {
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
        $totalScore->value = $totalAverage;

        if (!$totalScore->type) {
            // Make sure the total score has a type (bad things happen if it doesn't)
            $type = app(TotalScore::class)->findOrCreateType();

            $totalScore->type_id = $type->id;
        }

        $totalScore->save();
    }

    public function afterSave()
   {
       $esClient = new ElasticSearchService();
       $client = $esClient->getClient();

       $params = [
           'index' => 'resorts',
           'id'    => $this->id,
           'body'  => $this->toArray()
       ];

       $client->index($params);
   }

   public static function searchInElasticsearch($query)
   {
       $esClient = new ElasticSearchService();
       $client = $esClient->getClient();

       $params = [
           'index' => 'resorts',
           'body'  => [
               'query' => [
                   'multi_match' => [
                       'query' => $query,
                       'fields' => ['title', 'description']
                   ]
               ]
           ]
       ];

       return $client->search($params);
   }
}
