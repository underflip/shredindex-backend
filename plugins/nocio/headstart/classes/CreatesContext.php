<?php

namespace Nocio\Headstart\Classes;

use GraphQL\Executor\ExecutionContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Nuwave\Lighthouse\Support\Contracts\CreatesContext as LighthouseCreatesContext;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;


class CreatesContext implements LighthouseCreatesContext
{
    public function generate(?Request $request): GraphQLContext
    {
        //return new CustomGraphQLContext($request);
        Log::info('generate');
        return new SchemaContext($request);
    }

}
