<?php namespace Cms;

use Event;
use Backend;
use BackendAuth;
use Cms\Models\ThemeLog;
use Cms\Models\ThemeData;
use Cms\Classes\Theme;
use Cms\Classes\CmsObject;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\ThemeManager;
use Cms\Classes\CmsObjectCache;
use Cms\Widgets\PageLookup;
use Cms\Widgets\SnippetLookup;
use Cms\Classes\CmsReportDataSource;
use Cms\Classes\CmsStatusDataSource;
use Backend\Models\UserRole;
use Backend\Classes\Controller as BackendController;
use System\Classes\SettingsManager;
use October\Rain\Support\ModuleServiceProvider;

/**
 * ServiceProvider for CMS module
 */
class ServiceProvider extends ModuleServiceProvider
{
    /**
     * register the service provider.
     */
    public function register()
    {
        parent::register('cms');

        $this->registerSingletons();
        $this->registerThemeLogging();
        $this->registerCombinerEvents();
        $this->registerThemeSiteEvents();
        $this->registerThemeTranslations();
        $this->registerConsole();
        $this->registerRenamedClasses();

        CmsObjectCache::flush();

        // Backend specific
        if ($this->app->runningInBackend()) {
            $this->registerPageLookupInstance();
            $this->registerDashboardDatasource();
        }
    }

    /**
     * boot the module events.
     */
    public function boot()
    {
        parent::boot('cms');

        $this->bootEditorEvents();
        $this->bootPageLookupEvents();
    }

    /**
     * registerSingletons
     */
    protected function registerSingletons()
    {
        $this->app->singleton('cms.helper', \Cms\Helpers\Cms::class);
        $this->app->singleton('cms.components', \Cms\Classes\ComponentManager::class);
        $this->app->singleton('cms.snippets', \Cms\Classes\SnippetManager::class);
        $this->app->singleton('cms.themes', \Cms\Classes\ThemeManager::class);
        $this->app->singleton('cms.demos.traffic', \Cms\Classes\CmsDemoTrafficDataGenerator::class);
    }

    /**
     * registerConsole for command line specifics
     */
    protected function registerConsole()
    {
        $this->registerConsoleCommand('theme.install', \Cms\Console\ThemeInstall::class);
        $this->registerConsoleCommand('theme.remove', \Cms\Console\ThemeRemove::class);
        $this->registerConsoleCommand('theme.list', \Cms\Console\ThemeList::class);
        $this->registerConsoleCommand('theme.use', \Cms\Console\ThemeUse::class);
        $this->registerConsoleCommand('theme.copy', \Cms\Console\ThemeCopy::class);
        $this->registerConsoleCommand('theme.check', \Cms\Console\ThemeCheck::class);
        $this->registerConsoleCommand('theme.seed', \Cms\Console\ThemeSeed::class);
        $this->registerConsoleCommand('theme.clear', \Cms\Console\ThemeClear::class);
        $this->registerConsoleCommand('theme.cache', \Cms\Console\ThemeCache::class);
    }

    /**
     * registerComponents
     */
    public function registerComponents()
    {
        return [
           \Cms\Components\ViewBag::class => 'viewBag',
           \Cms\Components\Resources::class => 'resources',
           \Cms\Components\SitePicker::class => 'sitePicker'
        ];
    }

    /**
     * registerThemeLogging on templates
     */
    protected function registerThemeLogging()
    {
        CmsObject::extend(function ($model) {
            ThemeLog::bindEventsToModel($model);
        });
    }

    /**
     * registerCombinerEvents for the asset combiner.
     */
    protected function registerCombinerEvents()
    {
        if ($this->app->runningInBackend() || $this->app->runningInConsole()) {
            return;
        }

        Event::listen('cms.combiner.beforePrepare', function ($combiner, $assets) {
            $filters = array_flatten($combiner->getFilters());
            ThemeData::applyAssetVariablesToCombinerFilters($filters);
        });

        Event::listen('cms.combiner.getCacheKey', function ($combiner, &$cacheKey) {
            $cacheKey = $cacheKey . ThemeData::getCombinerCacheKey();
        });
    }

