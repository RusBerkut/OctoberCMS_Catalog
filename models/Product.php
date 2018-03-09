<?php namespace Radit\Catalog\Models;

use Model;
use Carbon\Carbon;
use Cms\Classes\Page as CmsPage;
use Cms\Classes\Theme;

/**
 * Product Model
 */
class Product extends Model
{

	use \October\Rain\Database\Traits\Validation;	
	
	public $rules = [
        'name' => 'required',
//        'slug'=>'required|unique:radit_shop_vendors',
		'price' => 'required'
    ];
    /**
     * @var string The database table used by the model.
     */
    public $table = 'radit_shop_products';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Fillable fields
     */
    protected $fillable = [];

    protected $jsonable = ['options']; 

    protected $slugs = ['slug' => 'name'];

    public static $allowedSortingOptions = array(
        'sorting asc' => 'Порядок (возр.)',
        'sorting desc' => 'Порядок (убыв.)',
        'name asc' => 'Название (ascending)',
        'name desc' => 'Название (descending)',
        'price asc' => 'Цена (ascending)',
        'price desc' => 'Цена (descending)',
        'random' => 'Случайно'
    );

    /**
     * @var array Relations
     */
    public $hasOne = [];
    public $hasMany = [
        'properties' => ['Radit\catalog\Models\Properties', 'table' => 'radit_shop_properties_values'],
        'properties_values' => [
            'Radit\catalog\Models\PropertiesValues',
            'table' => 'radit_shop_properties_values',
            'key' => 'products_id']
    ];
    public $belongsTo = [
        'vendor' => Vendor::class
    ];
    public $belongsToMany = [
        'categories' => [
            'Radit\catalog\Models\Category',
            'table' => 'radit_shop_products_categories',
            'order' => 'nest_left'
        ]
    ];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = ['digital_product' => ['System\Models\File']];
    public $attachMany = [
        'featured_images' => ['System\Models\File'],
        'gallery_images' => ['System\Models\File']
    ];

    public function scopeCategory($query, $catID)
    {
        IF($catID){
            $category = Category::find($catID);

            $categories = $category->getAllChildrenAndSelf()->lists('id');
            $query->whereHas('categories', function($q) use ($categories) {
                $q->whereIn('id', $categories);
            });
        }
        return $query;
    }

    /**
     * Sets the "url" attribute with a URL to this object
     * @param string $pageName
     * @param Cms\Classes\Controller $controller
     */
    public function setUrl($pageName, $controller)
    {
        $params = [
            'id' => $this->id,
            'slug' => $this->slug,
        ];

        if (array_key_exists('categories', $this->getRelations())) {
            $params['category'] = $this->categories->count() ? $this->categories->first()->slug : null;
        }

        return $this->url = $controller->pageUrl($pageName, $params);
    }

    public function scopeListFrontEnd($query, $options)
    {
        /*
         * Default options
         */
        extract(array_merge([
            'page'       => 1,
            'perPage'    => 6,
            'sorting'    => 'sorting',
            'categories' => null,
            'category'   => null,
            'search'     => '',
            'productsIDs' => []
        ], $options));

        $searchableFields = ['name', 'slug', 'short_description', 'description'];

        /*
         * Product IDs
         */
        IF(!empty($productsIDs)){
            $query->whereIn('id', $productsIDs);
        }

        /*
         * Sorting
         */
        if (!is_array($sorting)) {
            $sorting = [$sorting];
        }

        foreach ($sorting as $_sorting) {

            if (in_array($_sorting, array_keys(self::$allowedSortingOptions))) {
                $parts = explode(' ', $_sorting);
                if (count($parts) < 2) {
                    array_push($parts, 'asc');
                }
                list($sortField, $sortDirection) = $parts;
                if ($sortField == 'random') {
                    $sortField = Db::raw('RAND()');
                }
                $query->orderBy($sortField, $sortDirection);
            }
        }

        /*
         * Categories
         */
        if ($categories !== null) {
            if (!is_array($categories)) $categories = [$categories];
            $query->whereHas('categories', function($q) use ($categories) {
                $q->whereIn('id', $categories);
            });
        }

        /*
         * Category, including children
         */
        if ($category !== null) {
            $category = Category::find($category);

            $categories = $category->getAllChildrenAndSelf()->lists('id');
            $query->whereHas('categories', function($q) use ($categories) {
                $q->whereIn('id', $categories);
            });
        }

        return $query->paginate($perPage, $page);

    }

    public static function getMenuTypeInfo($type)
    {
        $result = [];

        if ($type == 'catalog-product') {
            $references = [];
            $products = self::orderBy('sorting')->get();
            foreach ($products as $product) {
                $references[$product->id] = $product->name;
            }

            $result = [
                'references'   => $references,
                'nesting'      => false,
                'dynamicItems' => false
            ];
        }

        if ($type == 'all-catalog-products') {
            $result = [
                'dynamicItems' => true
            ];
        }

        if ($result) {
            $theme = Theme::getActiveTheme();

            $pages = CmsPage::listInTheme($theme, true);
            $cmsPages = [];

            foreach ($pages as $page) {
                if (!$page->hasComponent('shopProduct')) {
                    continue;
                }

                /*
                 * Component must use a categoryPage filter with a routing parameter and post slug
                 * eg: categoryPage = "{{ :somevalue }}", slug = "{{ :somevalue }}"
                 */
                $properties = $page->getComponentProperties('shopProduct');
                $properties['categoryPage'] = 'monolitnie-lestnicy';
                if (!isset($properties['categoryPage']) || !preg_match('/{{\s*:/', $properties['slug'])) {
                    continue;
                }

                $cmsPages[] = $page;
            }

            $result['cmsPages'] = $cmsPages;
        }

        return $result;
    }

    public static function resolveMenuItem($item, $url, $theme)
    {
        $result = null;

        if ($item->type == 'catalog-product') {
            if (!$item->reference || !$item->cmsPage)
                return;

            $category = Category::find($item->reference);
            if (!$category)
                return;

            $pageUrl = self::getProductPageUrl($item->cmsPage, $category, $theme);
            if (!$pageUrl)
                return;

            $pageUrl = Url::to($pageUrl);

            $result = [];
            $result['url'] = $pageUrl;
            $result['isActive'] = $pageUrl == $url;
            $result['mtime'] = $category->updated_at;
        }
        elseif ($item->type == 'all-catalog-products') {
            $result = [
                'items' => []
            ];

            $products = self::orderBy('sorting')->get();
            foreach ($products as $product) {
                $productItem = [
                    'title' => $product->name,
                    'url'   => self::getProductPageUrl($item->cmsPage, $product, $theme),
                    'mtime' => $product->updated_at,
                ];

                $productItem['isActive'] = $productItem['url'] == $url;

                $result['items'][] = $productItem;
            }
        }

        return $result;
    }

    /**
     * Returns URL of a product page.
     */
    protected static function getProductPageUrl($pageCode, $category, $theme)
    {
        $page = CmsPage::loadCached($theme, $pageCode);
        if (!$page) return;

        $properties = $page->getComponentProperties('shopProduct');
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

}