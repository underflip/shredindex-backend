<?php

namespace Nuwave\Lighthouse\Schema\Directives;

use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Nuwave\Lighthouse\Support\Contracts\DefinedDirective;

class HasOneDirective extends RelationDirective implements FieldResolver, DefinedDirective
{
    /**
     * Name of the directive.
     *
     * @return string
     */
    public function name(): string
    {
        return 'hasOne';
    }

    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Corresponds to [the Eloquent relationship HasOne](https://laravel.com/docs/eloquent-relationships#one-to-one).
"""
directive @hasOne(      
  """
  Specify the relationship method name in the model class,
  if it is named different from the field in the schema.
  """
  relation: String
  
  """
  Apply scopes to the underlying query.
  """
  scopes: [String!]
) on FIELD_DEFINITION
SDL;
    }
}
