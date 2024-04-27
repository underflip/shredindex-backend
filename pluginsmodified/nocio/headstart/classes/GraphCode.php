<?php namespace Nocio\Headstart\Classes;

use Cms\Classes\CodeBase;
use October\Rain\Extension\Extendable;

/**
 * Parent class for PHP classes created for graph PHP resolver section.
 */
class GraphCode extends CodeBase
{

    public $graph;

    /**
     * Creates the object instance.
     * @param \Nocio\Headstart\Classes\Graph $graph Specifies the Headstart graph.
     * @param null
     * @param \Nocio\Headstart\Classes\GraphController $controller Specifies the Graph controller.
     */
    public function __construct($graph, $layout, $controller)
    {
        $this->graph = $graph;
        $this->controller = $controller;

        Extendable::__construct();
    }

    public function __get($name)
    {
        if (isset($this->graph->components[$name]) || isset($this->layout->components[$name])) {
            return $this[$name];
        }

        if (($value = $this->graph->{$name}) !== null) {
            return $value;
        }

        if (array_key_exists($name, $this->controller->vars)) {
            return $this[$name];
        }

        return null;
    }

    public function __set($name, $value)
    {
        return $this->graph->{$name} = $value;
    }

    public function __isset($name)
    {
        return isset($this->graph->{$name});
    }

}