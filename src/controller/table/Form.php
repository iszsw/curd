<?php

namespace iszsw\curd\controller\table;

use iszsw\curd\Helper;
use iszsw\curd\lib\Manage;
use surface\form\components\Arrays;
use surface\form\components\Input;
use surface\form\components\Radio;
use surface\form\components\Select;
use surface\form\components\Switcher;
use surface\helper\FormAbstract;
use iszsw\curd\model\Table as TableModel;

class Form extends FormAbstract
{

    public function options(): array
    {
        return [
            'async' => [
                'url' => '',
            ],
        ];
    }

    public function columns(): array
    {
        $table = input('table', '');
        if ( ! $table)
        {
            throw new \Exception("数据表[{$table}]不存在");
        }
        $model = Manage::instance()->table($table);
        if ( ! $model || count($model) < 1)
        {
            throw new \Exception("参数错误");
        }
        $buttons = [];
        foreach ($model['button'] as $b) {
            $b['data_extend'] = Helper::formatOptions($b['data_extend'], TableModel::VALUE, TableModel::KEY);
            $b['btn_extend'] = Helper::formatOptions($b['btn_extend'], TableModel::VALUE, TableModel::KEY);
            $buttons[] = $b;
        }

        return [
            (new Input('table', TableModel::$labels['table'], $model['table']))->props(['readonly' => true]),
            (new Input('pk', TableModel::$labels['pk'], $model['pk'])),
            (new Input('title', TableModel::$labels['title'], $model['title'])),
            (new Input('description', TableModel::$labels['description'], $model['description'])),
            (new Switcher('page', TableModel::$labels['page'], $model['page'])),
            (new Switcher('auto_timestamp', TableModel::$labels['auto_timestamp'], $model['auto_timestamp']))
                ->marker('是否需要自动写入时间戳 自动更新create_time,update_time字段')
                ->props(['options' => TableModel::$statusLabels]),
            (new Select('button_default', TableModel::$labels["button_default"], $model['button_default'] ?? array_keys(TableModel::$buttonDefaultLabels)))
                ->options(Helper::formatOptions(TableModel::$buttonDefaultLabels))
                ->props(['filterable' => true, 'multiple' => true, 'default-first-option' => true])
                ->marker('默认增、删、改、刷新按钮'),

            (new Arrays('button', TableModel::$labels['button'], $buttons))
                ->props(['span' => 24, 'title' => ! 1, 'append' => ! 0])
                ->options(
                    [
                        (new Input('icon', TableModel::$labels["icon"])),
                        (new Input('title', TableModel::$labels["title"])),
                        (new Radio('button_local', TableModel::$labels["button_local"], $model['option_local'] ?? TableModel::LOCAL_TOP))
                            ->options(Helper::formatOptions(TableModel::$localLabels)),

                        (new Select('top_type', TableModel::$labels["button_type"], TableModel::BTN_TYPE_PAGE))
                            ->options(Helper::formatOptions(TableModel::$btnTypeLabels))
                            ->visible([['prop' => 'button_local', 'value' => TableModel::LOCAL_TOP]]),
                        (new Select('right_type', TableModel::$labels["button_type"], TableModel::BTN_TYPE_PAGE))->options(
                            Helper::formatOptions([
                                                      TableModel::BTN_TYPE_PAGE    => TableModel::$btnTypeLabels[TableModel::BTN_TYPE_PAGE],
                                                      TableModel::BTN_TYPE_CONFIRM => TableModel::$btnTypeLabels[TableModel::BTN_TYPE_CONFIRM],
                                                  ])
                        )->visible([['prop' => 'button_local', 'value' => TableModel::LOCAL_RIGHT]]),

                        (new Input('confirm_msg', TableModel::$labels["confirm_msg"]))->marker('按钮点击提示文字')->visible(
                            [
                                ['exec' => "(model.button_local === '".TableModel::LOCAL_TOP."' && model.top_type === '".TableModel::BTN_TYPE_CONFIRM."') || (model.button_local === '".TableModel::LOCAL_RIGHT."' && model.right_type === '".TableModel::BTN_TYPE_CONFIRM."')"],
                            ]
                        ),

                        (new Input('url', TableModel::$labels["url"]))
                            ->visible([['exec' => 'model.button_local !== "'.TableModel::LOCAL_TOP.'" || model.top_type !== "'.TableModel::BTN_TYPE_REFRESH.'"']]),
                        (new Arrays('data_extend', TableModel::$labels['data_extend'], []))->options(
                            [
                                (new Input(TableModel::KEY, TableModel::$labels[TableModel::KEY]))->item(false),
                                (new Input(TableModel::VALUE, TableModel::$labels[TableModel::VALUE]))->item(false),
                            ]
                        )->marker('自定义提交的参数 user=>xxx'),
                        (new Arrays('btn_extend', TableModel::$labels['btn_extend'], []))->options(
                            [
                                (new Input(TableModel::KEY, TableModel::$labels[TableModel::KEY]))->item(false),
                                (new Input(TableModel::VALUE, TableModel::$labels[TableModel::VALUE]))->item(false),
                            ]
                        )->marker('按钮样式扩展(el-button的 props或者Component) type=>primary'),
                    ]
                )->marker("自定义的操作按钮"),

            (new Arrays('extend', TableModel::$labels['extend'], Helper::formatOptions($model['extend'], TableModel::VALUE, TableModel::KEY)))->options(
                [
                    (new Input(TableModel::KEY, TableModel::$labels[TableModel::KEY]))->item(false),
                    (new Input(TableModel::VALUE, TableModel::$labels[TableModel::VALUE]))->item(false),
                ]
            )->marker('el-table的props或者Component emptyText => ...'),
        ];
    }

    public function save():bool
    {
        $post = input();
        try
        {
            Manage::instance()->save($post);
        } catch (\Exception $e)
        {
            $this->error = $e->getMessage();
            return false;
        }
        return true;
    }


}
