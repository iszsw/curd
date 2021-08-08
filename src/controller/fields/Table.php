<?php

namespace iszsw\curd\controller\fields;


use iszsw\curd\Helper;
use iszsw\curd\lib\Manage;
use iszsw\curd\model\Table as TableModel;
use surface\Component;
use surface\helper\TableAbstract;

use surface\table\components\Switcher;
use surface\table\components\Button;
use surface\table\components\Column;
use surface\table\components\Select;
use surface\table\components\Writable;

class Table extends TableAbstract
{

    private $table;

    public function __construct()
    {
        $this->table = input('table', '');
    }

    public function header(): ?Component
    {
        return (new Component(['el' => 'div']))->children(
            [
                (new Component())->el('div')->children(
                    [
                        (new Component())->el('h2')->children(["表 【{$this->table}】 字段管理"]),
                        (new Component())->el('p')->children(['修改表中字段在页面中显示的样式']),
                    ]
                ),
                (new Button('el-icon-plus', '添加'))->createPage(Helper::builder_table_url('fields/update', ['table'=>$this->table]))->props('doneRefresh', true),
                (new Button('el-icon-refresh', '刷新'))->createRefresh()->props('doneRefresh', true),
            ]
        );
    }

    public function options(): array
    {
        $list = Manage::instance()->fields($this->table);
        $formTypes = TableModel::getFormServersLabels();
        foreach ($list as &$v)
        {
            $v['id'] = $v['field'];
            (isset($v['field_label']) && $v['field_label']) || $v['field_label'] = $v['field'].((isset($v['key']) && $v['key']) ? "【{$v['key']}】" : '') . ($v['relation'] ? '【增】' : '');
            $v['search'] =  $formTypes[$v['search_type']] . ($v['search_type'] !== '_' ? "  【{$v['search']}】 " : '');
        }
        unset($v);

        return [
            'props' => [
                'data' => $list
            ]
        ];
    }

    public function columns(): array
    {
        $formTypes = TableModel::getFormServersLabels();
        $tableTypes = TableModel::getTableServersLabels();

        $changeUrl = Helper::builder_table_url('fields/change', ['table' => $this->table]);
        $deleteUrl = Helper::builder_table_url('fields/delete', ['table' => $this->table]);
        $editUrl   = Helper::builder_table_url('fields/update', ['table' => $this->table]);

        return [
            (new Column('weight', TableModel::$labels['weight']))->scopedSlots(
                [
                    (new Writable())->props(['method' => 'post', 'doneRefresh'=>!0, 'async' => ['url' => $changeUrl, 'method' => 'post', 'data' => ['id']]]),
                ]
            )->props('width', '80px'),
            (new Column('field_label', TableModel::$labels['field']))->props(['min-width' => '120px']),
            (new Column('title', TableModel::$labels['title']))->props(['min-width' => '200px'])->scopedSlots(
                [
                    (new Writable())->props(['async' => ['url' => $changeUrl, 'method' => 'post', 'data'=>['id']]]),
                ]
            ),
            (new Column('type', TableModel::$labels['type']))->props(['min-width' => '150px']),
            (new Column('table_type', TableModel::$labels['table_type']))->props('width', '120px')->scopedSlots(
                [
                    (new Select())->props(
                        [
                            'async'   => ['method' => 'post', 'data' => ['id'], 'url' => $changeUrl],
                            'options' => $tableTypes,
                        ]
                    ),
                ]
            ),
            (new Column('form_type', TableModel::$labels['form_type']))->props('width', '120px')->scopedSlots(
                [
                    (new Select())->props(
                        [
                            'async'   => ['method' => 'post', 'data' => ['id'], 'url' => $changeUrl],
                            'options' => $formTypes,
                        ]
                    )->options(),
                ]
            ),
            (new Column('search', TableModel::$labels['search']))->props('min-width', '120px'),
            (new Column('table_sort', TableModel::$labels['table_sort']))->props('width', '100px')->scopedSlots(
                [
                    (new Switcher())->props(
                        [
                            'async'   => ['method' => 'post', 'data' => ['id'], 'url' => $changeUrl],
                            'options' => TableModel::$statusLabels,
                        ]
                    ),
                ]
            )->options(),
            (new Column('null', TableModel::$labels['null']))->props('width', '80px'),
            (new Column('default', TableModel::$labels['default']))->props(['width' => '80px', 'show-overflow-tooltip' => true]),
            (new Column('options', '操作'))->props('fixed', 'right')->props('width', '100px')
                ->scopedSlots(
                    [
                        (new Button('el-icon-edit-outline', '修改'))->createPage($editUrl, ['field'])->props('doneRefresh', true),
                        (new Button('el-icon-close', '删除'))
                            ->createConfirm('删除或者初始化字段，确认操作？', ['method' => 'post', 'data' => ['field'], 'url' => $deleteUrl])
                            ->props('doneRefresh', true),
                    ]
                ),
        ];

    }

    public function pagination(): ?Component
    {
        return null;
    }

    public function data($where = [], $order = '', $page = 1, $limit = 15): array
    {
        return [];
    }
}
