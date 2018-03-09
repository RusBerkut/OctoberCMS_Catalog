<?php namespace Radit\Catalog\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Config;

use Radit\Catalog\Models\Properties;
use Radit\Catalog\Models\PropertiesValues;
use Radit\Catalog\Models\PropertiesListValues;

class ProductEditProperties extends FormWidgetBase{

    public function widgetDetails()
    {
        return [
            'name' => 'Product edit properties',
            'description' => 'Editing properties for product'
        ];
    }

    public function render() {
        $this->prepareVars();
        return $this->makePartial('widget');
    }

    public function prepareVars()
    {

        $propertiesValues = Array();

        IF($this->model->id){

            $this->vars['products_id'] = $this->model->id;
            $propertiesValuesArr = PropertiesValues::where('products_id' , $this->model->id)->get()->toArray();
            IF(!empty($propertiesValuesArr)){
                foreach ($propertiesValuesArr AS $propValue){
                    $propertiesValues[$propValue['properties_id']] = $propValue['value'];
                }
            }
        }

        $this->vars['properties_values'] = $propertiesValues;

        $this->vars['properties'] = Properties::orderBy('sorting', 'asc')->get()->toArray();

        $propertiesListValues = PropertiesListValues::orderBy('sorting', 'asc')->get()->toArray();

        foreach ($propertiesListValues as $propertiesListValue) {
            $this->vars['properties_list_values'][$propertiesListValue['properties_id']][] = $propertiesListValue;
        }
    }

    public function getSaveValue($value)
    {
        return $value;
    }

}