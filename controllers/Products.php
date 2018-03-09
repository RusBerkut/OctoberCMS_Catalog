<?php namespace Radit\Catalog\Controllers;

use BackendMenu;
use Backend\Classes\Controller;
use Radit\Catalog\Models\PropertiesValues;

/**
 * Products Back-end Controller
 */
class Products extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.RelationController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relation.yaml';


    public $requiredPermissions = ['radit.catalog.access_products'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Radit.Catalog', 'catalog', 'products');
    }

    public function formAfterSave($recordId){
        IF(post() && isset($recordId->attributes['id'])){

            $arPropertiesValues = post('Product.properties_values');

            IF( $arPropertiesValues && !empty($arPropertiesValues)){
                foreach ($arPropertiesValues AS $pID => $pValue){

                    PropertiesValues::where('properties_id', $pID)
                        ->where('products_id', $recordId->attributes['id'])
                        ->delete();

                    $oPropertyValue = new PropertiesValues();
                    $oPropertyValue->products_id = $recordId->attributes['id'];
                    $oPropertyValue->properties_id = $pID;
                    $oPropertyValue->value = $pValue['value'];
                    $oPropertyValue->save();
                }
            }
        }
    }
}