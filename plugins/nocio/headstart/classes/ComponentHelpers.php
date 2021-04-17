<?php namespace Nocio\Headstart\Classes;

use Cms\Classes\ComponentHelpers as CmsComponentsHelper;

class ComponentHelpers extends CmsComponentsHelper
{

    /**
     * @inheritdoc
     */
    public static function getComponentsPropertyConfig($component, $addAliasProperty = true, $returnArray = false) {
        $result = parent::getComponentsPropertyConfig($component, $addAliasProperty, true);

        // filter no-graphql properties
        $result = array_values(array_filter($result, function($config) {
            return array_get($config, 'graphql', null) !== false;
        }));

        if ($returnArray)
            return $result;

        return json_encode($result);
    }

}