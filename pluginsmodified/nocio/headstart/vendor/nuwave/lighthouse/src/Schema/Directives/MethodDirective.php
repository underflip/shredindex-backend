<?php declare(strict_types=1);

namespace Nuwave\Lighthouse\Schema\Directives;

use GraphQL\Language\AST\FieldDefinitionNode;
use GraphQL\Type\Definition\ResolveInfo;
use Illuminate\Support\Facades\Log;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;

class MethodDirective extends BaseDirective implements FieldResolver
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
"""
Resolve a field by calling a method on the parent object.

Use this if the data is not accessible through simple property access or if you
want to pass argument to the method.
"""
directive @method(
  """
  Specify the method of which to fetch the data from.
  Defaults to the name of the field if not given.
  """
  name: String
) on FIELD_DEFINITION
GRAPHQL;
    }

    public function resolveField(FieldValue $fieldValue): callable
    {
        /** @var string $method */
        $method = $this->directiveArgValue(
            'name',
            $this->definitionNode->name->value
        );

        return function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($method) {
            return call_user_func([$root, $method], $root, $args, $context, $resolveInfo);
        };
    }
}
