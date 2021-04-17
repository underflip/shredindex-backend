<?php

namespace Nocio\Headstart\Classes;

use Lang;
use Cms\Classes\Page;


class Graph extends Page
{

    /**
     * @var string The container name associated with the model.
     */
    protected $dirName = 'graphs';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'markup',
        'settings',
        'code'
    ];

    /**
     * @var array The rules to be applied to the data.
     */
    public $rules = [
        'title' => 'required'
    ];

    public function getCodeClassParent() : string {
        return GraphCode::class;
    }

    public function runComponents()
    {
        foreach ($this->components as $component) {
            $component->onRun();
        }
    }

}