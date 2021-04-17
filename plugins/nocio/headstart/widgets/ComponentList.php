<?php

namespace Nocio\Headstart\Widgets;

use App;
use Str;
use Lang;
use Input;
use System\Classes\PluginManager;
use Nocio\Headstart\Classes\ComponentHelpers;
use Cms\Widgets\ComponentList as CmsComponentList;


class ComponentList extends CmsComponentList
{

    protected function getData()
    {
        $searchTerm = Str::lower($this->getSearchTerm());
        $searchWords = [];
        if (strlen($searchTerm)) {
            $searchWords = explode(' ', $searchTerm);
        }

        $pluginManager = PluginManager::instance();
        $plugins = $pluginManager->getPlugins();

        $this->prepareComponentList();

        $filterMode = $this->getFilterMode();

        $items = [];
        foreach ($plugins as $plugin) {

            $components = $this->getPluginComponents($plugin);

            if (!is_array($components)) {
                continue;
            }

            $pluginDetails = $plugin->pluginDetails();

            $pluginName = $pluginDetails['name'] ?? Lang::get('system::lang.plugin.unnamed');
            $pluginIcon = $pluginDetails['icon'] ?? 'icon-puzzle-piece';
            $pluginDescription = $pluginDetails['description'] ?? null;

            $pluginClass = get_class($plugin);

            $pluginItems = [];
            foreach ($components as $componentInfo) {
                $className = $componentInfo->className;
                $alias = $componentInfo->alias;
                $component = App::make($className);

                if ($component->isHidden) {
                    continue;
                }

                $componentDetails = $component->componentDetails();

                if (($filterMode == 'graphql') && (!array_get($componentDetails, 'graphql'))) {
                    continue;
                }

                $component->alias = '--alias--';

                $item = (object)[
                    'title'          => ComponentHelpers::getComponentName($component),
                    'description'    => ComponentHelpers::getComponentDescription($component),
                    'plugin'         => $pluginName,
                    'propertyConfig' => ComponentHelpers::getComponentsPropertyConfig($component),
                    'propertyValues' => ComponentHelpers::getComponentPropertyValues($component, $alias),
                    'className'      => get_class($component),
                    'pluginIcon'     => $pluginIcon,
                    'alias'          => $alias,
                    'name'           => $componentInfo->duplicateAlias
                        ? $componentInfo->className
                        : $componentInfo->alias
                ];

                if ($searchWords && !$this->itemMatchesSearch($searchWords, $item)) {
                    continue;
                }

                if (!array_key_exists($pluginClass, $items)) {
                    $group = (object)[
                        'title'       => $pluginName,
                        'description' => $pluginDescription,
                        'pluginClass' => $pluginClass,
                        'icon'        => $pluginIcon,
                        'items'       => []
                    ];

                    $items[$pluginClass] = $group;
                }

                $pluginItems[] = $item;
            }

            usort($pluginItems, function ($a, $b) {
                return strcmp($a->title, $b->title);
            });

            if (isset($items[$pluginClass])) {
                $items[$pluginClass]->items = $pluginItems;
            }
        }

        uasort($items, function ($a, $b) {
            return strcmp($a->title, $b->title);
        });

        return $items;
    }

    public function onToggleFilter()
    {
        $mode = $this->getFilterMode();
        $this->setFilterMode($mode == 'graphql' ? 'all' : 'graphql');

        $result = $this->updateList();
        //$result['#'.$this->getId('toolbar-buttons')] = $this->makePartial('toolbar-buttons');

        return $result;
    }

    protected function setFilterMode($mode)
    {
        $this->putSession('filter', $mode);
    }

    protected function getFilterMode()
    {
        return $this->getSession('filter', 'graphql');
    }

}