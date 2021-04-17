<?php

namespace Nocio\Headstart\Classes;

use App;
use Config;
use Cms\Classes\Controller;
use Cms\Classes\CmsException;
use Cms\Classes\ComponentManager;


class GraphController// extends Controller
{

    protected $graph;

    /**
     * @var self Cache of self
     */
    protected static $instance;


    /**
     * Creates the controller.
     */
    public function __construct($graph, $args)
    {
        $this->graph = $graph;
        $this->router = new GraphRouter($args);

        self::$instance = $this;
    }

    /**
     * Returns an existing instance of the controller.
     * If the controller doesn't exists, returns null.
     * @return mixed Returns the controller object or null.
     */
    public static function getController()
    {
        return self::$instance;
    }

    public function component($alias) {
        if (isset($this->graph->components[$alias])) {
            return $this->graph->components[$alias];
        }

        foreach ($this->graph->settings['components'] as $component => $properties) {
            list($name, $component_alias) = strpos($component, ' ')
                ? explode(' ', $component)
                : [$component, $component];

            if ($component_alias == $alias) {
                // make component
                $manager = ComponentManager::instance();

                if (!$componentObj = $manager->makeComponent($name, null, $properties)) {
                    throw new CmsException(Lang::get('cms::lang.component.not_found', ['name' => $name]));
                }

                $this->setComponentPropertiesFromParams($componentObj, $this->router->getParameters());
                $componentObj->init();

                $componentObj->alias = $alias;
                $this->graph->components[$alias] = $componentObj;

                return $componentObj;
            }
        }
    }

    /**
     * Sets component property values from partial parameters.
     * The property values should be defined as {{ param }}.
     * @param ComponentBase $component The component object.
     * @param array $parameters Specifies the partial parameters.
     */
    protected function setComponentPropertiesFromParams($component, $parameters = [])
    {
        $properties = $component->getProperties();
        $routerParameters = $this->router->getParameters();

        foreach ($properties as $propertyName => $propertyValue) {
            if (is_array($propertyValue)) {
                continue;
            }

            $matches = [];
            if (preg_match('/^\{\{([^\}]+)\}\}$/', $propertyValue, $matches)) {
                $paramName = trim($matches[1]);

                if (substr($paramName, 0, 1) == ':') {
                    $routeParamName = substr($paramName, 1);
                    $newPropertyValue = $routerParameters[$routeParamName] ?? null;
                }
                else {
                    $newPropertyValue = $parameters[$paramName] ?? null;
                }

                $component->setProperty($propertyName, $newPropertyValue);
                $component->setExternalPropertyName($propertyName, $paramName);
            }
        }
    }

    /**
     * Returns a routing parameter.
     * @param string $name Routing parameter name.
     * @param string $default Default to use if none is found.
     * @return string
     */
    public function param($name, $default = null)
    {
        return $this->router->getParameter($name, $default);
    }

}