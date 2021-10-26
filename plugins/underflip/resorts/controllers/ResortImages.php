<?php

namespace Underflip\Resorts\Controllers;

use Backend\Behaviors\FormController;
use Backend\Behaviors\ListController;
use Backend\Behaviors\ReorderController;
use Backend\Classes\Controller;
use BackendMenu;

/**
 * A controller to handle create, update and delete of Resort Images
 * {@see Settings} renders its list
 */
class ResortImages extends Controller
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
    public $listConfig = 'resort_images_list.yaml';

    /**
     * @var array
     */
    public $formConfig = 'resort_images_form.yaml';

    /**
     * @var string
     */
    public $reorderConfig = 'resort_images_reorder.yaml';

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->makeLists();

        BackendMenu::setContext('Underflip.Resorts', 'resorts', 'resort', 'resort_images');
    }
}
