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
use surface\table\components\Switcher;

class Table extends TableAbstract
{

    protected $status;

    public function __construct()
    {
        $this->status = input('status/b', true);
    }

    public function init(\surface\table\Table $table)
    {
        $table->addStyle(<<<STYLE
<style>
.el-menu--horizontal>.el-menu-item{
    padding: 0px;
}
.el-menu--horizontal>.el-menu-item>a{
    padding: 0px 20px;
}
</style>
STYLE);
    }

    public function options(): array
    {
        $manage = Manage::instance();
        $list = $manage->tables();
        $data = [];
        foreach ($list as $v)
        {
            if ($v['status'] === $this->status)
            {
                $v['table'] = $v['table'];
                $v['page_label'] = TableModel::$pageLabels[$v['page']] ?? '';
                $data[] = $v;
            }
        }

        return [
            'props' => [
                'data' => $data,
            ],
        ];
    }

    public function header(): ?Component
    {
        $url = Helper::builder_table_url('page', [], true);
        return (new Component(['el' => 'div']))->children(
            [
                (new Component())->el('p')->children(
                    [
                        (new Component())->el('div')->class('title')->children(['数据表管理']),
                        (new Component())->el('p')->domProps('innerHTML', "CURD访问页面(不带前缀)：<b>{$url}/表名 </b>"),
                        (new Component())->el('el-menu')->style(['margin-bottom' => '20px'])->props(
                            [
                                'mode'             => 'horizontal',
                                'default-active'   => $this->status ? 'on' : 'off',
                                'background-color' => '#EFF1F7',
                            ]
                        )->children(
                            [
                                (new Component())->el('el-menu-item')->props('index', 'on')->children(
                                    [
                                        (new Component())->el('el-link')->props('underline', false)
                                            ->attrs('href', Helper::builder_table_url('', ['status' => 1]))->children(['允许访问']),
                                    ]
                                ),
                                (new Component())->el('el-menu-item')->props('index', 'off')->children(
                                    [
                                        (new Component())->el('el-link')->props('underline', false)
                                            ->attrs('href', Helper::builder_table_url('', ['status' => 0]))->children(['禁止访问(所有访问)']),
                                    ]
                                ),
                            ]
                        ),
                    ]
                ),
            ]
        );
    }

    public function columns(): array
    {
        $fieldsUrl = Helper::builder_table_url('update');
        $delUrl = Helper::builder_table_url('delete');
        $changeUrl = Helper::builder_table_url('change');
        $dataUrl = Helper::builder_table_url('fields');

        return [
            (new Expand('description', TableModel::$labels['description']))->scopedSlots([new component(['el' => 'span', 'inject' => ['children']])]),
            (new Column('table', TableModel::$labels['table']))->props(['min-width' => '150px'])->scopedSlots(
                [
                    new component(
                        [
                            'el'     => 'el-tag',
                            'props'  => ['type' => 'success'],
                            'inject' => [
                                'children',
                                'title',
                            ],
                        ]
                    ),
                ]
            ),
            (new Column('title', TableModel::$labels['title']))->props(['show-overflow-tooltip' => true, 'min-width' => '150px']),
            (new Column('status', TableModel::$labels['status']))->props('width', '100px')->scopedSlots(
                [
                    (new Switcher())->props(
                        [
                            'async'       => ['method' => 'post', 'url' => $changeUrl.'/{table}'],
                            'options'     => TableModel::$statusLabels,
                            'doneRefresh' => true,
                        ]
                    ),
                ]
            )->options(),
            (new Column('page_label', TableModel::$labels['page']))->props(['width' => '100px']),
            (new Column('rows', TableModel::$labels['rows']))->props(['width' => '100px']),
            (new Column('engine', TableModel::$labels['engine']))->props(['width' => '100px']),
            (new Column('options', '操作'))->props('fixed', 'right')->props('width', '120px')
                ->scopedSlots(
                    [
                        (new Button('el-icon-edit-outline', '表配置'))->createPage($fieldsUrl.'/{table}')->props('doneRefresh',true),
                        (new Button('el-icon-tickets', '字段信息'))->createPage($dataUrl.'/{table}'),
                        (new Button('el-icon-refresh', '初始化表'))
                            ->createConfirm('当前表所有配置将被初始化，确认操作？', ['method' => 'post', 'url' => $delUrl.'/{table}']),
                    ]
                ),
        ];
    }

    public function pagination(): ?Component
    {
        return null;
    }
}
