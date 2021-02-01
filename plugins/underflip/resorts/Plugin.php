<?php namespace Underflip\Resorts;

use Backend;
use System\Classes\PluginBase;

/**
 * Resorts plugin
 */
class Plugin extends PluginBase
{
    /**
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name' => 'Resorts',
            'description' => 'Provides the Resorts part of Shred Index',
            'author' => 'Underflip',
            'icon' => 'icon-icon-snowflake-o'
        ];
    }

    /**
     * @return array|void
     */
    public function registerComponents()
    {
    }

    /**
     * @return array|void
     */
    public function registerSettings()
    {
    }

    /**
     * @return array
     */
    public function registerNavigation()
    {
        return[
            'resorts' => [
                'label' => 'Resorts',
                'url' => Backend::url('underflip/resorts/resorts'),
                'icon' => 'icon-wrench',
                'sideMenu' => [
                    'resorts' => [
                        'label' => 'Resorts',
                        'icon' => 'icon-wrench',
                        'url' => Backend::url('underflip/resorts/resorts'),
                    ],
                    'types' => [
                        'label' => 'Types',
                        'icon' => 'icon-wrench',
                        'url' => Backend::url('underflip/resorts/types'),
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function registerListColumnTypes()
    {
        return [
            'unit' => function ($value) {
                return $value ?: 'None';
            },
            'shortname' => function ($value) {
                if (!class_exists($value)) {
                    return '';
                }

                return (new \ReflectionClass($value))->getShortName();
            }
        ];
    }
}
