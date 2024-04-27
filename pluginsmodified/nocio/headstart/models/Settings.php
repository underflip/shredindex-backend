<?php namespace Nocio\Headstart\Models;

use Cms\Classes\Theme;
use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'nocio_headstart_settings';

    public $settingsFields = 'fields.yaml';

    public static function getSchemaPath($append = '')
    {
        if (!$path = trim(static::get('schema_location', 'graphql'))) {
            return join_paths('themes/' . Theme::getActiveThemeCode(), $append);
        }

        return join_paths($path, $append);
    }

    public static function getSchemaDirectory($append = '')
    {
        return base_path(static::getSchemaPath($append));
    }
}
