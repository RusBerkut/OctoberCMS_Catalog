<?php namespace Radit\Catalog\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Vendors Back-end Controller
 */
class Vendors extends Controller
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

        BackendMenu::setContext('Radit.Catalog', 'catalog', 'vendors');
    }
}