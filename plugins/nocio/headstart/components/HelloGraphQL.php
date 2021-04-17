<?php namespace Nocio\Headstart\Components;

use Cms\Classes\ComponentBase;


class HelloGraphQL extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Hello GraphQL',
            'description' => 'A GraphQL hello world demo',
            'graphql' => true
        ];
    }

    public function resolveHello($root, $args) {
        return 'GraphQL';
    }

}
