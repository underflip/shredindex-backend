<?php namespace Underflip\Resorts\Controllers;

use BackendMenu;
use Backend\Widgets\Form;
use Backend\Classes\Controller;
use Underflip\Resorts\Models\Resort;
use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Behaviors\RelationController;

class Resorts extends Controller
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
    public $listConfig = 'resorts_list.yaml';

    /**
     * @var string
     */
    public $formConfig = 'resorts_form.yaml';

    /**
     * @var string
     */
    public $relationConfig = 'resorts_relation.yaml';

    /**
     * Resorts constructor
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Underflip.Resorts', 'resorts', 'resorts');
    }
}
