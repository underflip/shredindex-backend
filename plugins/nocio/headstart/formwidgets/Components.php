<?php

namespace Nocio\Headstart\FormWidgets;

use Nocio\Headstart\Classes\ComponentHelpers;
use Cms\FormWidgets\Components as CmsComponents;


class Components extends CmsComponents
{

    protected function getComponentName($component)
    {
        return ComponentHelpers::getComponentName($component);
    }

    protected function getComponentDescription($component)
    {
        return ComponentHelpers::getComponentDescription($component);
    }

    protected function getComponentsPropertyConfig($component)
    {
        return ComponentHelpers::getComponentsPropertyConfig($component);
    }

    protected function getComponentPropertyValues($component)
    {
        return ComponentHelpers::getComponentPropertyValues($component);
    }

}