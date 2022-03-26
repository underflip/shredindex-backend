<?php

namespace Nocio\Headstart\FormWidgets;

use Backend\Classes\WidgetBase;
use Cms\Widgets\AssetList as CmsAssetList;
use Nocio\Headstart\Classes\Schema;
use October\Rain\Filesystem\Definitions as FileDefinitions;
use Nocio\Headstart\Models\Settings;
use Url;

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
        $this->addCss('/modules/cms/widgets/assetlist/assets/css/assetlist.css', 'core');
        $this->addJs('/modules/cms/widgets/assetlist/assets/js/assetlist.js', 'core');
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
