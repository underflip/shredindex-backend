<?php


namespace Nocio\Headstart\Classes;

use Cms\Classes\Asset as CmsAsset;
use October\Rain\Extension\Extendable;


class Asset extends CmsAsset
{
    protected $dirName = '';

    public function __construct(Schema $schema)
    {
        $this->theme = $schema;

        $this->allowedExtensions = ['php'];

        Extendable::__construct();
    }

}
