<?php

namespace iszsw\curd\controller\fields;

use iszsw\curd\Helper;
use iszsw\curd\lib\Manage;
use surface\Component;
use surface\form\components\Arrays;
use surface\form\components\Cascader;
use surface\form\components\Column;
use surface\form\components\Hidden;
use surface\form\components\Input;
use surface\form\components\Number;
use surface\form\components\Select;
use surface\form\components\Switcher;
use surface\helper\FormInterface;
use iszsw\curd\model\Table as TableModel;

class Form implements FormInterface
{

    public function options(): array
    {
        return [
            'resetBtn' => false,
            'async' => [
                'url' => '',
            ],
        ];
    }

    public function columns(): array
    {
        $table = input('table', '');
        $field = input('field', '');
        $create = !$field;
        $data = $create ? [] : Manage::instance()->field($table, $field);
        $formTypes = Helper::formatOptions(TableModel::getFormServersLabels());
        $tableTypes = Helper::formatOptions(TableModel::getTableServersLabels());

        // 外键默认值
        $remote_relation_state = ($create || ($data['relation'] ?? true));

        $searchChildren = [
            (new Select("search_type", TableModel::$labels["search_type"], $data['search_type'] ?? "0"))->options($formTypes),
            (new Select("search", TableModel::$labels["matching"], $data['search'] ?? '='))
                ->options(Helper::formatOptions(TableModel::$searchType))
                ->visible([['exec' => 'model.search_type !== "0"']]),
            (new Arrays('search_extend', TableModel::$labels['search_extend'], Helper::formatOptions($data['search_extend'] ?? [], TableModel::VALUE, TableModel::KEY)))->options(
                [
                    (new Input(TableModel::KEY, TableModel::$labels[TableModel::KEY]))->item(false),
                    (new Input(TableModel::VALUE, TableModel::$labels[TableModel::VALUE]))->item(false),
                ]
            )->marker('表单扩展props配置'),
        ];

        $tableChildren = [
            (new Select("table_type", TableModel::$labels["table_type"], $data['table_type'] ?? "0"))
                ->options($tableTypes),
            (new Select("table_format", TableModel::$labels["table_format"], $data['table_format'] ?? ''))
                ->props(['allow-create'=> true, 'filterable' => true, 'multiple' => true, 'default-first-option' => true])
                ->options(Helper::formatOptions(TableModel::$formatTypes))
                ->marker(
                    "表格中显示该字段时会触发 多选会按顺序依次执行<br>1.PHP方法,自定义方法,类静态方法（datetime | user_func | class::method）参数（当前值，当前列） <br>2.内容替换冒号开头"
                    .htmlspecialchars("(:<b>{data}</b>)")
                ),
            (new Arrays('table_extend', TableModel::$labels['table_extend'], Helper::formatOptions($data['table_extend'] ?? [], TableModel::VALUE, TableModel::KEY)))->options(
                [
                    (new Input(TableModel::KEY, TableModel::$labels[TableModel::KEY]))->item(false),
                    (new Input(TableModel::VALUE, TableModel::$labels[TableModel::VALUE]))->item(false),
                ]
            )->marker(' 表格<a target="_blank" href="https://element.eleme.cn/#/zh-CN/component/table">Table-column Attributes</a>配置 '),
        ];

        $formChildren = [
            (new Select("form_type", TableModel::$labels["form_type"], $data['form_type'] ?? "0"))
                ->options($formTypes),
            (new Select("form_format", TableModel::$labels["form_format"], $data['form_format'] ?? ''))
                ->props(['allow-create'=> true, 'filterable' => true, 'multiple' => true, 'default-first-option' => true])
                ->options(TableModel::$formatTypes)
                ->marker(
                    "表单中显示该字段时会触发 多选会按顺序依次执行<br>1.PHP方法,自定义方法,类静态方法（datetime | user_func | class::method）参数（当前值，当前列） <br>2.内容替换冒号开头"
                    .htmlspecialchars("(:<b>{data}</b>)")
                ),
            (new Arrays('form_extend', TableModel::$labels['form_extend'], Helper::formatOptions($data['form_extend'] ?? [], TableModel::VALUE, TableModel::KEY)))->options(
                [
                    (new Input(TableModel::KEY, TableModel::$labels[TableModel::KEY]))->item(false),
                    (new Input(TableModel::VALUE, TableModel::$labels[TableModel::VALUE]))->item(false),
                ]
            )->marker('表单扩展props配置')
        ];

        $saveChildren = [
            (new Select("save_format", TableModel::$labels["save_format"], $data['save_format'] ?? ""))
                ->props(['allow-create'=> true, 'filterable' => true, 'multiple' => true, 'default-first-option' => true])
                ->options(TableModel::$formatTypes)
                ->marker(
                    "保存该字段时会触发 多选会按顺序依次执行<br>1.PHP方法,自定义方法,类静态方法（datetime | user_func | class::method）参数（当前值，当前列） <br>2.内容替换冒号开头"
                    .htmlspecialchars("(:<b>{data}</b>)")
                ),
        ];

        $column = [
            (new Hidden('relation', (int)$remote_relation_state)),
            (new Input('field', TableModel::$labels['field'], $data['field'] ?? ""))->props(['readonly' => ! $create]),
            new Input('title', TableModel::$labels['title'], $data['title'] ?? ""),
            new Number('weight', TableModel::$labels['weight'], $data['weight'] ?? ""),
        ];

        if ($remote_relation_state)
        {
            array_splice(
                $column, 4, 0, [
                           (new Hidden('option_type', 'option_remote_relation')),
                           (new Hidden('table_sort', 0)),
                           (new Cascader(
                               'option_remote_relation', TableModel::$labels["option_remote_relation"], $data['option_remote_relation'] ?? []
                           ))->props(
                                   [
                                       'async' => [
                                           'url' => Helper::builder_table_url('fields/relation', ['table' => $table]),
                                       ],
                                   ]
                               )->marker((isset($data['option_remote_relation']) ? "当前值：" . implode('/', $data['option_remote_relation']) . " (el-Cascader存在同名问题待解决)<br>" : '') ."中间表 / {$table}主键 / 中间表与{$table}表的关联键 / 中间表与关联表的关联键 / 关联表 / 关联表主键 / 关联表可视字段名"),
                       ]
            );
        } else
        {

            $relation_tables = $childrenList = [];
            $tableNames = Manage::tableNames();
            foreach ($tableNames as $t)
            {
                $fields = Manage::instance()->fields($t['table']);
                $children = [];
                foreach ($fields as $f)
                {
                    $childrenData = [
                        'label' => $f['field'],
                        'value' => $f['field'],
                    ];
                    $childrenList[$t['table']][] = $childrenData;
                    $children[] = array_merge($childrenData, ['children' => &$childrenList[$t['table']]]);
                }

                $relation_tables[] = [
                    'label'    => $t['table'],
                    'value'    => $t['table'],
                    'children' => $children,
                ];
            }

            array_splice(
                $tableChildren, 2, 0,
                [
                    (new Switcher("table_sort", TableModel::$labels["table_sort"], $data['table_sort'] ?? 0))->options(Helper::formatOptions(TableModel::$statusLabels))
                        ->marker("启用之后可以在表格中使用该字段排序"),
                ]
            );

            array_splice(
                $column, 4, 0,
                [
                    new Input('default', TableModel::$labels['default'], $data['default'] ?? ""),

                    (new Select('option_type', TableModel::$labels["option_type"], $data['option_type'] ?? "option_default"))
                        ->options(Helper::formatOptions([
                                      'option_default' => TableModel::$labels["option_default"],
                                      'option_config' => TableModel::$labels["option_config"],
                                      'option_lang' => TableModel::$labels["option_lang"],
                                      'option_relation' => TableModel::$labels["option_relation"],
                                  ]))->marker('非‘默认’时 将读取相应配置 作为select radio等选项'),

                    (new Hidden('option_default', 1))->visible([['prop' => 'option_type', 'value' => 'option_default']]),
                    (new Arrays('option_config', TableModel::$labels['option_config'], $data['option_config'] ?: []))->options(
                        [
                            (new Input(TableModel::KEY, TableModel::$labels[TableModel::KEY]))->item(false),
                            (new Input(TableModel::VALUE, TableModel::$labels[TableModel::VALUE]))->item(false),
                        ]
                    )->visible([['prop' => 'option_type', 'value' => 'option_config']])->marker('手动填写选择项'),
                    (new Input('option_lang', TableModel::$labels["option_lang"], $data['option_lang'] ?? ""))
                        ->visible([['prop' => 'option_type', 'value' => 'option_lang']])
                        ->marker('读取语言包配置 (:status=>读取lang("status"))'),
                    (new Cascader('option_relation', TableModel::$labels["option_relation"], $data['option_relation'] ?? ""))
                        ->options($relation_tables)
                        ->visible([['prop' => 'option_type', 'value' => 'option_relation']])
                        ->marker('关联表/关联主键/关联可视字段'),
                ]
            );
        }

        array_push($column, (new Column('', TableModel::$labels['custom']))->el('el-tabs')
            ->children(
            [
                (new Component())->el('el-tab-pane')->item(false)
                    ->props('label', TableModel::$labels["search"])
                    ->children($searchChildren),
                (new Component())->el('el-tab-pane')->item(false)
                    ->props('label', TableModel::$labels["surface_table"])
                    ->children($tableChildren),
                (new Component())->el('el-tab-pane')->item(false)
                    ->props('label', TableModel::$labels["form"])
                    ->children($formChildren),
                (new Component())->el('el-tab-pane')->item(false)
                    ->props('label', TableModel::$labels["save"])
                    ->children($saveChildren),
            ]
        ));

        return $column;
    }

    public function save()
    {
        $post = input();
        try
        {
            $table = $post['table'];
            $field = $post['field'];
            $post['relation'] = !!$post['relation'];
            foreach (['form_extend', 'search_extend', 'table_extend'] as $k) {
                $post[$k] = Helper::formatValue(Helper::simpleOptions($post[$k]));
            }
            Manage::instance()->save(['table' => $table, 'fields' => [$field => $post]]);
        } catch (\Exception $e)
        {
            return '修改失败';
        }

        return true;
    }


}
