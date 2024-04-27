<?php

namespace Nocio\Headstart\FormWidgets;

use Backend\Classes\WidgetBase;
use Nocio\Headstart\Widgets\AssetList as CmsAssetList;
use Nocio\Headstart\Classes\Schema;
use Nocio\Headstart\Models\Settings;

class AssetList extends CmsAssetList
{

    public function __construct($controller, $alias)
    {
        $this->alias = $alias;
        $this->theme = Schema::load('headstart');
        $this->selectionInputName = 'file';
        $this->assetExtensions = ['.php', '.htm'];

        WidgetBase::__construct($controller, []);

        $this->bindToController();

//        $this->checkUploadPostback();
    }

    protected function loadAssets()
    {
        $this->addCss('/plugins/nocio/headstart/widgets/assetlist/assets/css/assetlist.css', 'core');
        $this->addJs('/plugins/nocio/headstart/widgets/assetlist/assets/js/assetlist.js', 'core');
    }

    protected function getAssetsPath()
    {
        return Settings::getSchemaDirectory('headstart');
    }

    protected function getThemeFileUrl($path)
    {
        return '#';
    }

    protected function validateRequestTheme()
    {
        //
    }

}
