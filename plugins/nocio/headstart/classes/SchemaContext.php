<?php

namespace Nocio\Headstart\Classes;

use App;
use Illuminate\Contracts\Auth\Authenticatable;
use Nuwave\Lighthouse\Schema\Context;
use Illuminate\Http\Request;
use Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;


class SchemaContext implements GraphQLContext
{

    public $source;

    /**
     * An instance of the incoming HTTP request.
     *
     * @var \Illuminate\Http\Request
     */
    public $request;

    /**
     * An instance of the currently authenticated user.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public $user;

    /**
     * Create new context.
     *
     * @param Request $request
     * @param $source
     */
    public function __construct(Request $request)
    {
        //parent::__construct($request);

        $this->source = App::make(SchemaSourceProvider::class);

        $this->request = $request;
        $this->user = $request->user();
    }


    public function user(): ?Authenticatable
    {
        return $this->user;
    }

    public function setUser(?Authenticatable $user): void
    {
        $this->user = $user;
    }

    public function request(): ?Request
    {
        return $this->request;
    }
}
