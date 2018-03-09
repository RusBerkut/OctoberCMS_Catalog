<?php namespace Radit\Catalog;

use Backend;
use Controller;
use Event;
use System\Classes\PluginBase;

use Radit\Catalog\Models\Category;
use Radit\Catalog\Models\Product;

/**
 * Shop Plugin Information File
 */
class Plugin extends PluginBase
{

    public $require = ['RainLab.Translate'];

    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'radit.catalog::lang.plugin.name',
            'description' => 'radit.catalog::lang.plugin.description',
            'author'      => 'Radit',
            'icon'        => 'icon-shopping-cart'
        ];
    }
	
	 public function registerComponents()
    {
        return [
            'Radit\Catalog\Components\Products'       => 'shopProducts',
            'Radit\Catalog\Components\ProductsLanding'       => 'shopProductsLanding',
			'Radit\Catalog\Components\Product'       => 'shopProduct',
			'Radit\Catalog\Components\Categories'       => 'shopCategories',
			'Radit\Catalog\Components\Ourworks'       => 'ourWorks',
			'Radit\Catalog\Components\CatalogBreadcrumbs'       => 'shopBreadcrumbs',
			'Radit\Catalog\Components\Calculator'       => 'shopCalculator',
        ];
    }
    
    public function registerMailTemplates()
    {
        return [
            'radit.catalog::mail.license' => 'Шаблон письма с лицензией на цифровый товар',
        ];
    }
	
	public function registerNavigation()
    {
        return [
            'catalog' => [
                'label'       => 'radit.catalog::lang.catalog.menu_label',
                'url'         => Backend::url('radit/catalog/products'),
                'icon'        => 'icon-shopping-cart',
                'permissions' => ['radit.catalog.*'],
                'order'       => 500,
                'sideMenu' => [
                    'products' => [
                        'label'       => 'radit.catalog::lang.catalog.products',
                        'icon'        => 'icon-cubes',
                        'url'         => Backend::url('radit/catalog/products'),
                        'permissions' => ['radit.catalog.access_products'],
                    ],
                    'categories' => [
                        'label'       => 'radit.catalog::lang.catalog.categories',
                        'icon'        => 'icon-sitemap',
                        'url'         => Backend::url('radit/catalog/cats'),
                        'permissions' => ['radit.catalog.access_categories'],
                    ],
                    /*'vendors' => [
                        'label'       => 'radit.catalog::lang.catalog.vendors',
                        'icon'        => 'icon-industry',
                        'url'         => Backend::url('radit/catalog/vendors'),
                        'permissions' => ['radit.catalog.access_categories'],
                    ],
                    'currencies' => [
                        'label'       => 'radit.catalog::lang.catalog.currencies',
                        'icon'        => 'icon-money',
                        'url'         => Backend::url('radit/catalog/currencies'),
                        'permissions' => ['radit.catalog.access_coupons'],
                    ],
					'orders' => [
                        'label'       => 'radit.catalog::lang.catalog.orders',
                        'icon'        => 'icon-shopping-bag',
                        'url'         => Backend::url('radit/catalog/orders'),
                        'permissions' => ['radit.catalog.access_orders'],
                    ],
					'coupons' => [
                        'label'       => 'radit.catalog::lang.catalog.coupons',
                        'icon'        => 'icon-percent',
                        'url'         => Backend::url('radit/catalog/coupons'),
                        'permissions' => ['radit.catalog.access_coupons'],
                    ],*/
					'properties' => [
                        'label'       => 'Свойства',
                        'icon'        => 'icon-list',
                        'url'         => Backend::url('radit/catalog/properties'),
                        'permissions' => ['radit.catalog.access_properties'],
                    ],
                    'calculator' => [
                        'label'       => 'Калькуляторы',
                        'icon'        => 'icon-calculator',
                        'url'         => Backend::url('radit/catalog/calculator'),
                        'permissions' => ['radit.catalog.access_calculator'],
                    ],
                ]
            ]
        ];
    }
	
	public function registerSettings()
    {
        return [
            'settings' => [
                'label' => 'radit.catalog::lang.catalog.settings',
                'description' => 'radit.catalog::lang.catalog.settings_description',
                'category' => 'Shop',
                'icon' => 'icon-credit-card',
                'class' => 'Radit\catalog\Models\Settings',
                'order' => 500,
            ],
        ];
    }

    public function registerFormWidgets()
    {
        return [
            'Radit\Catalog\FormWidgets\ProductEditProperties' => [
                'label' => 'Product Properties edit',
                'code' => 'editproperties'
            ]
        ];
    }

    public function registerMarkupTags()
    {
        return [
            'functions' => [
                'decline' => [$this, 'declination']
            ],
        ];
    }

    public function declination($number, $suffix = [])
    {
        $keys = array(2, 0, 1, 1, 1, 2);
        $mod = $number % 100;
        $suffix_key = ($mod > 7 && $mod < 20) ? 2: $keys[min($mod % 10, 5)];
        return $suffix[$suffix_key];
    }

    public function boot()
    {
        /*
         * Register menu items for the RainLab.Pages plugin
         */
        Event::listen('pages.menuitem.listTypes', function() {
            return [
                'catalog-category'       => 'Категория каталога',
                'all-catalog-categories' => 'Все категории каталога',
                'catalog-product'           => 'Товар каталога',
                'all-catalog-products'      => 'Все товары каталога',
            ];
        });

        Event::listen('pages.menuitem.getTypeInfo', function($type) {
            if ($type == 'catalog-category' || $type == 'all-catalog-categories') {
                return Category::getMenuTypeInfo($type);
            }
            elseif ($type == 'catalog-product' || $type == 'all-catalog-products') {
                return Product::getMenuTypeInfo($type);
            }
        });

        Event::listen('pages.menuitem.resolveItem', function($type, $item, $url, $theme) {
            if ($type == 'catalog-category' || $type == 'all-catalog-categories') {
                return Category::resolveMenuItem($item, $url, $theme);
            }
            elseif ($type == 'catalog-product' || $type == 'all-catalog-products') {
                return Product::resolveMenuItem($item, $url, $theme);
            }
        });
    }

}
