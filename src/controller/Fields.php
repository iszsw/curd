<?php

namespace iszsw\porter\controller;

use iszsw\porter\lib\Manage;
use surface\form\Form;
use surface\helper\tp\Curd;
use iszsw\porter\model\Table as TableModel;
use surface\helper\tp\FormInterface;
use surface\helper\tp\TableInterface;

class Fields
{

    use Curd;

    protected function getTableModel()
    {
        return new class implements TableInterface
        {

            private $table;

            public function __construct()
            {
                $this->table = input('table');
            }

            public function rules(): array
            {
                return [];
            }

            public function defaults(): array
            {
                $btn = create_table_btn(7, ['field', 'table' => $this->table], 'fields/');

                return array_merge(
                    $btn, [
                    'pk'          => "field",
                    'title'       => $this->table,
                    'description' => '表字段管理',
                    'pageShow'    => false,
                    'searchShow'  => true,
                ]
                );
            }

            public function column(): array
            {
                [$formTypes, $tableTypes] = TableModel::getServersLabels();

                $editUrl = builder_table_url('fields/change', ['table' => $this->table]);

                return [
                    'sort'        => [
                        'type'             => 'textEdit',
                        'title'            => TableModel::$labels['sort'],
                        'width'            => '30px',
                        'edit_url'         => $editUrl,
                        'editRefreshAfter' => true,
                    ],
                    "field_label" => TableModel::$labels['field'],
                    'title'       => [
                        'type'     => 'textEdit',
                        'title'    => TableModel::$labels['title'],
                        'edit_url' => $editUrl,
                    ],
                    //                    "type" => TableModel::$labels['type'],
                    'form_type'   => [
                        'type'     => 'selectEdit',
                        'title'    => TableModel::$labels['form_type'],
                        'options'  => $formTypes,
                        'edit_url' => $editUrl,
                    ],
                    'table_type'  => [
                        'type'     => 'selectEdit',
                        'title'    => TableModel::$labels['table_type'],
                        'options'  => $tableTypes,
                        'edit_url' => $editUrl,
                    ],
                    'search'      => [
                        'title'    => TableModel::$labels['search'],
                        'type'     => 'selectEdit',
                        'edit_url' => $editUrl,
                        'options'  => array_merge([0 => '禁用'], TableModel::$searchType),
                    ],
                    'table_sort'  => [
                        'title'    => TableModel::$labels['table_sort'],
                        'type'     => 'switchEdit',
                        'edit_url' => $editUrl,
                        'options'  => TableModel::$statusLabels,
                    ],
                    "null"        => TableModel::$labels['null'],
                    "default"     => TableModel::$labels['default'],
                ];
            }

            public function search($where = [], $order = '', $page = 1, $row_num = 15): array
            {
                $list = Manage::instance()->fields($this->table);
                foreach ($list as &$v)
                {
                    $v['field_label'] = $v['field'].((isset($v['key']) && $v['key']) ? "【{$v['key']}】" : '');
                }

                return [
                    'list' => $list,
                ];
            }
        };
    }

