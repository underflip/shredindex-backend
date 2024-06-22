<?php

namespace Underflip\Resorts;

use Backend;
use Backend\Classes\NavigationManager;
use Event;
use System\Classes\PluginBase;
use Underflip\Resorts\Console\RefreshTotalScore;
use Underflip\Resorts\Console\SeedResortSheetData;
use Underflip\Resorts\Console\SeedResortImageSheetData;
use Underflip\Resorts\Console\SeedRatingsNumericsGenericsSheetData;
use Underflip\Resorts\Console\SeedResortTypes;
use Underflip\Resorts\Console\SeedTestData;
use Underflip\Resorts\Models\Rating;
use Underflip\Resorts\models\Settings;

/**
 * Resorts plugin
 */
class Plugin extends PluginBase
{
    public const UNIT_NAME_SCORE = 'score';

    /**
     * @var array
     */
    public $require = [
        'Nocio.Headstart',
    ];

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
     * @return void
     */
    public function boot()
    {
        config(['lighthouse.namespaces.directives' => 'Underflip\\Resorts\\GraphQL\\Directives']);

        Event::listen(['rating.save', 'rating.delete'], function (Rating $rating) {
            $resort = $rating->resort;

            if ($resort) {
                // Refresh total scores
                $resort->updateTotalScore();
            }
        });
    }

    /**
     * @return void
     */
    public function register()
    {
        $this->registerConsoleCommand('resorts:seed_resort_sheet_data', SeedResortSheetData::class);
        $this->registerConsoleCommand('resorts:seed_resort_types', SeedResortTypes::class);
        $this->registerConsoleCommand('resorts:seed_ratings_numerics_generics_sheet_data', SeedRatingsNumericsGenericsSheetData::class);
        $this->registerConsoleCommand('resorts:seed_resort_image_sheet_data', SeedResortImageSheetData::class);
        $this->registerConsoleCommand('resorts:seed_test_data', SeedTestData::class);
        $this->registerConsoleCommand('resorts:refresh_total_score', RefreshTotalScore::class);
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
    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'underflip.resorts::lang.settings.label',
                'description' => 'underflip.resorts::lang.settings.description',
                'category'    => 'system::lang.system.categories.cms',
                'icon'        => 'icon-cogs',
                'class'       => Settings::class,
                'order'       => 1,
                'keywords'    => 'shredindex settings',
            ]
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
