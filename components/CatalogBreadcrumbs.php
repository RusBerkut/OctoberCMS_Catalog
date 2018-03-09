<?php namespace Radit\catalog\Components;

use Request;
use Cms\Classes\Theme;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;

use Radit\Catalog\Models\Category as CategoryModel;
use Radit\Catalog\Models\Category;
use Radit\Catalog\Models\Product as ProductModel;

class CatalogBreadcrumbs extends ComponentBase{

    public $currentPageType;

    public $currentCategory;

    public $breadcrumbs = [];

    public function componentDetails()
    {
        return [
            'name'        => 'Хлебные крошки',
            'description' => 'Хлебные крошки для каталога.'
        ];
    }

    public function defineProperties()
    {
        return [
            'currentType' => [
                'title'       => 'Тип страницы',
                'description' => 'Тип текущей страницы',
                'type'        => 'dropdown',
                'default'     => 'category',
            ],
            'catalogRoot' => [
                'title'       => 'Корень каталога',
                'description' => 'Входная страница каталога',
                'type'        => 'dropdown',
                'default'     => 'monolitnie-lestnicy',
            ],
            'categoryPage' => [
                'title'       => 'Страница категорий',
                'description' => 'Страница категорий каталога',
                'type'        => 'dropdown',
                'default'     => 'monolitnie-lestnicy',
            ],
            'productPage' => [
                'title'       => 'Страница товара',
                'description' => 'Подробная страница товара',
                'type'        => 'dropdown',
                'default'     => 'product',
            ],
            'slug' => [
                'title'       => 'Slug',
                'description' => 'Slug категории и товара',
                'default'     => '{{ :slug }}',
                'type'        => 'string'
            ],
        ];
    }

    public function getCategoryPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getProductPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getCatalogRootOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function getCurrentTypeOptions()
    {
        return ['category'=>'Категория', 'product'=>'Продукт'];
    }

    public function onRun()
    {
        $this->makeBreadcrumbs($this->property('currentType', 'category'));
    }

    private function makeBreadcrumbs($pageType)
    {
        switch ($pageType){
            case 'category':
                $this->currentCategory = $this->loadCategory();
                IF($this->currentCategory){
                    //Category selected
                    $categories = $this->currentCategory->getParentsAndSelf();
                    $branch = [];

                    $root = Page::load(Theme::getActiveTheme(),$this->property('catalogRoot'));
                    IF($root){
                        $rPage = [
                            'url' => '/monolitnie-lestnicy/',
                            'title' => $root->title,
                            'active' => false
                        ];
                        $branch[] = $rPage;
                    }

                    foreach ($categories as $category) {

                        $branchItem = [];
                        $branchItem['url'] = $category->setUrl($this->property('categoryPage'), $this->controller);
                        $branchItem['title'] = $category->title;
                        $branchItem['active'] = $this->currentCategory->slug == $category->slug ? true : false;

                        $branch[] = $branchItem;
                    }

                    $this->breadcrumbs += $branch;
                }ELSE{
                    //Catalog root?
                    $root = Page::load(Theme::getActiveTheme(),$this->property('catalogRoot'));
                    IF($root){
                        $rPage = [
                            'url' => '/monolitnie-lestnicy/',
                            'title' => $root->title,
                            'active' => true
                        ];
                        $branch[] = $rPage;
                    }

                    IF(!empty($branch))
                        $this->breadcrumbs += $branch;
                }
                break;
            case 'product':
                $arProduct = ProductModel::with('categories')->where('slug', $this->property('slug'))->get()->toArray();
                $arCategory = Category::find($arProduct[0]['categories'][0]['id']);

                IF($arCategory){

                    $branch = [];

                    $root = Page::load(Theme::getActiveTheme(),$this->property('catalogRoot'));
                    IF($root){
                        $rPage = [
                            'url' => '/monolitnie-lestnicy/',
                            'title' => $root->title,
                            'active' => false
                        ];
                        $branch[] = $rPage;
                    }

                    $categories = $arCategory->getParentsAndSelf();

                    foreach ($categories as $category) {
                        $branchItem = [];
                        $branchItem['url'] = $category->setUrl($this->property('categoryPage'), $this->controller);
                        $branchItem['title'] = $category->title;
                        $branchItem['active'] = false;

                        $branch[] = $branchItem;
                    }

                    $productItem = [
                        'url' => '/product/' . $arProduct[0]['slug'],
                        'title' => $arProduct[0]['name'],
                        'active' => true
                    ];

                    $branch[] = $productItem;

                    $this->breadcrumbs += $branch;
                }
                break;
        }
    }

    protected function loadCategory()
    {

        if (!$slug = $this->property('slug')) {
            return null;
        }

        $category = new CategoryModel();

        $category = $category->isClassExtendedWith('RainLab.Translate.Behaviors.TranslatableModel')
            ? $category->transWhere('slug', $slug)
            : $category->where('slug', $slug);

        $category = $category->first();

        return $category ?: null;
    }

    public function loadCategories()
    {
        $categories = CategoryModel::orderBy('nest_left')->get();

        if (!$categories) {
            return null;
        }

        $categories->each(function ($category) {
            $category->setUrl($this->categoryPage, $this->controller);
            $category->isActive = $category->slug == $this->currentProductCategorySlug;
            $category->isChildActive = Category::hasActiveChild(
                $category->id,
                $this->property('slug')
            );
            $category->hasChildren = CategoryModel::hasChildren($category->slug);
        });

        return $categories;

    }

}
