<?php namespace Radit\catalog\Components;

use Input;
use Validator;
use Cms\Classes\ComponentBase;

use Radit\Catalog\Models\Product as ProductModel;
use Radit\catalog\Models\Currency as CurrencyModel;
use Radit\catalog\Models\Properties as PropertiesModel;
use Radit\catalog\Models\PropertiesListValues;
use Radit\Reviews\Models\Reviews;
use PolloZen\SimpleGallery\Models\Gallery as GalleryModel;
use Radit\Catalog\Models\Category;

class Product extends ComponentBase
{
    public $product;

    public $product_properties;

    public $currencies;

    public $reviews;

    public $gallery;


    public function componentDetails()
    {
        return [
            'name'        => 'Товар детально',
            'description' => 'Выводит подробную информацию о товаре'
        ];
    }
    public function defineProperties()
    {
        return [
            'slug' => [
                'title'       => 'Slug',
                'description' => 'Product slug',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],
            'idGallery' => [
                'title'         => 'Наши работы',
                'description'   => 'Галерея наших работ',
                'type'          => 'dropdown',
                'showExternalParam' => false
            ],
        ];
    }

    public function getidGalleryOptions(){
        return array(0 => 'Using gallery slug') + GalleryModel::select('slug','name')->orderBy('name')->get()->lists('name','slug');
    }

    public function onRun()
    {
        $this->prepareVars();
        $product = $this->loadProduct();
        $existsProperties = $this->getExistsProperties();
        $propertiesListValues = PropertiesListValues::lists('value', 'id');
        $categoryID = false;
        $arProperties = [];

        if (!$product) {
            $this->setStatusCode(404);
            return $this->controller->run('404');
        }

        foreach ($product->categories as $category) {
            $categoryID = $category->id;
        }

        IF($categoryID){
            $category = $this->loadCategory($categoryID);
        }

        foreach ($product->properties_values as $properties_value) {
            $arProperties[$properties_value['properties_id']] = $properties_value['value'];
        }

//        print_r($arProperties);

        $reviews = $this->getReviews();
        $gallery = $this->getGallery();

        $this->product = $this->page['product'] = $product;
        $this->reviews = $this->page['reviews'] = $reviews;
        $this->gallery = $this->page['gallery'] = $gallery;

        /*H1*/
        $this->page->h1 = ($category)
            ? str_replace('ые', 'ая', $category[0]['title']) . ' '
            : ''; //Category

        $this->page->h1 .= 'бетонная лестница ';

        $this->page->h1 .= (!empty($arProperties) && $arProperties[3] > 0)
            ? mb_strtolower($propertiesListValues[$arProperties[3]]) . ' '
            : ''; //TURN_TYPE

        $this->page->h1 .= (!empty($arProperties))
            ? mb_strtolower($arProperties[8])
            : ''; //CONSTRUCTION_TYPE

        /*Title*/
        $this->page->meta_title = ($category)
            ? str_replace('ые', 'ая', $category[0]['title']) . ' '
            : ''; //Category

        $this->page->meta_title .= 'бетонная лестница ';

        $this->page->meta_title .= (!empty($arProperties) && $arProperties[3] > 0)
            ? mb_strtolower($propertiesListValues[$arProperties[3]]) . ' '
            : ''; //TURN_TYPE

        $this->page->meta_title .= (!empty($arProperties))
            ? mb_strtolower($arProperties[8]) . ' '
            : ''; //CONSTRUCTION_TYPE

        $this->page->meta_title .= (!empty($arProperties))
            ? 'от ' . number_format(round($product->price), 0, '.', ' ') . ' ₽ '
            : '';

        $this->page->meta_title .= (!empty($arProperties))
            ? 'и изготовление за ' . $arProperties[1] . ' ' . $this->declination($arProperties[1], ['день','дня','дней'])
            : '';

        /*Description*/
        $this->page->meta_description = 'Лучшее предложения от Строителей Ступенек - ';

        $this->page->meta_description .= ($category)
            ? str_replace('ые', 'ая', $category[0]['title']) . ' '
            : ''; //Category

        $this->page->meta_description .= 'бетонная лестница ';

        $this->page->meta_description .= (!empty($arProperties) && $arProperties[3] > 0)
            ? rtrim(mb_strtolower($propertiesListValues[$arProperties[3]])) . ' '
            : ''; //TURN_TYPE

        $this->page->meta_description .= (!empty($arProperties))
            ? rtrim(mb_strtolower($arProperties[8])) . ' '
            : ''; //CONSTRUCTION_TYPE

        $this->page->meta_description .= 'по индивидуальному проекту и гарантией 30 лет. Опытные мастера с гражданством РФ. Бесплатный замер ☎ +7 (499) 322-22-98';

        //$this->page->title = ($product->meta_title != '') ? $product->meta_title : $product->name;
        //$this->page->meta_title = ($product->meta_title != '') ? $product->meta_title : $product->name;
        //$this->page->meta_description = ($product->meta_description != '') ? $product->meta_description : strip_tags($product->description);
    }

    protected function loadProduct()
    {
        $slug = $this->property('slug');
        $product = ProductModel::whereSlug($slug)
            ->whereIsActive(1)
            ->with('categories')
            ->with('properties_values')
            ->first();

        return $product;
    }

    public function getReviews($limit=2)
    {
        IF($limit <= 0)
            return [];

        return Reviews::take($limit)->get()->toArray();
    }

    public function getExistsProperties()
    {
        $arReturn = [];
        $arProps = PropertiesModel::get()->toArray();
        IF ($arProps) {
            foreach ($arProps as $arProp) {
                $arReturn[$arProp['id']] = $arProp;
                $arReturn[$arProp['code']] = $arProp;
            }
        }

        return $arReturn;
    }

    public function prepareVars()
    {

        $this->product_properties = $this->page['product_properties'] = $this->getExistsProperties();
        $this->currencies = $this->page['currencies'] = CurrencyModel::get()->toArray();

        $this->addCss('/plugins/pollozen/simplegallery/assets/css/owl.carousel.min.css');
        $this->addCss('/plugins/pollozen/simplegallery/assets/css/owl.theme.default.min.css');
        $this->addJs('/plugins/pollozen/simplegallery/assets/js/owl.awesome.carousel.min.js');
        $this->addJs('/plugins/pollozen/simplegallery/assets/js/pz.js');
    }

    public function getGallery($limit=8){
        $slug  = ($this->property('idGallery') == '0') ? false : $this->property('idGallery');

        IF($slug == false)
            return [];

        $query = GalleryModel::where('slug',$slug);
        $gallery = $query->first();

        return $gallery;
    }

    protected function loadCategory($id=false)
    {

        if (!$id)
            return [];

        $category = new Category;
        $category = $category->where('id', $id);
        $category = $category->get()->toArray();

        return $category ?: null;
    }

    public function declination($number, $suffix = [])
    {
        $keys = array(2, 0, 1, 1, 1, 2);
        $mod = $number % 100;
        $suffix_key = ($mod > 7 && $mod < 20) ? 2: $keys[min($mod % 10, 5)];
        return $suffix[$suffix_key];
    }

}