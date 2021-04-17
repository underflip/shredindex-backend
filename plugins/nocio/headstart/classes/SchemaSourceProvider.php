<?php

namespace Nocio\Headstart\Classes;

use Cache;
use Nocio\Headstart\Models\Settings;
use Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider as LighthouseSchemaSourceProvider;
use GraphQL\Language\Parser;
use GraphQL\Error\Error;
use Cms\Classes\ComponentManager;
use Cms\Classes\ComponentPartial;

class SchemaSourceProvider implements LighthouseSchemaSourceProvider
{

    protected $fieldGraphMapCacheKey = 'headstart-schema-field-graph-map';

    /**
     * @var Schema
     */
    public $template;

    /**
     * @var string
     */
    protected $rootSchemaPath;

    /**
     * @var array
     * Collects graphs and their parsed schema
     */
    protected $graphMap;

    /**
     * @var array
     * Maps field names to graphs
     */
    protected $fieldGraphMap;


    /**
     * SchemaSource constructor.
     *
     * @param string|null $template
     */
    public function __construct($template = null)
    {
        $this->template = Schema::load(is_string($template) ? $template : 'headstart');
        $this->rootSchemaPath = $this->template->getPath();
    }

    /**
     * Set schema root path.
     *
     * @param string $path
     *
     * @return SchemaSourceProvider
     */
    public function setRootPath(string $path): self
    {
        $this->rootSchemaPath = $path;

        return $this;
    }

    /**
     * Stitch together schema documents and return the result as a string.
     *
     * @return string
     */
    public function getSchemaString(): string
    {
        // root types
        $schema = '
        type Query {
            headstart: Boolean
        }
        
        type Mutation {
            headstart: Boolean
        }
        ';
        // schema
        $schema .= collect($this->getGraphMap())->implode('schema', '');

        return $schema;
    }

    public function findGraph($fieldName, $findOrFail=false) {
        if (!isset($this->getFieldGraphMap()[$fieldName])) {
            if ($findOrFail) {
                throw new Error("Could not find graph of field '{$fieldName}'.");
            }

            return null;
        }

        $name = $this->getFieldGraphMap()[$fieldName];

        if (isset($this->graphMap[$name])) {
            // if available, use already instantiated graph objects in memory
            return $this->graphMap[$name]['graph'];
        }

        return Graph::loadCached($this->template, $name);
    }

    public function getFieldGraphMap() {
        if (! is_null($this->fieldGraphMap)) {
            return $this->fieldGraphMap;
        }

        // restore from cache

        $cacheable = Settings::get('enable_cache', false);

        if ($cacheable) {
            $map = Cache::get($this->fieldGraphMapCacheKey, false);
            if (
                $map &&
                ($map = @unserialize(@base64_decode($map))) &&
                is_array($map)
            ) {
                $this->fieldGraphMap = $map;
                return $this->fieldGraphMap;
            }
        }

        // rebuild mapping

        $this->fieldGraphMap = [];

        foreach ($this->getGraphMap() as $key => $element) {
            if (empty(trim($element['schema']))) {
                continue;
            }

            // parse the AST to collect fields mapping
            $ast = Parser::parse($element['schema'], ['noLocation' => true]);
            $fields = collect($ast->definitions)
                ->filter(function ($node) {
                    // we only consider the root types since their fields are unique
                    return $node->name->value == 'Query' || $node->name->value == 'Mutation';
                })
                ->flatMap(function ($node) {
                    // collect field names
                    return collect($node->fields)->map(function($field) {
                        return $field->name->value;
                    });
                });

            // map as [$field_name => $graph_filename]
            $this->fieldGraphMap = array_merge($this->fieldGraphMap, array_fill_keys($fields->toArray(), $key));
        }

        // cache mapping

        if ($cacheable) {
            Cache::put(
                $this->fieldGraphMapCacheKey,
                base64_encode(serialize($this->fieldGraphMap)),
                10
            );
        }

        return $this->fieldGraphMap;
    }

    public function getGraphMap() {
        if (!is_null($this->graphMap)) {
            return $this->graphMap;
        }

        $this->graphMap = [];
        $component_re = '/{%\s*component\s+["|\'](.*)["|\']\s*%}/m';
        $component_str = [$this, 'getComponentSchemaString'];
        foreach ($this->template->listGraphs() as $graph) {
            /* @var $graph \Nocio\Headstart\Classes\Graph */
            $markup = $graph->markup;

            $schema = preg_replace_callback($component_re, function(array $matches) use ($graph, $component_str) {
                $matched_alias = $matches[1];
                foreach ($graph->settings['components'] as $component => $properties) {
                    // find component by alias
                    list($name, $alias) = strpos($component, ' ')
                        ? explode(' ', $component)
                        : [$component, $component];
                    if ($alias == $matched_alias) {
                        // resolve component schema
                        return $component_str($name, $alias);
                    }
                }
                // if not found, remove
                return '';
            }, $markup);

            $this->graphMap[$graph->getFileName()] = [
                'graph' => $graph,
                'schema' => $schema
            ];
        }

        return $this->graphMap;
    }

    public function getComponentSchemaString($componentName, $alias) {
        $manager = ComponentManager::instance();
        $componentObj = $manager->makeComponent($componentName);
        if ($partial = ComponentPartial::load($componentObj, 'schema.graphqls')) {
            $content = $partial->getContent();
            $content = str_replace('__SELF__', $alias, $content);

            return $content;
        } else {
            return "# {$componentName} does not provide a default schema definition";
        }
    }

    public function clearCache() {
        Cache::forget($this->fieldGraphMapCacheKey);
    }

}
