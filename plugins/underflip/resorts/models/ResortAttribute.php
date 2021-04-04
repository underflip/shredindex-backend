<?php

namespace Underflip\Resorts\Models;

use Model;

/**
 * The base architecture for each characteristic of a resort
 *
 * @property int id
 * @property int resort_id
 * @property int type_id
 * @property Resort resort
 * @property Type type
 */
abstract class ResortAttribute extends Model
{
    /*
     * Disable timestamps by default, to remove the need for updated_at and
     * created_at columns.
     */
    public $timestamps = false;

    /**
     * @var array
     */
    public $belongsTo = [
        'resort' => Resort::class,
        'type' => Type::class,
    ];

    /**
     * @return string
     */
    public function getNameAttribute()
    {
        return $this->type->name;
    }

    /**
     * @return string
     */
    public function getTitleAttribute()
    {
        return $this->type->title;
    }

    /**
     * Provide options when using a dropdown for type_id in CMS fields
     *
     * @return array
     */
    public function getTypeIdOptions()
    {
        $types = Type::all('id', 'title', 'category')->where('category', '=', static::class)->toArray();

        return array_combine(array_column($types, 'id'), array_column($types, 'title'));
    }
}
