<?php namespace Nocio\Headstart\Classes;

use Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider;
use Nuwave\Lighthouse\Support\Contracts\CreatesContext;
use Nuwave\Lighthouse\Support\Contracts\ProvidesResolver;
use Nocio\Headstart\Classes\ResolverProvider as HeadstartProvidesResolver;
use Nocio\Headstart\Classes\SchemaSourceProvider as HeadstartSchemaSourceProvider;
use Nocio\Headstart\Classes\CreatesContext as HeadstartCreatesContext;
use October\Rain\Support\ServiceProvider;

class HeadstartServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->bind(ProvidesResolver::class, HeadstartProvidesResolver::class);
        $this->app->singleton(CreatesContext::class, HeadstartCreatesContext::class);
        $this->app->singleton(SchemaSourceProvider::class, HeadstartSchemaSourceProvider::class);
    }
}
