<?php

namespace Nocio\Headstart\GraphQL\Directives;

use GraphQL\Type\Definition\ResolveInfo;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\FieldResolver;
use Nocio\Headstart\Classes\GraphController;


class ComponentDirective extends BaseDirective implements FieldResolver
{

    /**
     * Name of the directive.
     *
     * @return string
     */
    public function name(): string
    {
        return 'component';
    }

    /**
     * Resolve the field directive.
     *
     * @param FieldValue $fieldValue
     *
     * @return FieldValue
     */
    public function resolveField(FieldValue $fieldValue): FieldValue
    {
        $alias = $this->directiveArgValue('alias');
        $methodName = $this->directiveArgValue('method');

        return $fieldValue->setResolver(
            function ($root, array $args, $context, ResolveInfo $resolveInfo) use ($fieldValue, $alias, $methodName) {
                /* @var $graphObj \Nocio\Headstart\Classes\Graph */
                $graphObj = $context->source->findGraph($resolveInfo->path[0], true);
                $controller = new GraphController($graphObj, $args);
                $alias = empty($alias) ? $fieldValue->getFieldName() : $alias;
                $component = $controller->component($alias);

                if (is_null($component)) {
                    throw new \Exception("Component '" . $alias .
                        "' not found. Did you specify the correct alias (@component(alias: ...))?");
                }

                $resolveMethod = $methodName ? $methodName : 'resolve' . studly_case($fieldValue->getFieldName());
                if (method_exists($component, $resolveMethod)) {
                    return $component->$resolveMethod($root, $args, $context, $resolveInfo);
                }

                // if no resolve method can be found at the component, we return the component object itself
                return $component;
            }
        );
    }

}