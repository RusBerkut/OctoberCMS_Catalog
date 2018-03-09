<?php
namespace Radit\Catalog\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Theme;
use Illuminate\Support\Facades\DB;
use System\Models\File AS SystemFiles;

use System\Models\File;
use Radit\Catalog\Models\Product AS ProductModel;
use Radit\Catalog\Models\Category AS CategoryModel;
use Radit\Catalog\Models\Calculator AS CalculatorModel;

class Calculator extends ComponentBase{

    public $calculator;

    public $parameters;

    public $baseCategoryId = 1;

    public $categories;

    public function componentDetails()
    {
        return [
            'name' => 'Калькулятор',
            'description' => 'Калькулятор для лендинга'
        ];
    }

    public function defineProperties()
    {
        return [
            'calculator' => [
                'title' => 'Название калькулятора',
                'description' => 'Название калькулятора',
                'type' => 'dropdown',
            ]
        ];
    }

    public function getCalculatorOptions()
    {
       return CalculatorModel::get()->lists('name', 'id');
    }

    public function onRun()
    {
        $parameters = [];

        $resultPrice = 0;

        $oCalc = CalculatorModel::where('id', $this->property('calculator'))->get()->toArray();
        IF($oCalc){
//            $parameters['calc_options'] = $oCalc[0]['options'];

            foreach ($oCalc[0]['options'] as $option) {
                $parameters['calc_options'][$option['option_code']] = $option;
            }

            //Get Categories
            $arCategories = CategoryModel::orderBy('title')->lists('title', 'id');
            $this->categories = $this->page['categories'] = $arCategories;

            //Get Products
            $arProducts = ProductModel::orderBy('price')
                ->Category($this->baseCategoryId)
                ->with('properties_values')
                ->get()
                ->toArray();

            IF($arProducts){
                foreach ($arProducts as $arProduct) {
                    $parameters['products'][$arProduct['id']] = [
                        'name' => $arProduct['name'],
                        'property_type' => $arProduct['properties_values'][1]['value'],
                        'property_height' => $arProduct['properties_values'][4]['value'],
                        'property_width' => $arProduct['properties_values'][5]['value'],
                    ];
                }
            }

            $arBaseProduct = $arProducts[0];
            $parameters['base_product'] = $arBaseProduct;
            $parameters['base_product_name'] = $arBaseProduct['name'];
            $parameters['base_product_type'] = $arBaseProduct['properties_values'][1]['value'];

            $resultPrice = $arBaseProduct['price'];

        }

        $parameters['resultPrice'] = $resultPrice;

        $this->parameters = $this->page['parameters'] = $parameters;


//        print_r($parameters);
    }

    public function onLoadCalc($pID=false)
    {
        $parameters = [];

        $resultPrice = 0;

        $oCalc = CalculatorModel::where('id', $this->property('calculator'))->get()->toArray();

        foreach ($oCalc[0]['options'] as $option) {
            $parameters['calc_options'][$option['option_code']] = $option;
        }

        //Get Categories
        $arCategories = CategoryModel::orderBy('title')->lists('title', 'id');
        $this->categories = $this->page['categories'] = $arCategories;

        IF($pID){

            //Get Product
            $arProducts = ProductModel::orderBy('price')
                ->where('id', $pID)
                ->with('properties_values')
                ->get()
                ->toArray();



        }ELSE{

            IF($oCalc){

                //Get Products
                $arProducts = ProductModel::orderBy('price')
                    ->Category($this->baseCategoryId)
                    ->with('properties_values')
                    ->get()
                    ->toArray();

                IF($arProducts){
                    foreach ($arProducts as $arProduct) {
                        $parameters['products'][$arProduct['id']] = [
                            'name' => $arProduct['name'],
                            'property_type' => $arProduct['properties_values'][1]['value'],
                            'property_height' => $arProduct['properties_values'][4]['value'],
                            'property_width' => $arProduct['properties_values'][5]['value'],
                        ];
                    }
                }

                $arBaseProduct = $arProducts[0];
                $parameters['base_product'] = $arBaseProduct;
                $parameters['base_product_name'] = $arBaseProduct['name'];
                $parameters['base_product_type'] = $arBaseProduct['properties_values'][1]['value'];

                $resultPrice = $arBaseProduct['price'];

            }

            $parameters['resultPrice'] = $resultPrice;

            $this->parameters = $this->page['parameters'] = $parameters;
        }
    }

    public function onCalculatePrice()
    {
        $inputData = input();
        $parameters['debug_data'] = $inputData;
        $parameters['resultPrice'] = 85001.0000;
        $this->parameters = $this->page['parameters'] = $parameters;
    }

}