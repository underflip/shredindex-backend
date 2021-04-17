<?php

namespace Nocio\Headstart\Classes;

use Illuminate\Http\Request;
use Nuwave\Lighthouse\Support\Contracts\CreatesContext as LighthouseCreatesContext;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;


class CreatesContext implements LighthouseCreatesContext
{

    /**
     * Generate GraphQL context.
     *
     * @param Request $request
     *
     * @return GraphQLContext
     */
    public function generate(Request $request): GraphQLContext
    {
        return new SchemaContext($request);
    }
}