<?php namespace UnderFlip\Resort\Models;

use Model;

/**
 * Model
 */
class Resort extends Model
{
    use \October\Rain\Database\Traits\Validation;
    
    /*
     * Disable timestamps by default.
     * Remove this line if timestamps are defined in the database table.
     */
    public $timestamps = false;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'underflip_resort_resort';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    /**
     * @var array
     */
    public $hasMany = [
        'statistic' => [
            Statistic::class,
            'table' => 'underflip_resort_statistic',
            'order' => 'id',
        ]
    ];

    
    /**
     * @param $query
     * @param array $options
     * @return mixed
     */
    public function scopeListFrontEnd($query, $options = []) {


        $defaults = [
            'page' => null,
            'per_page' => 20,
            'sort_direction' => 'desc',
            // 'statistics' => $statistics,
        ];


        $options = array_merge($defaults, input());

        $sortDirection = $defaults['sort_direction'];
        // $statistics = $defaults['statistics'];

        $sortField = 'total_terrain';
        $statistic_names = StatisticSchema::lists('name', 'id');
        $querytype = StatisticSchema::where('name', $sortField)->value('type');

        // echo Statistic::all();

        /** @var Builder $query */
        $query = $query     
            ->leftJoin('underflip_resort_statistic', 'underflip_resort_resort.id', '=', 'underflip_resort_statistic.resort_id')
            ->select('underflip_resort_resort.*')
            ->groupBy('underflip_resort_resort.id');
      
        $query = $query 
            ->orderByRaw("(underflip_resort_statistic.statistic_name_id = " . array_search($sortField, $statistic_names) . "), cast(underflip_resort_statistic.value as $querytype) " . $sortDirection);
       
        return $query->paginate($options['per_page'], $options['page']);
  
    }
}
