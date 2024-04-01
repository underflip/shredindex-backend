<?php namespace Nocio\Headstart;

use Illuminate\Routing\RouteCollection;
use System\Classes\PluginBase;

use App;
use Config;
use Route;
use Event;
use Request;
use Response;
use View;
use Redirect;
use Illuminate\Foundation\AliasLoader;
use Nocio\Headstart\Models\Settings;
use Nocio\Headstart\Classes\HeadstartServiceProvider;
use Backend;
use Lang;

class Plugin extends PluginBase
{
    public function registerComponents()
    {
        return [
            'Nocio\Headstart\Components\HelloGraphQL' => 'helloGraphQL',
        ];
    }



    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'nocio.headstart::lang.settings.label',
                'description' => 'nocio.headstart::lang.settings.description',
                'category'    => 'system::lang.system.categories.cms',
                'icon'        => 'icon-compress',
                'class'       => 'Nocio\Headstart\Models\Settings',
                'order'       => 1000,
                'keywords'    => 'graphql headstart api',
                'permissions' => ['nocio.headstart.access_settings']
            ]
        ];
    }


    public function boot()
    {
        // register schema folder as name space
        App::make('October\Rain\Support\ClassLoader')->addDirectories(Settings::getSchemaPath());

        $this->app['Illuminate\Contracts\Http\Kernel']
             ->pushMiddleware('Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse');

        $this->setHeadlessOptions();

        $this->bootPackages();

        App::register(HeadstartServiceProvider::class);

        foreach (glob(Settings::getSchemaDirectory('headstart/boot/*.php')) as $file) {
            include_once $file;
        }
    }

    public function setHeadlessOptions()
    {
        if (Settings::get('disable_cms_routes', false)) {
            Event::listen('cms.route', function () {
                // clear default CMS routes
                $routes = new RouteCollection();
                foreach (collect(Route::getRoutes()->getRoutes())->reject(function ($value, $key) {
                    return is_string($value->action['uses'])
                           && is_a($value->getController(), \Cms\Classes\CmsController::class);
                })->all() as $route) {
                    $routes->add($route);
                }

                Route::setRoutes($routes);

                // replace frontend routes with 404 page or redirection
                Route::any('{slug}', function () {
                    if (Settings::get('frontend_redirection')) {
                        return Redirect::to(Config::get('cms.backendUri', 'backend'));
                    } else {
                        return Response::make(View::make('cms::404'), 404);
                    }
                })->where('slug', '(.*)?')->middleware('web');
            });
        } else {
            if (Settings::get('frontend_redirection')) {
                Route::any('/', function () {
                    return Redirect::to(Config::get('cms.backendUri', 'backend'));
                });
            }
        }

        if (Settings::get('disable_cms_section', false)) {
            Event::listen('backend.menu.extendItems', function ($manager) {
                $manager->removeMainMenuItem('October.Cms', 'cms');
            });
        }
    }

    /**
     * Boots (configures and registers) any packages found within this plugin's packages.load configuration value
     *
     * @see https://luketowers.ca/blog/how-to-use-laravel-packages-in-october-plugins
     * @author Luke Towers <octobercms@luketowers.ca>
     */
    public function bootPackages()
    {
        // Get the namespace of the current plugin to use in accessing the Config of the plugin
        $pluginNamespace = str_replace('\\', '.', strtolower(__NAMESPACE__));

        // Instantiate the AliasLoader for any aliases that will be loaded
        $aliasLoader = AliasLoader::getInstance();

        // Get the packages to boot
        $packages = Config::get($pluginNamespace . '::packages');

        // Boot each package
        foreach ($packages as $name => $options) {
            // Setup the configuration for the package, pulling from this plugin's config
            if (!empty($options['config']) && !empty($options['config_namespace'])) {
                Config::set($options['config_namespace'], $options['config']);
            }

            // Register any Service Providers for the package
            if (!empty($options['providers'])) {
                foreach ($options['providers'] as $provider) {
                    App::register($provider);
                }
            }

            // Register any Aliases for the package
            if (!empty($options['aliases'])) {
                foreach ($options['aliases'] as $alias => $path) {
                    $aliasLoader->alias($alias, $path);
                }
            }
        }

        return $packages;
    }
}
