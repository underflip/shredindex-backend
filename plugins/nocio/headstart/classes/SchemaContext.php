<?php

namespace Nocio\Headstart\Classes;

use App;
use Nuwave\Lighthouse\Schema\Context;
use Illuminate\Http\Request;
use Nuwave\Lighthouse\Schema\Source\SchemaSourceProvider;


class SchemaContext extends Context
{

    public $source;

    /**
     * Create new context.
     *
     * @param Request $request
     * @param $source
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);

        $this->source = App::make(SchemaSourceProvider::class);
    }


}