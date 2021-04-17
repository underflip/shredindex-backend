# Headstart

A GraphQL engine for api-driven content management. Headstart allows you to compose powerful content APIs the October-way.

#### The best of two worlds

Use October while leveraging the flexibility of a headless architecture. Serve multiple channels and platforms, innovate with the latest frontend technologies and scale easily with your demands.

- Expose CMS content through an API [that does not limit how it is consumed](https://pantheon.io/decoupled-cms).
- Benefit from [GraphQL](https://graphql.org/). Query only what you need and transparently receive in one request. Strongly typed and introspective.
- All while keeping what makes OctoberCMS great. Take advantage of the OctoberCMS ecosystem and integrate plugins and components per drag and drop.

#### Features

- Schema first. Write native Schema Definition Language to describe your CMS data. Use Laravel-optimized directives to spare the boilerplate and start querying
- Write the schema and resolver code directly in the CMS backend or your favorite editor, completely manageable with Content Version Systems like Git or SVN.
- Test your API with the integrated GraphiQL client
- Take advantage of existing Schema templates and plugins to compose a fully fledged API in a few clicks.


Make your OctoberCMS api-driven

==

GraphQL is a query language for APIs and a runtime for fulfilling those queries with your existing data. It comes with its own type system that you can use to define the schema of your API. [Learn more about GraphQL](https://howtographql.com/).

Headstart allows you to implement the schema in October just like you implement themes. In fact, the API editor tries to follow October principles as close as possible. Let's have a look at an example: Creating an API for Rainlab's Blog plugin.

The whole process of creating your API can be described in three steps:

**1. Define the form of your data using GraphQL's Schema Definition language**

The types required for our Blog API might look like this:

```graphql
extend type Query {
    blogPost(id: ID!): BlogPost
    blogPosts: [BlogPost]
}

type BlogPost {
    title: String
    content: String
}
```

The example blog schema defines two queries, ``blogPost`` and ``blogPosts`` that return a single blog post and a list of all blog posts respectively.

**2. Use pre-built directives to define how response data should be composed.**

Rather than implementing the ``blogPost`` model query, we can use the built-in ``@find`` directive to directly express the response in the schema. All available directives are documented under the 'Directives' tab in the editor. 

```graphql
extend type Query {
    blogPost(id: ID!): BlogPost @find(model: "Rainlab\\Blog\\Models\\Post")
}
```

The ``find`` directive resolves the blogPost query using the ``find`` method of the specified model. We are now able to query and retrieve blog posts by their ID.

**3. Write custom resolvers for fields that don't use a directive. Resolvers are simply PHP functions that return the response for a given field.**

Instead of using directives, you can also implement the query response as a PHP resolver function. The function can be specified under the 'Resolvers' tab of the editor and must be named ``resolve<FieldName>``, for example:

```php
use Rainlab\Blog\Models\Blog;

function resolveBlogPost($root, $args) {
   return Blog::find($args['id']);
}
```

Resolvers are always called with the same 4 arguments:

```php
use GraphQL\Type\Definition\ResolveInfo;
use Nocio\Headstart\Classes\SchemaContext;

function ($rootValue, array $args, SchemaContext $context, ResolveInfo $resolveInfo)
```

- `$rootValue`: The result that was returned from the parent field. When resolving a field that sits on one of the root types (`Query`, `Mutation`) this is `null`.
- `array $args`: The arguments that were passed into the field. For example, for a field call like `user(name: "Bob")` it would be `['name' => 'Bob']`
- `SchemaContext $context`: Arbitrary data that is shared between all fields of a single query.
- `ResolveInfo $resolveInfo`: Information about the query itself, such as the execution state, the field name, path to the field from the root, and more.

The return value of the resolver must fit the return type defined for the corresponding field from the schema. 

**Testing your API**

The API becomes available under the ``/graphql`` endpoint. To test it, you can use the integrated GraphiQL client.

**Using templates**

Rather than starting from scratch you can use pre-defined schema definitions to rapidly compose your API. To create from a template, click ``Add > From template`` and select a suitable template. All templates are available on [GitHub](https://github.com/nocio/headstart) and contributions are welcome.

**Managing your schema**

Just like a theme, your schema is stored as a directory of text files that can be easily version controlled and managed. By default, the schema lives under ``$OCTOBER_ROOT/graphql/headstart``. However, you are free to adjust the location in the backend settings. Headstart supports the activated theme directory as a storage location. This is useful when you want to ship the API directly with the frontend theme that consumes it.

**Custom types**

Headstart supports advanced use cases like custom directives as described in the [Lighthouse documentation](https://lighthouse-php.com/4.3/custom-directives/getting-started.html). To implement custom Queries, Mutations, Subscriptions, Interfaces, Unions, Scalars or directives, place the corresponding classes under the namespace ``Headstart\<type>``. 

**Headless mode**

Headstart comes with options to disable the default CMS frontend routes to use October in an headless fashion. You can find the headless options in the plugins backend settings.

### Support

Questions? Feedback? Feature requests? Please open an issue on [GitHub](https://github.com/nocio/headstart/issues).

### Acknowledgements

The Headstart plugin wouldn't be possible without the excellent [Lighthouse GrapQL Server for Laravel](https://lighthouse-php.com).

---

**Upgrade to version 1.1.0**

Headstart 1.1.0 brings the latest version of Lighthouse GraphQL 4.3. As a result, PHP 7.1 or higher is required. Please ensure that you use PHP 7.1 or later before upgrading. If you are using the composer installation mode, make sure the composer.json reflects this requirement.

Prior to this version, the Headstart schema lived under ``$OCTOBER_ROOT/schema/default``. In version 1.1, the default location changes to ``$OCTOBER_ROOT/graphql/headstart`` to provide greater namespace consistency. Please move any existing schema code to the new location. Alternatively, you can use the backend settings to adjust the path to the original (or any other) location.
