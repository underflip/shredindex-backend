<?php namespace System;

use Db;
use Site;
use Event;
use Config;
use Schema;
use System;
use Backend;
use System\Models\EventLog;
use System\Models\MailSetting;
use System\Classes\MailManager;
use System\Classes\ErrorHandler;
use System\Classes\PluginManager;
use System\Classes\SettingsManager;
use System\Twig\Engine as TwigEngine;
use System\Twig\Loader as TwigLoader;
use System\Twig\Extension as TwigExtension;
use System\Twig\SecurityPolicy as TwigSecurityPolicy;
use October\Rain\Support\ModuleServiceProvider;
use Illuminate\Pagination\Paginator;
use Twig\Environment as TwigEnvironment;
use System\Classes\SystemReportDataSource;

/**
 * ServiceProvider for System module
 */
class ServiceProvider extends ModuleServiceProvider
{
    /**
     * register the service provider.
     */
    public function register()
    {
        parent::register('system');

        $this->registerSingletons();
        $this->registerErrorHandler();
        $this->registerLogging();
        $this->registerTwigParser();
        $this->registerValidator();
        $this->registerManifest();
        $this->registerConsole();
        $this->extendViewService();
        $this->extendMailerService();

        // Register other module providers
        foreach (System::listModules() as $module) {
            if ($module !== 'System' && class_exists($spClass = '\\' . $module . '\ServiceProvider')) {
                $this->app->register($spClass);
            }
        }

        // Register all plugins
        PluginManager::instance()->registerFromProvider();

        // Register app service provider
        if (class_exists(\App\Provider::class)) {
            $this->app->register(\App\Provider::class);
        }

        // Backend specific
        if ($this->app->runningInBackend()) {
            $this->registerDashboardDatasource();
            $this->extendBackendNavigation();
            $this->extendBackendSettings();
        }
    }

    /**
     * boot the module events.
     */
    public function boot()
    {
        // Fix UTF8MB4 support for MariaDB < 10.2 and MySQL < 5.7
        $this->applyDatabaseDefaultStringLength();

        // Fix use of Storage::url() for local disks that haven't been configured correctly
        foreach (Config::get('filesystems.disks') as $key => $config) {
            if (
                isset($config['driver']) &&
                $config['driver'] === 'local' &&
                isset($config['root']) &&
                ends_with($config['root'], '/storage/app') &&
                empty($config['url'])
            ) {
                Config::set("filesystems.disks.$key.url", '/storage/app');
            }
        }

        // Set pagination views
        Paginator::defaultView('system::pagination.default');
        Paginator::defaultSimpleView('system::pagination.simple');

        // Boot plugins
        PluginManager::instance()->bootFromProvider();

        parent::boot('system');
    }

    /**
     * registerSingletons
     */
    protected function registerSingletons()
    {
        $this->app->singleton('system.cacher', \System\Helpers\Cache::class);
        $this->app->singleton('system.helper', \System\Helpers\System::class);
        $this->app->singleton('system.manifest', \System\Classes\ManifestCache::class);
        $this->app->singleton('system.preset', \System\Classes\PresetManager::class);
        $this->app->singleton('system.ui', \System\Classes\UiManager::class);
        $this->app->singleton('system.sites', \System\Classes\SiteManager::class);
        $this->app->singleton('system.resizer', \System\Classes\ResizeImages::class);
        $this->app->singleton('system.combiner', \System\Classes\CombineAssets::class);
        $this->app->singleton('system.mailer', \System\Classes\MailManager::class);
        $this->app->singleton('system.markup', \System\Classes\MarkupManager::class);
        $this->app->singleton('system.settings', \System\Classes\SettingsManager::class);
        $this->app->singleton('system.updater', \System\Classes\UpdateManager::class);
        $this->app->singleton('system.versions', \System\Classes\VersionManager::class);
        $this->app->singleton('system.plugins', \System\Classes\PluginManager::class);
    }