    /**
     * @return FormInterface|__anonymous@4120
     */
    protected function getFormModel()
    {
        return new class() implements FormInterface
        {

            public function defaults(): array
            {
                return [];
            }

            public function column(): array
            {
                $table = input('table', '');
                $field = input('field', '');
                $create = ! $field;
                $data = $create ? [] : Manage::instance()->field($table, $field);
                [$formTypes, $tableTypes] = TableModel::getServersLabels();

                $relation_tables = $childrenList = $remote_relation = [];
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

                    $remote_relation[] = [
                        'label'    => $t['table'],
                        'value'    => $t['table'],
                        'children' => [''],
                    ];
                }

                // 外键默认值
                $remote_relation_state = ($create || ($data['relation'] ?? true));
                if ($remote_relation_state && isset($data['option_remote_relation']))
                {
                    $remote_relation_list = $data['option_remote_relation'];
                    $current = array_shift($remote_relation_list);
                    foreach ($remote_relation as $k => $v)
                    {
                        if ($v['value'] === $current)
                        {
                            $default = [];
                            while (1)
                            {
                                if (count($remote_relation_list) === 0)
                                {
                                    break;
                                }
                                $selTable = array_pop($remote_relation_list);
                                $default = [
                                    'label'    => $selTable,
                                    'value'    => $selTable,
                                    'children' => $default ? [$default] : [],
                                ];
                            }
                            $remote_relation[$k]['children'] = [$default];
                        }
                    }
                }

                $searchChildren = [
                    Form::select("search_type", TableModel::$labels["search_type"], $data['search_type'] ?? 0)->addOptions($formTypes),
                    Form::select("search", TableModel::$labels["search"], $data['search'] ?? 0)
                        ->addOptions(array_merge([0 => '禁用'], TableModel::$searchType)),
                    Form::json("search_extend", TableModel::$labels["search_extend"], $data['search_extend'] ?? "")
                        ->props(['mark' => '搜索表单扩展props配置 type => datetimerange']),
                ];

                $tableChildren = [
                    Form::select("table_type", TableModel::$labels["table_type"], $data['table_type'] ?? 0)->addOptions($tableTypes),
                    Form::selects("table_format", TableModel::$labels["table_format"], $data['table_format'] ?? '')->props(
                        [
                            'filterable'  => true,
                            'allowCreate' => true,
                            'mark'        => "表格中显示该字段时会触发 多选会按顺序依次执行<br>1.PHP方法,自定义方法,类静态方法（datetime | user_func | class,method）参数（当前值，当前列） <br>2.内容替换冒号开头"
                                .htmlspecialchars("(:<b>{data}</b>)"),
                        ]
                    )->addOptions(TableModel::$formatTypes),
                    Form::json("table_extend", TableModel::$labels["table_extend"], $data['table_extend'] ?? "")
                        ->props(['mark' => '表格扩展Column配置 width|edit_url自定义提交地址不填写为默认地址|align']),
                ];

                $formChildren = [
                    Form::select("form_type", TableModel::$labels["form_type"], $data['form_type'] ?? 0)->addOptions($formTypes),
                    Form::selects("form_format", TableModel::$labels["form_format"], $data['form_format'] ?? '')->props(
                        [
                            'filterable'  => true,
                            'allowCreate' => true,
                            'mark'        => "表单中显示该字段时会触发 多选会按顺序依次执行<br>1.PHP方法,自定义方法,类静态方法（datetime | user_func | class,method）参数（当前值，当前列） <br>2.内容替换冒号开头"
                                .htmlspecialchars("(:<b>{data}</b>)"),
                        ]
                    )->addOptions(TableModel::$formatTypes),
                    Form::json("form_extend", TableModel::$labels["form_extend"], $data['form_extend'] ?? "")
                        ->props(['mark' => '表单扩展props配置 readonly => true']),
                ];

                $saveChildren = [
                    Form::selects("save_format", TableModel::$labels["save_format"], $data['save_format'] ?? '')->props(
                        [
                            'filterable'  => true,
                            'allowCreate' => true,
                            'mark'        => "保存该字段时会触发 多选会按顺序依次执行<br>1.PHP方法,自定义方法,类静态方法（datetime | user_func | class,method）参数（当前值，当前列） <br>2.内容替换冒号开头"
                                .htmlspecialchars("(:<b>{data}</b>)"),
                        ]
                    )->addOptions(TableModel::$formatTypes)
                ];

                $column = [
                    Form::hidden("relation", '', $data['relation'] ?? true),
                    Form::text("field", TableModel::$labels["field"], $data['field'] ?? "")->props(['readonly' => ! $create]),
                    Form::text("title", TableModel::$labels["title"], $data['title'] ?? ""),
                    Form::number("sort", TableModel::$labels["sort"], $data['sort'] ?? 0),
                ];

                if ($remote_relation_state)
                {
                    array_splice(
                        $column, 4, 0, [
                        Form::hidden("option_type", '', 'option_remote_relation'),
                        Form::hidden("table_sort", '', 0),
                        Form::cascader('option_remote_relation', TableModel::$labels["option_remote_relation"], $data['option_remote_relation'] ?? "")
                            ->props(
                                [
                                    'lazy'    => true,
                                    'url'     => builder_table_url('fields/relation', ['table' => $table]),
                                    'mark'    => "中间表/{$table}外键/中间表与{$table}表的关联键/中间表与关联表的关联键/关联表/关联表外键/关联表可视字段名",
                                    'options' => $remote_relation,
                                ]
                            ),

                    ]
                    );
                } else
                {
                    array_splice(
                        $tableChildren, 2, 0,
                        [
                            Form::switcher("table_sort", TableModel::$labels["table_sort"], $data['table_sort'] ?? 0)
                                ->addOptions(TableModel::$statusLabels),
                        ]
                    );

                    array_splice(
                        $column, 4, 0, [
                        Form::text("default", TableModel::$labels["default"], $data['default'] ?? ""),
                        Form::tab(
                            'option_type', TableModel::$labels["option"], $data['option_type'] ?? "", [
                            'children' => [
                                'option_default'  => [
                                    'title'    => TableModel::$labels["option_default"],
                                    'children' => [
                                        Form::hidden("option_default", '', ''),
                                    ],
                                ],
                                'option_config'   => [
                                    'title'    => TableModel::$labels["option_config"],
                                    'children' => [
                                        Form::json("option_config", TableModel::$labels["option_config"], $data['option_config'] ?? "")
                                            ->props(['mark' => '手动填写选择项']),
                                    ],
                                ],
                                'option_lang'     => [
                                    'title'    => TableModel::$labels["option_lang"],
                                    'children' => [
                                        Form::text('option_lang', TableModel::$labels["option_lang"], $data['option_lang'] ?? "")->props(
                                            [
                                                'mark' => '读取语言包配置 (:status=>读取lang("status"))',
                                            ]
                                        ),
                                    ],
                                ],
                                'option_relation' => [
                                    'title'    => TableModel::$labels["option_relation"],
                                    'children' => [
                                        Form::cascader('option_relation', '选择', $data['option_relation'] ?? "")->props(
                                            [
                                                'mark'    => '关联表/关联主键/关联可视字段',
                                                'options' => $relation_tables,
                                            ]
                                        ),
                                    ],
                                ],
                            ],
                        ]
                        )->props(['mark' => '非‘默认’时 将读取相应配置 作为select switcher参数',]),
                    ]
                    );

                }

                array_push($column, Form::tab('_config', '其他', '_config_search', ['children' => [
                    [
                        'name'     => '_config_search',
                        'title'    => '搜索',
                        'children' => $searchChildren,
                    ],
                    [
                        'name'     => '_config_table',
                        'title'    => '表格',
                        'children' => $tableChildren,
                    ],
                    [
                        'name'     => '_config_form',
                        'title'    => '表单',
                        'children' => $formChildren,
                    ],
                    [
                        'name'     => '_config_save',
                        'title'    => '保存',
                        'children' => $saveChildren,
                    ],
                ]]));

                return $column;
            }

            public function save()
            {
                $post = input();
                try
                {
                    $table = $post['table'];
                    $field = $post['field'];
                    Manage::instance()->save(['table' => $table, 'fields' => [$field => $post]]);
                } catch (\Exception $e)
                {
                    return json(['code' => 1, 'msg'  => "保存失败", 'data' => []]);
                }
                return json(['code' => 0, 'msg'  => "保存成功", 'data' => []]);
            }
        };
    }

    /**
     * @param $value
     * @param $table 当前表
     *
     * @throws \think\db\BindParamException
     * @throws \think\db\PDOException
     */
    public function relation($value, $table)
    {
        $data = [];
        switch (count($value))
        {
            case 1: // 本表外键
                $data = $this->getRelationFields($table);
                break;
            case 2: // 中间表与本表的关联键
                $data = $this->getRelationFields($value[0]);
                break;
            case 3: // 中间表与关联表的关联键
                $data = $this->getRelationFields($value[0]);
                break;
            case 4: // 关联表
                foreach (Manage::tableNames() as $t)
                {
                    $data[] = [
                        'label'    => $t['table'],
                        'value'    => $t['table'],
                        'children' => [''],
                    ];
                }
                break;
            case 5: // 关联表外键
                $data = $this->getRelationFields($value[4]);
                break;
            case 6: // 关联表可视字段名
                $data = $this->getRelationFields($value[4], false);
                break;
        }
        return json(['code' => 0, 'msg'  => "success", 'data' => $data]);
    }

    private function getRelationFields($table, $children = true)
    {
        $data = [];
        foreach (Manage::instance()->fields($table) as $v)
        {
            if ( ! $v['relation'])
            {
                $data[] = [
                    'label'    => $v['field'],
                    'value'    => $v['field'],
                    'children' => $children ? [''] : '',
                ];
            }
        }

        return $data;
    }

    public function index()
    {
        return $this->createTable($this->getTableModel());
    }

    public function edit()
    {
        return $this->createForm($this->getFormModel());
    }

    /**
     * 删除配置
     * Author: zsw zswemail@qq.com
     */
    public function delete()
    {
        $table = input('table');
        $field = input('field');
        if ( ! $field)
        {
            return json(['code' => 1, 'msg'  => "请选择需要删除的字段", 'data' => []]);
        }
        Manage::instance()->delete($table, $field);
        return json(['code' => 0, 'msg'  => "删除成功", 'data' => []]);
    }

    public function change()
    {
        $field = input('field');
        $table = input('table');
        $post = request()->only(['title', 'sort', 'form_type', 'search', 'table_type', 'table_sort']);
        try
        {
            if ( ! $table || ! $field || ! $post)
            {
                throw new \Exception();
            }
            Manage::instance()->save(['table' => $table, 'fields' => [$field => $post]]);
        } catch (\Exception $e)
        {
            return json(['code' => 1, 'msg'  => $e->getMessage(), 'data' => []]);
        }
        return json(['code' => 0, 'msg'  => "保存成功", 'data' => []]);
    }

}
