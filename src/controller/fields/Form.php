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
use surface\helper\FormAbstract;
use iszsw\curd\model\Table as TableModel;

class Form extends FormAbstract
{

    const CASCADER_SEPARATOR = '.';


    protected $table;

    protected $field;

    public function __construct($table, $field)
    {
        $this->table = $table;
        $this->field = $field;
    }

    public function columns(): array
    {
        $create = ! $this->field;
        $data = $create ? [] : Manage::instance()->field($this->table, $this->field);
        $formTypes = Helper::formatOptions(TableModel::getFormServersLabels());
        $tableTypes = Helper::formatOptions(TableModel::getTableServersLabels());

        // 外键默认值
        $remote_relation_state = ($create || ($data['relation'] ?? true));

        $searchChildren = [
            (new Select("search_type", TableModel::$labels["search_type"], $data['search_type'] ?? "_"))->options($formTypes),

            (new Select("search", TableModel::$labels["matching"], $data['search'] ?? '='))
                ->options(Helper::formatOptions(TableModel::$searchType))
                ->visible([['exec' => 'model.search_type !== "_"']]),

            (new Arrays(
                'search_extend', TableModel::$labels['search_extend'], Helper::formatOptions(
                $data['search_extend'] ?? [], TableModel::VALUE, TableModel::KEY
            )
            ))->options(
                [
                    (new Input(TableModel::KEY, TableModel::$labels[TableModel::KEY]))->item(false),
                    (new Input(TableModel::VALUE, TableModel::$labels[TableModel::VALUE]))->item(false),
                ]
            )
                ->visible([['exec' => 'model.search_type !== "_"']])
                ->marker('表单扩展props配置'),
        ];

        $tableChildren = [
            (new Select("table_type", TableModel::$labels["table_type"], $data['table_type'] ?? "_"))
                ->options($tableTypes),
            (new Select("table_format", TableModel::$labels["table_format"], $data['table_format'] ?? ''))
                ->props(['allow-create' => true, 'filterable' => true, 'multiple' => true, 'default-first-option' => true])
                ->options(Helper::formatOptions(TableModel::$formatTypes))
                ->marker(
                    "表格中显示该字段时会触发 多选会按顺序依次执行<br>1.公共方法,类静态方法（ func | \\namespace\Class::method ）参数（当前值，&当前列）：value <br>2.内容替换冒号开头"
                    .htmlspecialchars("(:<b>{data}</b>)")
                ),
            (new Arrays(
                'table_extend', TableModel::$labels['table_extend'], Helper::formatOptions(
                $data['table_extend'] ?? [], TableModel::VALUE, TableModel::KEY
            )
            ))->options(
                [
                    (new Input(TableModel::KEY, TableModel::$labels[TableModel::KEY]))->item(false),
                    (new Input(TableModel::VALUE, TableModel::$labels[TableModel::VALUE]))->item(false),
                ]
            )->marker(' 表格<a target="_blank" href="https://element.eleme.cn/#/zh-CN/component/table">Table-column Attributes</a>配置 '),
        ];

        $formChildren = [
            (new Select("form_type", TableModel::$labels["form_type"], $data['form_type'] ?? "_"))
                ->options($formTypes),
            (new Input("marker", TableModel::$labels['marker'], $data['marker'] ?? ''))->props(['type' => 'textarea'])->marker('提示文本会显示在表单下面，支持HTML'),
            (new Select("form_format", TableModel::$labels["form_format"], $data['form_format'] ?? ''))
                ->props(['allow-create' => true, 'filterable' => true, 'multiple' => true, 'default-first-option' => true])
                ->options(Helper::formatOptions(TableModel::$formatTypes))
                ->marker(
                    "表单中显示该字段时会触发 多选会按顺序依次执行<br>1.公共方法,类静态方法（ func | \\namespace\Class::method ）参数（当前值，当前列）：value <br>2.内容替换冒号开头"
                    .htmlspecialchars("(:<b>{data}</b>)")
                ),
            (new Arrays(
                'form_extend', TableModel::$labels['form_extend'], Helper::formatOptions(
                $data['form_extend'] ?? [], TableModel::VALUE, TableModel::KEY
            )
            ))->options(
                [
                    (new Input(TableModel::KEY, TableModel::$labels[TableModel::KEY]))->item(false),
                    (new Input(TableModel::VALUE, TableModel::$labels[TableModel::VALUE]))->item(false),
                ]
            )->marker('表单扩展props配置'),
        ];

        $saveChildren = [
            (new Select("save_format", TableModel::$labels["save_format"], $data['save_format'] ?? ""))
                ->props(['allow-create' => true, 'filterable' => true, 'multiple' => true, 'default-first-option' => true])
                ->options(Helper::formatOptions(TableModel::$formatTypes))
                ->marker(
                    "保存该字段时会触发 多选会按顺序依次执行<br>2.公共方法,类静态方法（ func | \\namespace\Class::method ）参数（当前值，当前列）：value | null(禁止修改)  <br>2.内容替换冒号开头"
                    .htmlspecialchars("(:<b>{data}</b>)")
                ),
        ];

        $column = [
            (new Hidden('relation', (int)$remote_relation_state)),
            (new Input('field', TableModel::$labels['field'], $data['field'] ?? ""))->props(['readonly' => ! $create]),
            new Input('title', TableModel::$labels['title'], $data['title'] ?? ""),
            (new Number('weight', TableModel::$labels['weight'], $data['weight'] ?? ""))->marker('权重越高字段显示越靠前'),
        ];

        if ($remote_relation_state)
        {
            $option_remote_relation = $data['option_remote_relation'] ?? [];
            foreach ($option_remote_relation as $k => &$v)
            {
                $v .= ".{$k}";
            }
            unset($v);

            array_splice(
                $column, 4, 0, [
                           (new Hidden('option_type', 'option_remote_relation')),
                           (new Hidden('table_sort', 0)),
                           (new Cascader(
                               'option_remote_relation', TableModel::$labels["option_remote_relation"], $option_remote_relation
                           ))
                               ->style('width', '100%')
                               ->props(
                                   [
                                       'async' => [
                                           'url' => Helper::builder_table_url('fields/relation/'.$this->table),
                                       ],
                                   ]
                               )->marker("中间表 / {$this->table}主键 / 中间表与{$this->table}表的关联键 / 中间表与关联表的关联键 / 关联表 / 关联表主键 / 关联表可视字段名"),
                       ]
            );
        } else
        {

            $relation_tables = $childrenList = [];
            $tableNames = Manage::tableNames();
            foreach ($tableNames as $t)
            {
                $fields = Manage::instance()->fields($t);
                $children = [];
                foreach ($fields as $f)
                {
                    $childrenData = [
                        'label' => $f['field'],
                        'value' => $f['field'],
                    ];
                    $childrenList[$t][] = $childrenData;
                    $children[] = array_merge($childrenData, ['children' => &$childrenList[$t]]);
                }

                $relation_tables[] = [
                    'label'    => $t,
                    'value'    => $t,
                    'children' => $children,
                ];
            }

            array_splice(
                $tableChildren, 2, 0,
                [
                    (new Switcher(
                        "table_sort", TableModel::$labels["table_sort"], $data['table_sort'] ?? 0
                    ))->options(Helper::formatOptions(TableModel::$statusLabels))
                        ->marker("启用之后可以在表格中使用该字段排序"),
                ]
            );

            array_splice(
                $column, 4, 0,
                [
                    new Input('default', TableModel::$labels['default'], $data['default'] ?? ""),

                    (new Select('option_type', TableModel::$labels["option_type"], $data['option_type'] ?? "option_default"))
                        ->options(
                            Helper::formatOptions(
                                [
                                    'option_default'  => TableModel::$labels["option_default"],
                                    'option_config'   => TableModel::$labels["option_config"],
                                    'option_lang'     => TableModel::$labels["option_lang"],
                                    'option_relation' => TableModel::$labels["option_relation"],
                                ]
                            )
                        )->marker('非‘默认’时 将读取相应配置 作为select radio等选项'),

                    (new Hidden('option_default', 1))->visible([['prop' => 'option_type', 'value' => 'option_default']]),
                    (new Arrays(
                        'option_config', TableModel::$labels['option_config'], Helper::formatOptions(
                        $data['option_config'] ?? [], TableModel::VALUE, TableModel::KEY
                    )
                    ))->options(
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

        array_push(
            $column, (new Column('', TableModel::$labels['custom']))->el('el-tabs')
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
            )
        );

        return $column;
    }

    public function save(): bool
    {
        $post = input();
        try
        {
            $post['relation'] = ! ! $post['relation'];
            foreach (['form_extend', 'search_extend', 'table_extend', 'option_config'] as $k)
            {
                if (isset($post[$k]))
                {
                    $post[$k] = Helper::simpleOptions($post[$k]);
                }
            }

            // 解决 Element Cascader 组件value唯一问题
            if (isset($post['option_remote_relation']))
            {
                $separator = self::CASCADER_SEPARATOR;
                $post['option_remote_relation'] = array_map(
                    function ($r) use ($separator)
                    {
                        return explode($separator, $r)[0];
                    }, $post['option_remote_relation']
                );
            }

            Manage::instance()->save(['table' => $this->table, 'fields' => [$this->field => $post]]);
        } catch (\Exception $e)
        {
            $this->error = $e->getMessage();

            return false;
        }

        return true;
    }


}