    /**
     * registerMarkupTags
     */
    public function registerMarkupTags()
    {
        return [
            'functions' => [
                // Escaped Functions
                'input' => ['input', true],
                'post' => ['post', true],
                'get' => ['get', true],
                'form_value' => [\Form::class, 'value', true],

                // Raw Functions
                'link_to' => 'link_to',
                'link_to_asset' => 'link_to_asset',
                'link_to_route' => 'link_to_route',
                'link_to_action' => 'link_to_action',
                'array_only' => 'array_only',
                'array_except' => 'array_except',
                'asset' => 'asset',
                'action' => 'action',
                'url' => 'url',
                'route' => 'route',
                'secure_url' => 'secure_url',
                'secure_asset' => 'secure_asset',
                'html_email' => [\Html::class, 'email'],
                'html_mailto' => [\Html::class, 'mailto'],

                // Escaped Classes
                'str_*' => [\Str::class, '*', true],
                'html_*' => [\Html::class, '*', true],

                // Raw Classes
                'url_*' => [\Url::class, '*'],
                'form_*' => [\Form::class, '*'],
                'form_macro' => [\Form::class, '__call']
            ],
            'filters' => [
                // Escaped Functions
                'str_replace' => [fn(...$args) => \Str::replace($args[1] ?? '', $args[2] ?? '', $args[0] ?? ''), true],
                'str_replace_first' => [fn(...$args) => \Str::replaceFirst($args[1] ?? '', $args[2] ?? '', $args[0] ?? ''), true],
                'str_replace_last' => [fn(...$args) => \Str::replaceLast($args[1] ?? '', $args[2] ?? '', $args[0] ?? ''), true],
                'str_replace_array' => [fn(...$args) => \Str::replaceArray($args[1] ?? '', $args[2] ?? [], $args[0] ?? ''), true],

                // Escaped Classes
                'str_*' => [\Str::class, '*', true],

                // Raw Classes
                'url_*' => [\Url::class, '*'],
                'html_*' => [\Html::class, '*'],
                'slug' => [\Str::class, 'slug'],
                'plural' => [\Str::class, 'plural'],
                'singular' => [\Str::class, 'singular'],
                'finish' => [\Str::class, 'finish'],
                'snake' => [\Str::class, 'snake'],
                'camel' => [\Str::class, 'camel'],
                'studly' => [\Str::class, 'studly'],
                'md' => [\Markdown::class, 'parse'],
                'md_safe' => [\Markdown::class, 'parseSafe'],
                'md_clean' => [\Markdown::class, 'parseClean'],
                'md_indent' => [\Markdown::class, 'parseIndent'],
                'time_since' => [\System\Helpers\DateTime::class, 'timeSince'],
                'time_tense' => [\System\Helpers\DateTime::class, 'timeTense'],
            ]
        ];
    }

    /**
     * registerManifest
     */
    protected function registerManifest()
    {
        $this->app->after(function() {
            $this->app->make('system.manifest')->build();
        });
    }

