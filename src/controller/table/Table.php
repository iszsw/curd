<?php

namespace iszsw\curd\controller\table;

use iszsw\curd\Helper;
use surface\Component;
use iszsw\curd\lib\Manage;
use surface\helper\TableAbstract;
use surface\table\components\Button;
use surface\table\components\Column;
use surface\table\components\Expand;
use iszsw\curd\model\Table as TableModel;

class Table extends TableAbstract
{

    public function options(): array
    {
        $manage = Manage::instance();
        $list = $manage->tables();
        foreach ($list as &$v) {
            $v['table'] = $v['table'];
            $v['page_label'] = TableModel::$pageLabels[$v['page']] ?? '';
        }
        unset($v);
        return  [
            'props' => [
                'data' => $list
            ]
        ];
    }

    public function header(): ?Component
    {
        $url = Helper::builder_table_url('page/index', ['_table' => ''], true);
        $prefix = Manage::getPrefix();
        return (new Component(['el' => 'div']))->children(
            [
                (new Component())->el('div')->children(
                    [
                        (new Component())->el('h2')->children(['数据表管理']),
                        (new Component())->el('p')->children(["表前缀【{$prefix}】 "]),
                        (new Component())->el('p')->domProps('innerHTML', "CURD访问页面：<b>{$url}表名(不带前缀)</b>"),
                    ]
                ),
            ]
        );
    }

    public function columns(): array
    {
        $fieldsUrl = Helper::builder_table_url('update');
        $delUrl  = Helper::builder_table_url('delete');
        $dataUrl = Helper::builder_table_url('fields/index');

        return [
            (new Expand('description', TableModel::$labels['description']))->scopedSlots([new component(['el' => 'span', 'inject' => ['children']])]),
            (new Column('table', TableModel::$labels['table']))->props(['min-width' => '150px'])->scopedSlots([new component(['el' => 'el-tag', 'props'=>['type'=>'success'], 'inject' => ['children', 'title']])]),
            (new Column('title', TableModel::$labels['title']))->props(['show-overflow-tooltip' => true, 'min-width' => '150px']),
            (new Column('page_label', TableModel::$labels['page']))->props(['width' => '100px']),
            (new Column('rows', TableModel::$labels['rows']))->props(['width' => '100px']),
            (new Column('engine', TableModel::$labels['engine']))->props(['width' => '100px']),
            (new Column('options', '操作'))->props('fixed', 'right')->props('width', '120px')
                ->scopedSlots(
                    [
                        (new Button('el-icon-edit-outline', '表配置'))->createPage($fieldsUrl, ['table']),
                        (new Button('el-icon-tickets', '字段信息'))->createPage($dataUrl, ['table']),
                        (new Button('el-icon-refresh', '初始化表'))
                            ->createConfirm('当前表所有配置将被初始化，确认操作？', ['method' => 'post', 'data' => ['table'], 'url' => $delUrl]),
                    ]
                ),
        ];
    }
}
