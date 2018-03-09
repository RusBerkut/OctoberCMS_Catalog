<?php namespace Radit\Catalog\Models;

use Model;
use ApplicationException;
use Cms\Classes\Theme;
use Cms\Classes\Page as CmsPage;

/**
 * Category Model
 */
class Category extends Model
{

    use \October\Rain\Database\Traits\Sluggable;
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\NestedTree;

    /**
     * @var boolean Channel has new posts for member, set by ChannelWatch model
     */
    public $hasNew = true;

    /**
     * @var string The database table used by the model.
     */
    public $table = 'radit_shop_categories';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = ['title', 'description', 'parent_id'];

    /**
     * @var array The attributes that should be visible in arrays.
     */
    protected $visible = ['id', 'slug', 'title', 'description', 'meta_h1', 'meta_title', 'meta_description', 'meta_keywords'];

    /**
     * @var array Validation rules
     */
    public $rules = [
        'title' => 'required|unique:radit_shop_categories',
        'slug' => 'required'
    ];

    /**
     * @var array Auto generated slug
     */
    protected $slugs = ['slug' => 'title'];

    /**
     * @var array Relations
     */
    public $hasMany = [
        'products' => ['Radit\catalog\Models\Product', 'table' => 'radit_shop_products_categories']
    ];

    /**
     * @var array Attributes that support translation, if available.
     */
    public $translatable = ['title', 'description'];

    /**
     * Add translation support to this model, if available.
     * @return void
     */
    public static function boot()
    {
        // Call default functionality (required)
        parent::boot();

        // Check the translate plugin is installed
        if (!class_exists('RainLab\Translate\Behaviors\TranslatableModel')) {
            return;
        }

        // Extend the constructor of the model
        self::extend(function ($model) {
            // Implement the translatable behavior
            $model->implement[] = 'RainLab.Translate.Behaviors.TranslatableModel';
        });
    }

    public function getCategoriesOptions()
    {
        return $this->orderBy('title')->lists('title', 'id');
    }

    /*
     * Возвращает количество продуктов для данной категории
     */
    public function getProductCountAttribute()
    {
        return $this->products()->whereIsPublished(1)->count();
    }



    /**
     * Sets the "url" attribute with a URL to this object
     * @param string $pageName
     * @param Cms\Classes\Controller $controller
     */
    public function setUrl($pageName, $controller)
    {
        $params = [
            //'id' => $this->id,
            'slug' => $this->slug,
        ];

        return $this->url = $controller->pageUrl($pageName, $params);
    }

    public static function categoryDetails($param)
    {
        if (!$category = self::whereSlug($param['category'])->first()) {
            return null;
        }

        return $category;
    }

    public static function getMenuTypeInfo($type)
    {
        $result = [];

        if ($type == 'all-catalog-categories') {
            $result = [
                'dynamicItems' => true,
            ];
        }

        if ($type == 'catalog-category') {
            $result = [
                'references'   => self::listSubCategoryOptions(),
                'dynamicItems' => true
            ];
        }

        if ($result) {
            $theme = Theme::getActiveTheme();

            $pages = CmsPage::listInTheme($theme, true);
            $cmsPages = [];
            foreach ($pages as $page) {
                if (!$page->hasComponent('shopCategories')) {
                    continue;
                }

                /*
                 * Component must use a category filter with a routing parameter
                 * eg: categoryFilter = "{{ :somevalue }}"
                 */
                $properties = $page->getComponentProperties('shopCategories');
                if (!isset($properties['slug']) || !preg_match('/{{\s*:/', $properties['slug'])) {
                    continue;
                }

                $cmsPages[] = $page;
            }

            $result['cmsPages'] = $cmsPages;
        }

        return $result;
    }

    protected static function listSubCategoryOptions()
    {
        $category = self::make()->getAllRoot();

        $iterator = function ($categories) use (&$iterator) {
            $result = [];

            foreach ($categories as $category) {
                if (!$category->children) {
                    $result[$category->id] = $category->title;
                } else {
                    $result[$category->id] = [
                        'title' => $category->title,
                        'items' => $iterator($category->children)
                    ];
                }
            }

            return $result;
        };

        return $iterator($category);
    }

    public static function resolveMenuItem($item, $url, $theme)
    {
        $result = null;

        if ($item->type == 'catalog-category') {
            if (!$item->reference || !$item->cmsPage) {
                return;
            }

            $category = self::find($item->reference);
            if (!$category) {
                return;
            }

            $pageUrl = self::getCategoryPageUrl($item->cmsPage, $category, $theme);
            if (!$pageUrl) {
                return;
            }

            $pageUrl = \URL::to($pageUrl);

            $result = [];
            $result['url'] = $pageUrl;
            $result['isActive'] = $pageUrl == $url;
            $result['mtime'] = $category->updated_at;

            if ($item->nesting) {
                $categories = $category->getAllRoot();
                $iterator = function ($categories) use (&$iterator, &$item, &$theme, $url) {
                    $branch = [];

                    foreach ($categories as $category) {

                        $branchItem = [];
                        $branchItem['url'] = self::getCategoryPageUrl($item->cmsPage, $category, $theme);
                        $branchItem['isActive'] = $branchItem['url'] == $url;
                        $branchItem['title'] = $category->name;
                        $branchItem['mtime'] = $category->updated_at;

                        if ($category->children) {
                            $branchItem['items'] = $iterator($category->children);
                        }

                        $branch[] = $branchItem;
                    }

                    return $branch;
                };

                $result['items'] = $iterator($categories);
            }
        } elseif ($item->type == 'all-catalog-categories') {
            $result = [
                'items' => []
            ];

            $categories = self::orderBy('nest_left')->get();
            foreach ($categories as $category) {
                $categoryItem = [
                    'title' => $category->title,
                    'url'   => self::getCategoryPageUrl($item->cmsPage, $category, $theme),
                    'mtime' => $category->updated_at,
                ];

                $categoryItem['isActive'] = $categoryItem['url'] == $url;

                $result['items'][] = $categoryItem;
            }
        }

        return $result;
    }

    /**
     * Returns URL of a category page.
     */
    public static function getCategoryPageUrl($pageCode, $category, $theme)
    {
        $page = CmsPage::loadCached($theme, $pageCode);
        if (!$page) {
            return;
        }

        $properties = $page->getComponentProperties('shopCategories');
        if (!isset($properties['slug'])) {
            return;
        }

        /*
         * Extract the routing parameter name from the category filter
         * eg: {{ :someRouteParam }}
         */
        if (!preg_match('/^\{\{([^\}]+)\}\}$/', $properties['slug'], $matches)) {
            return;
        }

        $paramName = substr(trim($matches[1]), 1);
        $url = CmsPage::url($page->getBaseFileName(), [$paramName => $category->slug]);

        return $url;
    }

    public static function hasChildren($categoryUrl)
    {
        $category = self::whereSlug($categoryUrl)->firstOrFail();

        $children = self::whereParentId($category->id)->get();

        if (isset($children) && sizeof($children) != 0) {
            return true;
        }

        return false;
    }

    /**
     * Does the category is parent of active category ?
     */
    public static function hasActiveChild($categoryId, $activeCategorySlug)
    {
        //dump($categoryId . ' / ' . $activeCategorySlug);
        $activeCategoryHasParent = self::whereSlug($activeCategorySlug)->whereParentId($categoryId)->first();

        if (isset($activeCategoryHasParent) && sizeof($activeCategoryHasParent) != 0) {
            //dump($activeCategoryHasParent);
            return true;
        }

        return false;
    }

}