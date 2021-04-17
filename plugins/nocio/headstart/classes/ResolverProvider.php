<?php namespace Nocio\Headstart\Classes;

use Closure;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use GraphQL\Type\Definition\ResolveInfo;
use Cms\Classes\CodeParser;
use Nuwave\Lighthouse\Schema\ResolverProvider as LighthouseResolverProvider;


class ResolverProvider extends LighthouseResolverProvider {

    /**
     * Provide a field resolver in case no resolver directive is defined for a field.
     *
     * @param  \Nuwave\Lighthouse\Schema\Values\FieldValue  $fieldValue
     * @return \Closure
     */
    public function provideResolver(FieldValue $fieldValue): Closure
    {
        return function ($root, array $args, $context, ResolveInfo $resolveInfo) use ($fieldValue) {
            $fieldName = $fieldValue->getFieldName();
            // use local graph resolver in code section if existent
            if ($graphObj = $context->source->findGraph($fieldName)) {
                /* @var $graphObj \Nocio\Headstart\Classes\Graph */
                $parser = new CodeParser($graphObj);
                $codeObj = $parser->source($graphObj, null, new GraphController($graphObj, $args));
                $resolveMethod = 'resolve' . studly_case($fieldName);

                if (method_exists($codeObj, $resolveMethod)) {
                    return $codeObj->$resolveMethod($root, $args, $context, $resolveInfo);
                }
            }

            // default resolver
            return parent::provideResolver($fieldValue)($root, $args, $context, $resolveInfo);
        };
    }

}