    /**
     * registerConsole command line specifics
     */
    protected function registerConsole()
    {
        // Allow app and plugins to use the scheduler
        Event::listen('console.schedule', function ($schedule) {
            foreach (PluginManager::instance()->getPlugins() as $plugin) {
                $plugin->registerSchedule($schedule);
            }

            if ($app = $this->app->getProvider(\App\Provider::class)) {
                $app->registerSchedule($schedule);
            }
        });

        // Add CMS based cache clearing to native command
        Event::listen('cache:cleared', function () {
            \System\Helpers\Cache::clearInternal();
        });

        // Register console commands
        $this->registerConsoleCommand('october.up', \System\Console\OctoberUp::class);
        $this->registerConsoleCommand('october.down', \System\Console\OctoberDown::class);
        $this->registerConsoleCommand('october.migrate', \System\Console\OctoberMigrate::class);
        $this->registerConsoleCommand('october.update', \System\Console\OctoberUpdate::class);
        $this->registerConsoleCommand('october.util', \System\Console\OctoberUtil::class);
        $this->registerConsoleCommand('october.mirror', \System\Console\OctoberMirror::class);
        $this->registerConsoleCommand('october.fresh', \System\Console\OctoberFresh::class);
        $this->registerConsoleCommand('october.passwd', \System\Console\OctoberPasswd::class);
        $this->registerConsoleCommand('october.optimize', \System\Console\OctoberOptimize::class);
        $this->registerConsoleCommand('october.about', \System\Console\OctoberAbout::class);

        $this->registerConsoleCommand('plugin.install', \System\Console\PluginInstall::class);
        $this->registerConsoleCommand('plugin.remove', \System\Console\PluginRemove::class);
        $this->registerConsoleCommand('plugin.disable', \System\Console\PluginDisable::class);
        $this->registerConsoleCommand('plugin.enable', \System\Console\PluginEnable::class);
        $this->registerConsoleCommand('plugin.refresh', \System\Console\PluginRefresh::class);
        $this->registerConsoleCommand('plugin.list', \System\Console\PluginList::class);
        $this->registerConsoleCommand('plugin.check', \System\Console\PluginCheck::class);
        $this->registerConsoleCommand('plugin.test', \System\Console\PluginTest::class);
        $this->registerConsoleCommand('plugin.seed', \System\Console\PluginSeed::class);

        $this->registerConsoleCommand('project.sync', \System\Console\ProjectSync::class);
    }

    /**
     * registerErrorHandler for uncaught Exceptions
     */
    protected function registerErrorHandler()
    {
        Event::listen('exception.beforeReport', function ($exception) {
            (new ErrorHandler)->beforeReport($exception);
        });

        Event::listen('exception.beforeRender', function ($exception, $httpCode, $request) {
            return (new ErrorHandler)->handleException($exception);
        });
    }

    /**
     * registerLogging writes all log events to the database
     */
    protected function registerLogging()
    {
        Event::listen(\Illuminate\Log\Events\MessageLogged::class, function ($event) {
            if (EventLog::useLogging()) {
                EventLog::add($event->message, $event->level, $event->context);
            }
        });
    }

    /**
     * registerTwigParser
     */
    protected function registerTwigParser()
    {
        // Register system Twig environment
        $this->app->singleton('twig.environment', function ($app) {
            $twig = new TwigEnvironment(new TwigLoader, ['auto_reload' => true]);

            TwigExtension::addExtensionToTwig($twig);
            TwigSecurityPolicy::addExtensionToTwig($twig);

            return $twig;
        });

        // Register Twig for mailer
        $this->app->singleton('twig.environment.mailer', function ($app) {
            $twig = new TwigEnvironment(new TwigLoader, ['auto_reload' => true]);

            TwigExtension::addExtensionToTwig($twig);
            TwigSecurityPolicy::addExtensionToTwig($twig);

            $twig->addTokenParser(new \System\Twig\MailPartialTokenParser);
            return $twig;
        });
    }

    /**
     * registerMailLayouts
     */
    public function registerMailLayouts()
    {
        return [
            'default' => 'system::mail.layout-default',
            'system' => 'system::mail.layout-system',
        ];
    }

    /**
     * registerMailPartials
     */
    public function registerMailPartials()
    {
        return [
            'header' => 'system::mail.partial-header',
            'footer' => 'system::mail.partial-footer',
            'button' => 'system::mail.partial-button',
            'panel' => 'system::mail.partial-panel',
            'table' => 'system::mail.partial-table',
            'subcopy' => 'system::mail.partial-subcopy',
            'promotion' => 'system::mail.partial-promotion',
        ];
    }

    /**
     * registerNavigation
     */
    public function registerNavigation()
    {
        return [
            'system' => [
                'label' => 'Settings',
                'icon' => 'icon-cog',
                'iconSvg' => 'modules/system/assets/images/cog-icon.svg',
                'url' => Backend::url('system/settings'),
                'permissions' => [],
                'order' => 1000
            ]
        ];
    }

