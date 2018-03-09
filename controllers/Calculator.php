<?php namespace Radit\Catalog\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Coupons Back-end Controller
 */
class Calculator extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Radit.Catalog', 'catalog', 'calculator');
    }
}