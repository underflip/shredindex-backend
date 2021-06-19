<?php

namespace Underflip\Resorts\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Behaviors\ReorderController;
use Backend\Classes\Controller;
use BackendMenu;
use System\Classes\SettingsManager;

/**
 * A controller to handle create, update and delete of Supporters, even thought
 * {@see Settings} renders its list
 */
class TeamMembers extends Controller
{
    /**
     * @var array Extensions implemented by this controller.
     */
    public $implement = [
        ListController::class,
        FormController::class,
        ReorderController::class,
    ];

    /**
     * @var array
     */
    public $listConfig = 'config_list.yaml';

    /**
     * @var array
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var string
     */
    public $reorderConfig = 'config_reorder.yaml';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->makeLists();

        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('October.System', 'shredindex_team_members');
    }
}
