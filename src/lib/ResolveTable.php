<?php
/**
 * Author: zsw zswemail@qq.com
 * Date: 2019/11/25 10:11
 */

namespace iszsw\porter\lib;

use surface\table\Type;

class ResolveTable extends Resolve
{

    /**
     * 默认配置
     * @var array
     */
    private $default;

    /**
     * 搜索条件
     * @var array
     */
    private $search;

    /**
     * 列项
     * @var array
     */
    private $column;

    /**
     * 列解析
     */
    private function resolveColumn()
    {
        foreach ($this->table['fields'] as $k => $f)
        {
            if (!$f['table_type']) {
                continue;
            }

            $column = array_merge(json_decode($f['table_extend'], true) ?? [],
                                  [
                                      "type" => $f['table_type'],
                                      "field" => $f['field'],
                                      "title" => $f['title'],
                                      "sort" => $f['table_sort'] ? true : null,
                                  ]);

            switch ($f['table_type']) {
                case Type::IN:
                    $column['options'] = $this->options($f[$f['option_type']], $f['option_type']);
                    break;
                case Type::SWITCH_EDIT:
                case Type::SELECT_EDIT:
                    $column['options'] = $this->options($f[$f['option_type']], $f['option_type']);
                case Type::TEXT_EDIT:
                    !isset($column['edit_url']) && $column['edit_url'] = builder_table_url('change', ['table' => $this->table['table']]);
                    break;
                default:
            }
            $this->column[$k] = $column;
        }
    }

    /**
     * 搜索解析
     */
    private function resolveSearch()
    {
        foreach ($this->table['fields'] as $v) {
            if (!$v['search']) {
                continue;
            }
            if ($v['form_type'] == 'hidden') {
                $v['form_type'] = 'text';
            }
            $form = $this->resolveSearchColumn($v, input($v['field'], null));
            if (isset($form['options'])) {
                $form['props']['options'] = $form['options'];
            }
            $this->search[] = [$v['search'], $form['type'], $form['field'], $form['title'], $form['value'], ['props' => $form['props']]];
        }
    }

    /**
     * 按钮解析
     */
    private function resolveBtn()
    {
        $btn = json_decode($this->table['btn'], true);
        if (count($btn) > 0) {
            $defaultBtnNode = [1 => 'create', 2 => 'edit', 4 => 'delete'];
            $defaultBtnNum = 0;
            $topBtn = [];
            $operations = [];
            foreach ($btn as $k=>$v) {
                if (in_array($v, $defaultBtnNode)) {
                    $defaultBtnNum += array_search($v, $defaultBtnNode);
                    continue;
                }
                $btnDefault = [
                    'local' => 'right',
                    'type' => 'page',
                    'title' => '',
                    'params' => [],
                    'faClass' => 'fa fa-list',
                    'refresh' => true,
                    'text' => '',
                    'method' => 'POST',
                ];
                $v = explode(',', $v);
                $v = array_combine(array_keys($btnDefault), array_pad($v, count($btnDefault), null));
                foreach ($v as $kk => $vv) {
                    if ($vv == null) {
                        $v[$k] = $btnDefault[$kk];
                    }
                    if ($kk == 'params' && $vv) {
                        $v[$kk] = explode('|', $vv);
                    }
                }

                $k = builder_table_url($k);
                $btn = \surface\table\Table::button($v['type'], $v['title'], [
                    'params' => $v['params'],
                    'title' => '编辑',
                    'text' => $v['text'],
                    'method' => $v['method'],
                    'refresh' => $v['refresh'],
                    'url'   => $k,
                ], $v['faClass']);
                if ($v['local'] == 'top') {
                    $topBtn[] = $btn;
                }else{
                    $operations[] = $btn;
                }
            }
            $defaultBtn = array_merge_recursive(create_table_btn($defaultBtnNum, ['table'=>$this->table['table'], $this->table['pk']], 'page/'), ['topBtn' => $topBtn, 'operations' => $operations]);
            $this->default['topBtn'] = $defaultBtn['topBtn'];
            $this->default['operations'] = $defaultBtn['operations'];
        }
    }

    public function getSearch()
    {
        if (is_null( $this->search ) ) {
            $this->search = [];
            $this->resolveSearch();
        }
        return $this->search;
    }

    public function getDefault()
    {
        if (is_null( $this->default) ) {
            $this->default = json_decode($this->table['extend'], true) ?? [];
            $this->default['title'] = $this->table['title'];
            $this->default['description'] = $this->table['description'];
            $this->resolveBtn();
        }
        return $this->default;
    }

    public function getColumn(){
        if (is_null( $this->column ) ) {
            $this->resolveColumn();
        }
        return $this->column;
    }

    public function getData($where = [], $order = '', $page = 1, $row_num = 15): array
    {
        $model = Model::instance($this->table['table'])->where($where);
        $count = $model->count();
        $lists = $model->order($order)->page($page, $row_num)->select()->toArray();

        foreach ($lists as $k => $v) {
            foreach ($this->table['fields'] as $field => $config) {
                if (!$config['table_type']) {continue;}

                $value = $v[$field] ?? '';

                if ($config["option_type"])
                {
                    $row_pk = 0;
                    if ($config['relation']) {
                        $relation = $config['option_remote_relation'];
                        $row_pk = $v[$relation[1]];
                    }
                    $value = $this->initFieldVal($value, $config['table_type'], $config["option_type"], $config[$config['option_type']] ?? '', $row_pk);
                }

                if ($config["table_format"] ?? false) {
                    $value = $this->initFormat($config["table_format"], $value, $v);
                }

                $lists[$k][$field] = $value;
            }
        }

        return [
            'count' => $count,
            'list' => $lists
        ];
    }
}