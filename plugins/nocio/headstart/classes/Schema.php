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
    public function getPath($dirName = null): string
    {
        if (!$dirName) {
            $dirName = $this->getDirName();
        }

        return Settings::getSchemaDirectory() . '/' . $dirName;
    }


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
    public static function getActiveThemeCode(): ?string
    {
        return 'headstart';
    }

}
