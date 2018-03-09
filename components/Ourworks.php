<?php
namespace Radit\Catalog\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Theme;
use Illuminate\Support\Facades\DB;
use System\Models\File AS SystemFiles;

use System\Models\File;
use Radit\Catalog\Models\Product AS ProductModel;
use Radit\Watermark\Models\Watermark;

class Ourworks extends ComponentBase{

    public $works;

    public function componentDetails()
    {
        return [
            'name' => 'Примеры работы',
            'description' => 'Галерея работ с фильтром'
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
            'worksPerPage' => [
                'title' => 'Товаров на страницу',
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Не верное значение',
                'default' => '6',
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
                'description' => 'Сортировка работ',
                'type' => 'dropdown',
                'default' => 'name asc'
            ],
        ];
    }

    public function onRun()
    {
        $arImagesData = [];

        $arWorks = [];

        $theme = Theme::getActiveTheme();
        $this->addCss('/themes/' . $theme->getDirName() . '/assets/js/multiselect/multiple-select.css');
        $this->addCss('/themes/' . $theme->getDirName() . '/assets/css/fancybox.css');
        $this->addJs('/themes/' . $theme->getDirName() . '/assets/js/multiselect/multiple-select.js');

        $arImagesData = $this->getImagesData();

        IF($arImagesData){
            foreach ($arImagesData['data'] as $rawImage){
                $arFileData = Watermark::where('id', $rawImage->id)->first();
                $arProduct = ProductModel::find($rawImage->attachment_id)->toArray();
                $arWorks[] = [
                    'file_data' => $arFileData,
                    'product_data' =>$arProduct
                ];
            }
            $arImagesData['data'] = $arWorks;
        }

        IF(input('page') > 1){
            $this->page->meta_title .= ' - ' . input('page') . ' страница';
            $this->page->meta_description .= ' - ' . input('page') . ' страница';
            $this->page->meta_keywords .= ', ' . input('page') . ' страница';
        }

        $this->works = $this->page['works'] = $arImagesData;
    }

    public function getImagesData()
    {
        $arReturn = Db::table('system_files')
            ->where('field', 'gallery_images')
            ->select('attachment_id', 'id')
            ->paginate(16)
            ->toArray();

        return $arReturn;
    }


}