    /**
     * registerThemeSiteEvents will reset the cache in case of a race condition where
     * the theme is accessed before the site is set.
     */
    protected function registerThemeSiteEvents()
    {
        Event::listen('site.changed', function() {
            Theme::resetCache();
        });
    }

    /**
     * registerThemeTranslations localization from an active theme.
     */
    protected function registerThemeTranslations()
    {
        $this->callAfterResolving('translator', function() {
            if ($this->app->runningInBackend()) {
                ThemeManager::instance()->bootAllBackend();
            }
            else {
                ThemeManager::instance()->bootAllFrontend();
            }
        });
    }

    /**
     * registerReportWidgets
     */
    public function registerReportWidgets()
    {
        return [
            \Cms\ReportWidgets\ActiveTheme::class => [
                'label' => 'cms::lang.dashboard.active_theme.widget_title_default',
                'context' => 'dashboard'
            ],
        ];
    }

    /**
     * registerPermissions
     */
    public function registerPermissions()
    {
        return [
            // General
            'general.view_offline' => [
                'label' => 'View Website During Maintenance',
                'tab' => 'General',
                'order' => 100
            ],

            // Editor
            'editor.cms_content' => [
                'label' => 'Manage Content',
                'comment' => 'cms::lang.permissions.manage_content',
                'tab' => 'Editor',
                'roles' => UserRole::CODE_DEVELOPER,
                'order' => 200
            ],
            'editor.cms_assets' => [
                'label' => 'Manage Asset Files',
                'comment' => 'cms::lang.permissions.manage_assets',
                'tab' => 'Editor',
                'roles' => UserRole::CODE_DEVELOPER,
                'order' => 300
            ],
            'editor.cms_pages' => [
                'label' => 'Manage Pages',
                'comment' => 'cms::lang.permissions.manage_pages',
                'tab' => 'Editor',
                'roles' => UserRole::CODE_DEVELOPER,
                'order' => 400
            ],
            'editor.cms_partials' => [
                'label' => 'Manage Partials',
                'comment' => 'cms::lang.permissions.manage_partials',
                'tab' => 'Editor',
                'roles' => UserRole::CODE_DEVELOPER,
                'order' => 500
            ],
            'editor.cms_layouts' => [
                'label' => 'Manage Layouts',
                'comment' => 'cms::lang.permissions.manage_layouts',
                'tab' => 'Editor',
                'roles' => UserRole::CODE_DEVELOPER,
                'order' => 600
            ],

            // Themes
            'cms.themes' => [
                'label' => 'Manage Themes',
                'comment' => 'cms::lang.permissions.manage_themes',
                'tab' => 'Themes',
                'roles' => UserRole::CODE_DEVELOPER,
                'order' => 300
            ],
            'cms.themes.create' => [
                'label' => 'Create Theme',
                'tab' => 'Themes',
                'roles' => UserRole::CODE_DEVELOPER,
                'order' => 400
            ],
            'cms.themes.activate' => [
                'label' => 'Activate Theme',
                'tab' => 'Themes',
                'roles' => UserRole::CODE_DEVELOPER,
                'order' => 600
            ],
            'cms.themes.delete' => [
                'label' => 'Delete Theme',
                'tab' => 'Themes',
                'roles' => UserRole::CODE_DEVELOPER,
                'order' => 600
            ],
            'cms.maintenance_mode' => [
                'label' => 'Manage Maintenance Mode',
                'tab' => 'Themes',
                'order' => 900
            ],
            'cms.theme_customize' => [
                'label' => 'Customize Theme',
                'comment' => 'cms::lang.permissions.manage_theme_options',
                'tab' => 'Themes',
                'order' => 400
            ],

            // Internal Traffic Statistics
            // @vuedashboard
            // 'cms.internal_traffic_statistics' => [
            //     'label' => 'cms::lang.permissions.manage_internal_traffic_statistics',
            //     'tab' => 'Internal Traffic Statistics',
            //     'order' => 1000
            // ]
        ];
    }

    /**
     * registerFormWidgets
     */
    public function registerFormWidgets()
    {
        return [
            \Cms\FormWidgets\PageFinder::class => 'pagefinder'
        ];
    }

    /**
     * registerMarkupTags
     */
    public function registerMarkupTags()
    {
        return [
            'filters' => [
                'link' => [\Cms\Classes\PageManager::class, 'url'],
            ],
            'functions' => [
                'link' => [\Cms\Classes\PageManager::class, 'resolve'],
            ]
        ];
    }

