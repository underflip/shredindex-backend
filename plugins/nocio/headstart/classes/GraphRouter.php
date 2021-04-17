<?php

namespace Nocio\Headstart\Classes;

use File;
use Cms\Classes\Router;

class GraphRouter //extends Router
{

    /**
     * @var array A list of parameters names and values extracted from the URL pattern and URL string.
     */
    protected $parameters = [];

    public function __construct($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Returns the current routing parameters.
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }


    /**
     * Returns a routing parameter.
     * @return array
     */
    public function getParameter($name, $default = null)
    {
        if (isset($this->parameters[$name]) && !empty($this->parameters[$name])) {
            return $this->parameters[$name];
        }

        return $default;
    }

}