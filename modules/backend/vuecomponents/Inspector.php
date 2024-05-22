<?php namespace Backend\VueComponents;

use Backend\Classes\VueComponentBase;

/**
 * Inspector Vue component
 *
 * @package october\backend
 * @author Alexey Bobkov, Samuel Georges
 */
class Inspector extends VueComponentBase
{
    protected $require = [
        \Backend\VueComponents\Splitter::class,
        \Backend\VueComponents\Tabs::class,
        \Backend\VueComponents\Dropdown::class,
        \Backend\VueComponents\Modal::class,
        \Backend\VueComponents\Autocomplete::class,
        \Backend\VueComponents\LoadingIndicator::class,
    ];

    /**
     * Adds component specific asset files. Use $this->addJs() and $this->addCss()
     * to register new assets to include on the page.
     * The default component script and CSS file are loaded automatically.
     * @return void
     */
    protected function loadAssets()
    {
        $this->addJs('vendor/ajv/ajv.min.js');
        $this->addJsBundle('js/dataschema.js');
        $this->addJsBundle('js/utils.js');
        $this->addJsBundle('js/dataloader.js');
        $this->addJsBundle('js/host.js');
        $this->addJsBundle('js/control.base.js');
        $this->addJsBundle('js/control.table.base.js');
        $this->addJsBundle('js/validatorset.js');
        $this->addJsBundle('js/validator.base.js');
        $this->addJsBundle('js/validator.number.base.js');
        $this->addJsBundle('js/validator.regex.js');
        $this->addJsBundle('js/validator.integer.js');
        $this->addJsBundle('js/validator.required.js');
    }

    protected function registerSubcomponents()
    {
        $this->registerSubcomponent('panel');
        $this->registerSubcomponent('group');
        $this->registerSubcomponent('grouphost');
        $this->registerSubcomponent('controlhost');
        $this->registerSubcomponent('controlhost.row');
        $this->registerSubcomponent('control.text');
        $this->registerSubcomponent('control.dropdown');
        $this->registerSubcomponent('control.checkbox');
        $this->registerSubcomponent('control.table');
        $this->registerSubcomponent('control.table.head');
        $this->registerSubcomponent('control.table.headcell');
        $this->registerSubcomponent('control.table.row');
        $this->registerSubcomponent('control.table.cell');
        $this->registerSubcomponent('control.table.text');
        $this->registerSubcomponent('control.table.dropdown');
        $this->registerSubcomponent('control.object');
        $this->registerSubcomponent('control.set');
        $this->registerSubcomponent('control.dictionary');
        $this->registerSubcomponent('control.objectlist');
        $this->registerSubcomponent('control.objectlist.records');
        $this->registerSubcomponent('control.objectlist.recordtitle');
        $this->registerSubcomponent('control.autocomplete');
        $this->registerSubcomponent('control.mediafinder');
        $this->registerSubcomponent('host.modal');
    }
}