    /**
     * registerSettings
     */
    public function registerSettings()
    {
        return [
            'theme' => [
                'label' => 'Frontend Theme',
                'description' => 'Manage the front-end theme and customization options.',
                'category' => SettingsManager::CATEGORY_CMS,
                'icon' => 'icon-text-image',
                'url' => Backend::url('cms/themes'),
                'permissions' => ['cms.themes', 'cms.theme_customize'],
                'order' => 200
            ],
            'maintenance_settings' => [
                'label' => 'Maintenance Mode',
                'description' => 'Configure the maintenance mode page and toggle the setting.',
                'category' => SettingsManager::CATEGORY_CMS,
                'icon' => 'icon-power',
                'class' => \Cms\Models\MaintenanceSetting::class,
                'permissions' => ['cms.maintenance_mode'],
                'order' => 300
            ],
            'theme_logs' => [
                'label' => 'cms::lang.theme_log.menu_label',
                'description' => 'cms::lang.theme_log.menu_description',
                'category' => SettingsManager::CATEGORY_LOGS,
                'icon' => 'icon-magic',
                'url' => Backend::url('cms/themelogs'),
                'permissions' => ['utilities.logs'],
                'order' => 910,
                'keywords' => 'theme change log'
            ],
            // @vuedashboard
            // 'internal_traffic_statistics' => [
            //     'label' => 'cms::lang.internal_traffic_statistics.label',
            //     'description' => 'cms::lang.internal_traffic_statistics.permission_description',
            //     'category' => SettingsManager::CATEGORY_CMS,
            //     'icon' => 'icon-line-chart',
            //     'url' => Backend::url('cms/internaltrafficstatisticssettings'),
            //     'class' => \Cms\Models\InternalTrafficStatisticsSetting::class,
            //     'permissions' => ['cms.internal_traffic_statistics'],
            //     'order' => 1000
            // ],
        ];
    }

    /**
     * bootPageLookupEvents
     */
    protected function bootPageLookupEvents()
    {
        Event::listen(['cms.pageLookup.listTypes', 'pages.menuitem.listTypes'], function () {
            return [
                'cms-page' => 'CMS Page'
            ];
        });

        Event::listen(['cms.pageLookup.getTypeInfo', 'pages.menuitem.getTypeInfo'], function ($type) {
            if ($type === 'cms-page') {
                return CmsPage::getMenuTypeInfo($type);
            }
        });

        Event::listen(['cms.pageLookup.resolveItem', 'pages.menuitem.resolveItem'], function ($type, $item, $url, $theme) {
            if ($type === 'cms-page') {
                return CmsPage::resolveMenuItem($item, $url, $theme);
            }
        });
    }

    /**
     * bootEditorEvents handles editor events
     */
    protected function bootEditorEvents()
    {
        Event::listen('editor.extension.register', function () {
            return \Cms\Classes\EditorExtension::class;
        });
    }

    /**
     * registerPageLookupInstance ensures page lookup widget is available on all backend pages
     */
    protected function registerPageLookupInstance()
    {
        BackendController::extend(function($controller) {
            if (BackendAuth::getUser()) {
                $manager = new PageLookup($controller, ['alias' => 'ocpagelookup']);
                $manager->bindToController();

                $manager = new SnippetLookup($controller, ['alias' => 'ocsnippetlookup']);
                $manager->bindToController();
            }
        });
    }

    /**
     * registerDashboardDatasource
     */
    protected function registerDashboardDatasource()
    {
        $this->callAfterResolving('backend.reports', function($manager) {
            $manager->registerDataSourceClass(
                CmsReportDataSource::class,
                'cms::lang.dashboard.report_data_source.name'
            );

            $manager->registerDataSourceClass(
                CmsStatusDataSource::class,
                'cms::lang.dashboard.status_data_source.name'
            );
        });
    }

    /**
     * registerRenamedClasses
     */
    protected function registerRenamedClasses()
    {
        $this->app->registerClassAliases([
            \Cms\Classes\PageLookup::class => \Cms\Classes\PageManager::class,
        ]);
    }
}
