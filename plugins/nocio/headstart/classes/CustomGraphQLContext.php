<?php

namespace Nocio\Headstart\Classes;

use Illuminate\Contracts\Auth\Authenticatable;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Illuminate\Http\Request;

class CustomGraphQLContext implements GraphQLContext
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    // Implement any methods or properties required by the GraphQLContext interface
    // For example, you might implement methods to retrieve user authentication status or request data
    public function user(): ?Authenticatable
    {
        // TODO: Implement user() method.
    }

    public function setUser(?Authenticatable $user): void
    {
        // TODO: Implement setUser() method.
    }

    public function request(): ?Request
    {
        return $this->request;
    }
}
