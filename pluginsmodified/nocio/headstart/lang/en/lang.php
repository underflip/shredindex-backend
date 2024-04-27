<?php return [
    'plugin' => [
        'name' => 'Headstart',
        'description' => 'Decoupled GraphQL-driven content management',
    ],
    'settings' => [
        'label' => 'Headstart GraphQL',
        'description' => 'Configure Headstart\'s GraphQL API',
        'general' => 'General',
        'engine' => 'Engine',
        'schema_location' => [
            'label' => 'Schema location',
            'comment' => 'Location relative to the basepath where the schema is stored; defaults to "graphql". 
                          Leave empty to use the activated theme\'s directory.'
        ],
        'headless_section' => 'Headless mode',
        'disable_cms_routes' => [
            'label' => 'Deactivate frontend routes',
            'comment' => 'Disables all CMS theme frontend routes.'
        ],
        'frontend_redirection' => [
            'label' => 'Redirect frontend requests to backend',
            'comment' => 'Enables redirection of frontend routes to backend.'
        ],
        'disable_cms_section' => [
            'label' => 'Deactivate CMS section',
            'comment' => 'Hides the CMS editor menu entry.'
        ],
        'route_uri' => [
            'label' => 'GraphQL endpoint',
            'comment' => 'Route to which the GraphQL server responds. The default route endpoint is "yourdomain.com/graphql".'
        ],
        'route_name' => [
            'label' => 'Route name',
            'comment' => 'Headstart creates a named route for convenient URL generation and redirects. The default route name is "graphql".'
        ],
        'middleware' => [
            'label' => 'Global request middleware',
            'placeholder' => 'e.g. \Barryvdh\Cors\HandleCors',
            'comment' => 'Comma separated list of middleware classes. Beware that middleware defined here runs before the GraphQL execution phase. This means that errors will cause the whole query to abort and return a response that is not spec-compliant. It is preferable to use directives to add middleware to single fields in the schema. Read more about this in the <a target="_blank" href="https://lighthouse-php.com/4.3/security/authentication.html#apply-auth-middleware">docs</a>.'
        ],
        'enable_cache' => [
            'label' => 'Enable Schema Cache',
            'comment' => 'A large part of the Schema generation is parsing the various graph definition into an AST. These operations are pretty expensive so it is recommended to enable caching in production mode.'
        ],
        'batched_queries' => [
            'label' => 'Batched Queries',
            'comment' => 'GraphQL query batching means sending multiple queries to the server in one request. You may set this flag to process/deny batched queries.'
        ]
    ],
    'editor' => [
        'new_title' => 'New graph title',
        'title' => 'Title'
    ],
    'menu' => [
        'schema' => 'Schema',
        'testing' => 'Testing',
        'code' => 'Source',
        'documentation' => 'Directives',
        'graphiql' => 'Test'
    ],
    'permissions' => [
        'tab' => 'Headstart',
        'manage_schema' => 'Manage schema',
        'access_settings' => 'Access settings'
    ],
    'components' => [
        'filter_description' => 'Display all components or only components with existing schema support'
    ],
    'documentation' => [
        'no_data' => 'No documentation found'
    ],
    'graph' => [
        'delete_confirm_multiple' => 'Delete selected graphs?',
        'no_list_records' => 'No graphs found',
        'delete_confirm_single' => 'Delete this graph definition?'
    ],
    'schema' => [
        'manage_schema' => 'Manage GraphQL schema'
    ]
];
