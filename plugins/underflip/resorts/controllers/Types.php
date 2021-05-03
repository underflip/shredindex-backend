<?php

namespace Underflip\Resorts\Controllers;

use Backend\Behaviors\RelationController;
use BackendMenu;
use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Classes\Controller;

/**
 * The Types CMS Controller
 */
class Types extends Controller
{
    /**
     * @var array
     */
    public $implement = [
        ListController::class,
        FormController::class,
        RelationController::class,
    ];

    /**
     * @var string
     */
    public $listConfig = 'types_list.yaml';

    /**
     * @var string
     */
    public $formConfig = 'types_form.yaml';

    /**
     * @var string
     */
    public $relationConfig = 'types_relation.yaml';

    /**
     * Types constructor
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Underflip.Resorts', 'resorts', 'types');
    }
}
