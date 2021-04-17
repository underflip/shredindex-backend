<?php

namespace Nocio\Headstart\Classes;

use Cms\Classes\Theme;
use Nocio\Headstart\Models\Settings;


class Schema extends Theme
{

    /**
     * Returns the absolute theme path.
     * @param  string $dirName Optional theme directory. Defaults to $this->getDirName()
     * @return string
     */
    public function getPath($dirName = null)
    {
        if (!$dirName) {
            $dirName = $this->getDirName();
        }

        return Settings::getSchemaDirectory() . '/' . $dirName;
    }

    /**
     * Returns a list of graphs in the template.
     * @param boolean $skipCache Indicates if the pages should be reloaded from the disk bypassing the cache.
     * @return array Returns an array of Nocio\Headstart\Classes\Graph objects.
     */
    public function listGraphs($skipCache = false)
    {
        return Graph::listInTheme($this, $skipCache);
    }

    /**
     * Returns the active theme code.
     *
     * @return string
     * If the theme doesn't exist, returns null.
     */
    public static function getActiveThemeCode()
    {
        return 'headstart';
    }

}
