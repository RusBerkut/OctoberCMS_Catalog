<?php namespace Radit\Catalog\Controllers;

use Flash;
use BackendMenu;
use Backend\Classes\Controller;
use Radit\catalog\Models\Category;
use System\Classes\SettingsManager;

/**
 * Channels Back-end Controller
 */
class Cats extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.RelationController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Radit.Catalog', 'catalog', 'cats');
    }

    public function index_onDelete()
    {
        if (($checkedIds = post('checked')) && is_array($checkedIds) && count($checkedIds)) {

            foreach ($checkedIds as $channelId) {
                if (!$channel = Category::find($channelId))
                    continue;

                $channel->delete();
            }

            Flash::success('Successfully deleted those categories.');
        }

        return $this->listRefresh();
    }

    public function reorder()
    {
        $this->pageTitle = e(trans('radit.catalog::lang.catalog.category_reorder'));

        $toolbarConfig = $this->makeConfig();
        $toolbarConfig->buttons = '~/plugins/radit/catalog/controllers/cats/_reorder_toolbar.htm';

        $this->vars['toolbar'] = $this->makeWidget('Backend\Widgets\Toolbar', $toolbarConfig);
        $this->vars['records'] = Category::make()->getEagerRoot();
    }

    public function reorder_onMove()
    {
        $sourceNode = Category::find(post('sourceNode'));
        $targetNode = post('targetNode') ? Category::find(post('targetNode')) : null;

        if ($sourceNode == $targetNode)
            return;

        switch (post('position')) {
            case 'before': $sourceNode->moveBefore($targetNode); break;
            case 'after': $sourceNode->moveAfter($targetNode); break;
            case 'child': $sourceNode->makeChildOf($targetNode); break;
            default: $sourceNode->makeRoot(); break;
        }

        // $this->vars['records'] = Category::make()->getEagerRoot();
        // return ['#reorderRecords' => $this->makePartial('reorder_records')];
    }
}