    /**
     * registerDashboardDatasource
     */
    protected function registerDashboardDatasource()
    {
        $this->callAfterResolving('backend.reports', function($manager) {
            $manager->registerDataSourceClass(
                SystemReportDataSource::class,
                'system::lang.dashboard.report_data_source.data_source_name'
            );
        });
    }

    /**
     * extendBackendNavigation
     */
    protected function extendBackendNavigation()
    {
        // Register the sidebar for the System main menu
        $this->callAfterResolving('backend.menu', function ($manager) {
            $manager->registerContextSidenavPartial(
                'October.System',
                'system',
                '~/modules/system/partials/_system_sidebar.php'
            );
        });

        // Remove the October.System.system main menu item if there is no subpages to display
        Event::listen('backend.menu.extendItems', function ($manager) {
            $systemSettingItems = SettingsManager::instance()->listItems('system');
            $systemMenuItems = $manager->listSideMenuItems('October.System', 'system');

            if (empty($systemSettingItems) && empty($systemMenuItems)) {
                $manager->removeMainMenuItem('October.System', 'system');
            }
        }, -1);
    }

    /**
     * registerReportWidgets
     */
    public function registerReportWidgets()
    {
        return [
            \System\ReportWidgets\Status::class => [
                'label' => 'backend::lang.dashboard.status.widget_title_default',
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
            // Mail
            'mail.templates' => [
                'label' => 'system::lang.permissions.manage_mail_templates',
                'tab' => 'Mail',
                'order' => 300
            ],
            'mail.settings' => [
                'label' => 'system::lang.permissions.manage_mail_settings',
                'tab' => 'Mail',
                'order' => 900
            ],

            // Utilities
            'utilities.logs' => [
                'label' => 'system::lang.permissions.access_logs',
                'tab' => 'Utilities',
                'order' => 400
            ],

            // Settings
            'settings.manage_sites' => !Site::hasFeature() ? null : [
                'label' => 'Manage Sites',
                'tab' => 'Settings',
                'order' => 400
            ]
        ];
    }

    /**
     * registerSettings
     */
    public function registerSettings()
    {
        return [
            'updates' => [
                'label' => 'System Updates',
                'description' => 'Update the system modules and plugins.',
                'category' => SettingsManager::CATEGORY_SYSTEM,
                'icon' => 'icon-download-cloud',
                'url' => Backend::url('system/updates'),
                'permissions' => ['general.backend.perform_updates'],
                'order' => 300
            ],
            'my_updates' => [
                'label' => 'System Updates',
                'description' => 'Update the system modules and plugins.',
                'category' => SettingsManager::CATEGORY_MYSETTINGS,
                'icon' => 'icon-components',
                'url' => Backend::url('system/updates'),
                'permissions' => ['general.backend.perform_updates'],
                'order' => 520,
                'context' => 'mysettings'
            ],
            'sites' => !Site::hasFeature() ? null : [
                'label' => "Site Definitions",
                'description' => 'Manage the websites available for this application.',
                'category' => SettingsManager::CATEGORY_SYSTEM,
                'icon' => 'icon-globe-site',
                'url' => Backend::url('system/sites'),
                'permissions' => ['settings.manage_sites'],
                'order' => 350
            ],
            'mail_templates' => [
                'label' => "Mail Templates",
                'description' => "Modify the mail templates that are sent to users and administrators, manage email layouts.",
                'category' => SettingsManager::CATEGORY_MAIL,
                'icon' => 'icon-mail-messages',
                'url' => Backend::url('system/mailtemplates'),
                'permissions' => ['mail.templates'],
                'order' => 610
            ],
            'mail_settings' => Config::get('backend.force_mail_setting', false) ? [] : [
                'label' => "Mail Configuration",
                'description' => "Manage email configuration.",
                'category' => SettingsManager::CATEGORY_MAIL,
                'icon' => 'icon-mail-settings',
                'class' => \System\Models\MailSetting::class,
                'permissions' => ['mail.settings'],
                'order' => 620
            ],
            'mail_brand_settings' => [
                'label' => "Mail Branding",
                'description' => "Modify the colors and appearance of mail templates.",
                'category' => SettingsManager::CATEGORY_MAIL,
                'icon' => 'icon-mail-branding',
                'url' => Backend::url('system/mailbrandsettings'),
                'permissions' => ['mail.templates'],
                'order' => 630
            ],
            'event_logs' => [
                'label' => "Event Log",
                'description' => "View system log messages with their recorded time and details.",
                'category' => SettingsManager::CATEGORY_LOGS,
                'icon' => 'icon-text-format-ul',
                'url' => Backend::url('system/eventlogs'),
                'permissions' => ['utilities.logs'],
                'order' => 900,
                'keywords' => 'error exception'
            ],
            'request_logs' => [
                'label' => "Request Log",
                'description' => "View bad or redirected requests, such as Page not found (404).",
                'category' => SettingsManager::CATEGORY_LOGS,
                'icon' => 'icon-file',
                'url' => Backend::url('system/requestlogs'),
                'permissions' => ['utilities.logs'],
                'order' => 910,
                'keywords' => '404 error'
            ],
            'log_settings' => [
                'label' => "Log Settings",
                'description' => "Specify which areas should use logging.",
                'category' => SettingsManager::CATEGORY_LOGS,
                'icon' => 'icon-log-settings',
                'class' => \System\Models\LogSetting::class,
                'permissions' => ['system.manage_logs'],
                'order' => 990
            ],
        ];
    }

    /**
     * extendBackendSettings
     */
    protected function extendBackendSettings()
    {
        Event::listen('system.settings.extendItems', function ($manager) {
            \System\Models\LogSetting::filterSettingItems($manager);
        });
    }

    /**
     * registerValidator extends the validator with custom rules
     */
    protected function registerValidator()
    {
        $this->callAfterResolving('validator', function ($validator) {
            // Allowed file extensions, as opposed to mime types.
            // - extensions: png,jpg,txt
            $validator->extend('extensions', function ($attribute, $value, $parameters) {
                $extension = strtolower($value->getClientOriginalExtension());
                return in_array($extension, $parameters);
            });

            $validator->replacer('extensions', function ($message, $attribute, $rule, $parameters) {
                return strtr($message, [':values' => implode(', ', $parameters)]);
            });
        });
    }

    /**
     * applyDatabaseDefaultStringLength allows the database config to specify a max length
     * for VARCHAR. Primarily used by MariaDB (<10.2) and MySQL (<5.7)
     * @todo This should be moved to the core library
     */
    protected function applyDatabaseDefaultStringLength()
    {
        if (Db::getDriverName() !== 'mysql') {
            return;
        }

        $defaultStrLen = Db::getConfig('varcharmax');
        if ($defaultStrLen === null) {
            return;
        }

        Schema::defaultStringLength((int) $defaultStrLen);
    }

    /**
     * extendViewService
     */
    protected function extendViewService()
    {
        $this->callAfterResolving('view', function($view) {
            // Register .htm extension for Twig views
            $view->addExtension('htm', 'twig', function () {
                return new TwigEngine($this->app->make('twig.environment'));
            });

            // Share app name
            $view->share('appName', Config::get('app.name'));
        });
    }

    /**
     * extendMailerService templates and settings override.
     */
    protected function extendMailerService()
    {
        // Override system mailer with mail settings
        if (!Config::get('backend.force_mail_setting', false)) {
            $this->callBeforeResolving('mail.manager', function() {
                if (MailSetting::isConfigured()) {
                    MailSetting::applyConfigValues();
                }

                if (Site::hasFeature('backend_mail_setting')) {
                    MailSetting::enableMultisiteMailer();
                }
            });
        }

        // Override standard Mailer content with template
        Event::listen('mailer.beforeAddContent', function ($mailer, $message, $view, $data, $raw, $plain) {
            return !MailManager::instance()->addContentFromEvent($message, $view, $plain, $raw, $data);
        });
    }
}
