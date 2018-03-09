<?php namespace Radit\Catalog\Components;

use App;
use Illuminate\Support\Facades\DB;
use PhpParser\Node\Stmt\Foreach_;
use Request;
use Redirect;
use Response;
use Cms\Classes\Page;
use Cms\Classes\ComponentBase;

use Radit\Catalog\Models\Category;
use Radit\catalog\Models\Product as Product;
use Radit\catalog\Models\Currency as Currency;
use Radit\catalog\Models\Properties as Properties;
use Radit\catalog\Models\PropertiesValues as PropertiesValues;

class ProductsLanding extends ComponentBase
{
    public $products;

    public $currencies;

    public $properties;

    public $category;

    public $product_categories;

    public $productCategoryPage;

    public $currentProductCategorySlug;

    public $pageParam;

    public $categoryPage;

    public $productPage;

    public $productsPerPage = 50;

    public $sortOrder;

    public $request;

    public $productsFilter = [];

    public $ajax_products;

    public $propertiesFilter;

    public $breadcrumbs;

    public function componentDetails()
    {
        return [
            'name' => 'Компонент товаров',
            'description' => 'Выводит список товаров на странице'
        ];
    }

    public function defineProperties()
    {
        return [
            'pageNumber' => [
                'title' => 'rainlab.blog::lang.settings.posts_pagination',
                'description' => 'rainlab.blog::lang.settings.posts_pagination_description',
                'type' => 'string',
                'default' => '{{ :page }}',
            ],
            'categorySlug' => [
                'title' => 'Категория',
                'description' => 'Категория товаров',
                'default' => '{{ :slug }}',
                'type' => 'string'
            ],
            'categoryFilter' => [
                'title' => 'rainlab.blog::lang.settings.posts_filter',
                'description' => 'rainlab.blog::lang.settings.posts_filter_description',
                'type' => 'string',
                'default' => ''
            ],
            'propertiesFilter' => [
                'title' => 'Фильтр свойств',
                'description' => 'Фильтр по свойствам каталога.',
                'type' => 'string',
                'default' => ''
            ],
            'categoryPage' => [
                'title' => 'Страница категории',
                'description' => 'Страница категории',
                'type' => 'dropdown',
                'default' => '{{ :slug }}',
                'group' => 'Products',
            ],
            'productPage' => [
                'title' => 'Страница товара',
                'description' => 'Страница товара',
                'type' => 'dropdown',
                'default' => 'product/:slug',
                'group' => 'Products',
            ],
            'productsPerPage' => [
                'title' => 'Товаров на страницу',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Не верное значение',
                'default' => '50',
                'group' => 'Pagination',
            ],
            'pageParam' => [
                'title' => 'Имя параметра для пагинации',
                'description' => 'Имя параметра для постраничной навигации',
                'type' => 'string',
                'default' => ':page',
                'group' => 'Pagination',
            ],
            'sortOrder' => [
                'title' => 'Сортировка',
                'description' => 'Сортировка товаров',
                'type' => 'dropdown',
                'default' => 'name asc'
            ],
        ];
    }

