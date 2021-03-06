<?php namespace Radit\Catalog\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Currencies Back-end Controller
 */
class Currencies extends Controller
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

        BackendMenu::setContext('Radit.Catalog', 'catalog', 'currencies');
    }
}