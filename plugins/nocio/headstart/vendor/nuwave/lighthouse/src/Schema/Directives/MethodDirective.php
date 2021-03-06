<?php

namespace Nuwave\Lighthouse\Schema\Directives;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Nuwave\Lighthouse\Support\Contracts\DefinedDirective;

class MethodDirective extends BaseDirective implements FieldResolver, DefinedDirective
{
    /**
     * Name of the directive.
     *
     * @return string
     */
    public function name(): string
    {
        return 'method';
    }

    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Call a method with a given `name` on the class that represents a type to resolve a field.
Use this if the data is not accessible as an attribute (e.g. `$model->myData`).
"""
directive @method(      
  """
  Specify the method of which to fetch the data from.
  """
  name: String
) on FIELD_DEFINITION
SDL;
    }

    /**
     * Resolve the field directive.
     *
     * @param  \Nuwave\Lighthouse\Schema\Values\FieldValue  $fieldValue
     * @return \Nuwave\Lighthouse\Schema\Values\FieldValue
     */
    public function resolveField(FieldValue $fieldValue): FieldValue
    {
        /** @var string $method */
        $method = $this->directiveArgValue(
            'name',
            $this->definitionNode->name->value
        );

        return $fieldValue->setResolver(
            function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($method) {
                return call_user_func([$root, $method], $root, $args, $context, $resolveInfo);
            }
        );
    }
}
