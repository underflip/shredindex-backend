<?php

namespace Underflip\Resorts\Models;

use Model;
use System\Behaviors\SettingsModel;

/**
 * Global settings
 *
 * @property string copyright_message
 *
 * @mixin SettingsModel
 */
class Settings extends Model
{
    /**
     * @var array
     */
    public $implement = ['System.Behaviors.SettingsModel'];

    /**
     * @var string
     */
    public $settingsCode = 'underflip_resorts_settings';

    /**
     * @var string
     */
    public $settingsFields = 'fields.yaml';
}
