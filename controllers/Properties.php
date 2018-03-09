<?php namespace Radit\Catalog\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Products Back-end Controller
 */
class Properties extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.RelationController',
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relation.yaml';

    protected $itemFormWidget;

    public $requiredPermissions = ['radit.catalog.access_properties'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Radit.Catalog', 'catalog', 'properties');

        $this->itemFormWidget = $this->createValueFormWidget();

    }

    protected function createValueFormWidget(){
        $config = $this->makeConfig('$/radit/catalog/models/propertieslistvalues/fields.yaml');
        $config->alias = 'valueForm';
        $config->arrayName = 'Value';
        $config->model = new \Radit\Catalog\Models\PropertiesListValues;
        $widget = $this->makeWidget('Backend\Widgets\Form', $config);
        $widget->bindToController();
        return $widget;
    }
}