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
        $fields = array_keys($model['fields']);

        $fieldsOptions = array_combine($fields, $fields);
        $defaultDateTime = array_intersect_key($fieldsOptions, TableModel::$defaultDateTime);

        return [
            (new Input('table', TableModel::$labels['table'], $model['table']))->props(['readonly' => true]),
            (new Select('pk', TableModel::$labels['pk'], $model['pk']))->options(Helper::formatOptions($fields)),
            (new Input('title', TableModel::$labels['title'], $model['title'])),
            (new Input('description', TableModel::$labels['description'], $model['description'])),
            (new Switcher('page', TableModel::$labels['page'], $model['page'])),
            (new Select('datetime_fields', TableModel::$labels["datetime_fields"], $model['datetime_fields'] ?? array_keys($defaultDateTime)))
                ->options(Helper::formatOptions($fieldsOptions))
                ->props(['filterable' => true, 'multiple' => true, 'default-first-option' => true])
                ->marker('时间字段助手 根据数据库字段类型自动更新时间字段'),
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
                            ->visible([['exec' => 'model.button_local !== "'.TableModel::LOCAL_TOP.'" || (model.top_type !== "'.TableModel::BTN_TYPE_REFRESH.'" && model.top_type !== "'.TableModel::BTN_TYPE_CUSTOM.'")']]),
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
                        )->marker('按钮样式扩展(el-button的 props) type=>primary, class => export'),
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
        $data = input();
        try
        {
            foreach ($data['button'] as $k => &$b)
            {
                $b['btn_extend'] = Helper::simpleOptions($b['btn_extend']);
                $b['data_extend'] = Helper::simpleOptions($b['data_extend']);
            }
            unset($b);
            Manage::instance()->save($data);
        } catch (\Exception $e)
        {
            $this->error = $e->getMessage();
            return false;
        }
        return true;
    }


}