    public function getProductPageOptions()
    {
        return ['' => '- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getCategoryPageOptions()
    {
        return ['' => '- none -'] + Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getSortOrderOptions()
    {
        return Product::$allowedSortingOptions;
    }

    public function prepareVars()
    {
        $this->productPage = $this->page['productPage'] = $this->property('productPage');
        $this->categoryPage = $this->page['categoryPage'] = $this->property('categoryPage');
        $this->pageParam = $this->page['pageParam'] = $this->paramName('pageNumber');

        $this->currentProductCategorySlug   = $this->property('categorySlug');

        $this->product_categories = $this->page['product_categories'] = $this->loadCategories();
        $this->category = $this->page['category'] = $this->loadCategory();


        $this->catalog_properties = $this->page['catalog_properties'] = $this->getExistsProperties();
        $this->currencies = $this->page['currencies'] = Currency::get()->toArray();
    }

    public function onLoadMore()
    {
        $this->prepareVars();

        $arFilter = [];

        foreach (post() AS $postParam => $postValue) {
            $arFilter[$postParam] = $postValue;
        }

        IF($arFilter['page'] > 0){
            $this->productsPerPage = $this->property('productsPerPage')*$arFilter['page'];
        }


        $this->products = $this->page['products'] = $this->getProductList($arFilter);

        /*
         * Pagination
         */
        $currentPage = 1;
        if ($this->products) {
            $queryArr = [];
            $queryArr['page'] = '';
            $requestUrl = (substr(Request::url(), -1) == '/') ? Request::url() : Request::url() . '/';
            $paginationUrl = $requestUrl . '?' . http_build_query($queryArr);
            if ($currentPage > ($lastPage = $this->products->lastPage()) && $currentPage > 1) {
                return Redirect::to($paginationUrl . $lastPage);
            }
            $this->page['paginationUrl'] = $paginationUrl;
        }
    }

    public function onFilterSet()
    {

        $this->prepareVars();

        $arFilter = [];

        foreach (post() AS $postParam => $postValue) {
            $arFilter[$postParam] = $postValue;
        }

        IF($this->property('propertiesFilter') != '' && $this->property('propertiesFilter') != '[]'){

            $arFilter['page'] = $this->property('pageNumber');
            $arFilterProps = explode(',', $this->property('propertiesFilter'));

            IF(!empty($arFilterProps)){
                foreach ($arFilterProps as $property) {
                    $arProperty = explode(':', $property);
                    $arFilter[$arProperty[0]] = $arProperty[1];
                }
            }
        }


        $this->products = $this->page['products'] = $this->getProductList($arFilter);

        /*
         * Pagination
         */
        $currentPage = trim(input('page'));
        if ($this->products) {
            $queryArr = [];
            $queryArr['page'] = '';
            $requestUrl = (substr(Request::url(), -1) == '/') ? Request::url() : Request::url() . '/';
            $paginationUrl = $requestUrl . '?' . http_build_query($queryArr);
            if ($currentPage > ($lastPage = $this->products->lastPage()) && $currentPage > 1) {
                return Redirect::to($paginationUrl . $lastPage);
            }
            $this->page['paginationUrl'] = $paginationUrl;
        }
    }

    public function onRun()
    {
        $this->prepareVars();
        $arProperties = $this->getExistsProperties();

        $arFilter = [];

        IF($this->property('propertiesFilter') != '' && $this->property('propertiesFilter') != '[]'){

            $arFilter['page'] = $this->property('pageNumber');
            $arFilterProps = explode(',', $this->property('propertiesFilter'));

            IF(!empty($arFilterProps)){
                foreach ($arFilterProps as $property) {
                    $arProperty = explode(':', $property);
                    $arFilter[$arProperty[0]] = $arProperty[1];
                }
            }

            $this->products = $this->page['products'] = $this->getProductList($arFilter);
        }ELSE{
            $this->products = $this->page['products'] = $this->getProducts();
        }

        /*
         * Pagination
         */
        $currentPage = $this->property('pageNumber');
        if ($this->products) {
            $queryArr = [];
            $queryArr['page'] = '';
            $requestUrl = (substr(Request::url(), -1) == '/') ? Request::url() : Request::url() . '/';
            $paginationUrl = $requestUrl . '?' . http_build_query($queryArr);
            if ($currentPage > ($lastPage = $this->products->lastPage()) && $currentPage > 1) {
                return Redirect::to($paginationUrl . $lastPage);
            }
            $this->page['paginationUrl'] = $paginationUrl;
        }
    }

    public function getExistsProperties()
    {
        $arReturn = Array();
        $arProps = Properties::get()->toArray();
        IF ($arProps) {
            foreach ($arProps as $arProp) {
                $arReturn[$arProp['id']] = $arProp;
                $arReturn[$arProp['code']] = $arProp;
            }
        }

        return $arReturn;
    }

    public function getProducts()
    {

        $category = $this->category ? $this->category->id : null;

        IF($category){

            /*Meta H1*/
            $this->page->h1 =  $this->category->meta_h1;


            $this->page->meta_title = $this->category->meta_title;
            $this->page->meta_description = $this->category->meta_description;
            $this->page->meta_keywords = $this->category->meta_keywords;
        }ELSE{
            IF($this->property('categorySlug'))
                $this->controller->run('404');
        }

        /*
         * List all the posts, eager load their categories
         */
        $products = Product::with('categories')->with('properties_values')->listFrontEnd([
            'page' => $this->property('pageNumber'),
            'sorting' => $this->property('sortOrder'),
            'perPage' => $this->productsPerPage,
            'search' => trim(input('search')),
            //'categories' => $this->property('categoryFilter'),
            'category' => $category,
        ]);

        IF(input('page') > 1){
            $this->page->meta_title .= ' - ' . input('page') . ' страница';
            $this->page->meta_description .= ' - ' . input('page') . ' страница';
            $this->page->meta_keywords .= ', ' . input('page') . ' страница';
        }

        /*
         * Add a "url" helper attribute for linking to each product and category
         */
        $products->each(function($product) {
            $product->setUrl($this->productPage, $this->controller);

            $product->categories->each(function($category) {
                $category->setUrl($this->categoryPage, $this->controller);
            });
        });

        return $products;
    }

    public function getProductList($arFilter = [])
    {

        $arProductFilteredIDs = [];
        $arProductFilteredIDs2 = [];
        $dCategory = null;

        IF (!empty($arFilter)) {

            /*Category filter*/
            IF(ISSET($arFilter['category']) && $arFilter['category'] > 0){
                $dCategory = intval($arFilter['category']);
            }

            //print_r($arFilter);

            /*Properties filter*/
            foreach ($arFilter as $key => $value) {
                IF (preg_match('/^PROPERTY_/ie', $key) && $value) {
                    $sPropertyName = substr($key, 9, strlen($key) - 1);

                    IF(is_array($value)){
                        IF(!empty($arProductFilteredIDs)){
                            $arProductFilteredIDs2 += PropertiesValues::whereIn('value', $value)
                                ->whereIn('properties_id', Properties::where('code', $sPropertyName)->lists('id'))
                                ->whereIn('products_id', $arProductFilteredIDs)
                                ->lists('products_id');
                        }ELSE{
                            $arProductFilteredIDs2 += PropertiesValues::whereIn('value', $value)
                                ->whereIn('properties_id', Properties::where('code', $sPropertyName)->lists('id'))
                                ->lists('products_id');
                        }
                    }ELSE{
                        IF(!empty($arProductFilteredIDs)){
                            $arProductFilteredIDs2 += PropertiesValues::where('value', $value)
                                ->whereIn('properties_id', Properties::where('code', $sPropertyName)->lists('id'))
                                ->whereIn('products_id', $arProductFilteredIDs)
                                ->lists('products_id');
                        }ELSE{
                            $arProductFilteredIDs2 += PropertiesValues::where('value', $value)
                                ->whereIn('properties_id', Properties::where('code', $sPropertyName)->lists('id'))
                                ->lists('products_id');
                        }
                    }
                }
            }
        }

        $arIDs = (!empty(array_unique($arProductFilteredIDs)) && !empty(array_unique($arProductFilteredIDs2)))
            ? array_intersect($arProductFilteredIDs, $arProductFilteredIDs2)
            : (!empty($arProductFilteredIDs2))
                ? $arProductFilteredIDs2
                : $arProductFilteredIDs;

        $products = Product::with('categories')->with('properties_values')->listFrontEnd([
            'page' => $arFilter['page'],
            'sort' => $this->property('sortOrder'),
            'perPage' => $this->productsPerPage,
            'search' => trim(input('search')),
            'category' => $dCategory,
            'productsIDs' => $arIDs
        ]);

        IF($dCategory){
            /*Meta H1*/
            $this->page->h1 = ($this->category->meta_h1 != '')
                ? $this->category->meta_h1
                : ($this->category->title != '')
                    ? $this->category->title
                    : $this->category->meta_title;
        }

        /*
         * Add a "url" helper attribute for linking to each product and category
         */
        $products->each(function($product) {
            $product->setUrl($this->productPage, $this->controller);

            $product->categories->each(function($category) {
                $category->setUrl($this->categoryPage, $this->controller);
            });
        });

        return $products;
    }

    protected function loadCategory()
    {

        if (!$slug = $this->property('categorySlug')) {
            return null;
        }

        $category = new Category;

        $category = $category->isClassExtendedWith('RainLab.Translate.Behaviors.TranslatableModel')
            ? $category->transWhere('slug', $slug)
            : $category->where('slug', $slug);

        $category = $category->first();

        return $category ?: null;
    }

    public function loadCategories()
    {
        $categories = Category::orderBy('nest_left')->get();

        if (!$categories) {
            return null;
        }

        $categories->each(function ($category) {
            $category->setUrl($this->categoryPage, $this->controller);
            $category->isActive = $category->slug == $this->currentProductCategorySlug;
            $category->isChildActive = Category::hasActiveChild(
                $category->id,
                $this->property('categorySlug')
            );
            $category->hasChildren = Category::hasChildren($category->slug);
        });

        return $categories;

    }

}