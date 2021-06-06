<?php
/**
 * Author: zsw zswemail@qq.com
 *
 */

namespace iszsw\curd\lib;

use iszsw\curd\Helper;
use surface\Component;
use surface\Factory;
use surface\table\components\Button;
use surface\table\components\Column;
use surface\table\components\Selection;
use surface\table\Table;
use surface\table\Type;
use iszsw\curd\model\Table as TableModel;

class ResolveTable extends Resolve
{

    use Condition;

    /**
     * 默认配置
     *
     * @var array
     */
    private $options;

    /**
     * 搜索条件
     *
     * @var array
     */
    private $search;

    /**
     * 列项
     *
     * @var array
     */
    private $column;

    /**
     * 按钮操作
     *
     * @var array
     */
    private $buttons;

    /**
     * @var Table
     */
    private $surfaceTable;

    /**
     * 获取列
     *
     * @return array<Component>
     */
    public function getColumn()
    {
        if (is_null($this->column))
        {
            $this->surfaceTable = Factory::table();
            $this->resolveColumn();
        }

        return $this->column;
    }

    /**
     * 列解析
     */
    private function resolveColumn(): void
    {
        $this->column = [];
        // Selection
        $this->column[] = new Selection($this->table['pk']);

        // columns
        foreach ($this->table['fields'] as $k => $f)
        {
            if ($f['table_type'] == '_')
            {
                continue;
            }

            $type = $f['table_type'];
            $prop = $f['field'];
            $label = $f['title'];
            $props = array_merge($f['table_extend'] ?? [], ["sortable" => $f['table_sort'] ? true : false,]);
            $options = [];

            switch ($f['table_type'])
            {
                case 'select':
                case 'switcher':
                    $options = $this->options($f[$f['option_type']], $f['option_type']);
                    break;
                default:
            }

            $this->column[] = $this->generateTable($type, $prop, $label, $props, $options);
        }

        // handle
        $this->column[] = (new Column('_options', '操作'))->props('fixed', 'right')->scopedSlots($this->getButtons(TableModel::LOCAL_RIGHT));
    }

    private function getButtons($local = TableModel::LOCAL_TOP)
    {
        if (is_null($this->buttons))
        {
            $this->resolveButton();
        }

        return $this->buttons[$local] ?? [];
    }

    private function generateTable($type, $prop, $label, $props, array $options = []): Component
    {
        $component = $this->surfaceTable->column($prop, $label);
        $component->props($props);
        switch ($type)
        {
            case 'select':
            case 'switcher':
            case 'writable':
                $child = $this->surfaceTable->$type()->props(
                    [
                        'options'     => $options,
                        'async'       => [
                            'method' => 'post',
                            'data'   => ['id'],
                            'url'    => Helper::builder_table_url('page/change', ['_table' => $this->table['table']]),
                        ],
                        'doneRefresh' => true,
                    ]
                );
                break;
            case 'expand':
                $component->props(['type' => 'expand']);
            default:
                $child = (new Component())->el('span')->inject(['domProps' => 'innerHTML']);
                break;
        }
        $component->scopedSlots([$child]);

        return $component;
    }

    public function getSearch()
    {
        if (is_null($this->search))
        {
            $this->search = [];
            $this->resolveSearch();
        }

        return $this->search;
    }

    private function resolveSearch()
    {
        foreach ($this->table['fields'] as $v)
        {
            if ( ! $v['search'])
            {
                continue;
            }
            if ($v['form_type'] == 'hidden')
            {
                $v['form_type'] = 'text';
            }
            $form = $this->resolveSearchColumn($v, input($v['field'], null));
            if (isset($form['options']))
            {
                $form['props']['options'] = $form['options'];
            }
            $this->search[] = [$v['search'], $form['type'], $form['field'], $form['title'], $form['value'], ['props' => $form['props']]];
        }
    }

    /**
     * 按钮解析
     */
    private function resolveButton(): void
    {
        $button = $this->table['button'];
        $this->buttons = [
            TableModel::LOCAL_TOP   => [],
            TableModel::LOCAL_RIGHT => [],
        ];

        if (count($this->table['fields']) > 0)
        {
            $i = 0;
            $fields = array_values($this->table['fields']);
            while (1)
            {
                if ( ! isset($fields[$i]))
                {
                    break;
                }
                if ($fields[$i]['search_type'] !== "_")
                {
                    array_unshift(
                        $button, [
                                   "icon"         => "el-icon-search",
                                   "title"        => TableModel::$labels['search'],
                                   "button_local" => TableModel::LOCAL_TOP,
                                   "top_type"     => TableModel::BTN_TYPE_PAGE,
                                   "url"          => Helper::builder_table_url('page/search', ['_table' => $this->table['table']]),
                               ]
                    );
                    break;
                }
                $i++;
            }
            unset($fields);
        }

        $btn = array_reverse($this->table['button_default']);
        foreach ($btn as $v)
        {
            switch ($v)
            {
                case TableModel::BUTTON_CREATE:
                    array_unshift(
                        $button, [
                                   "doneRefresh"  => true,
                                   "icon"         => "el-icon-plus",
                                   "title"        => TableModel::$buttonDefaultLabels[TableModel::BUTTON_CREATE],
                                   "button_local" => TableModel::LOCAL_TOP,
                                   "top_type"     => TableModel::BTN_TYPE_PAGE,
                                   "url"          => Helper::builder_table_url('page/create', ['_table' => $this->table['table']]),
                               ]
                    );
                    break;
                case TableModel::BUTTON_UPDATE:
                    array_unshift(
                        $button, [
                                   "doneRefresh"  => true,
                                   "icon"         => "el-icon-edit-outline",
                                   "title"        => TableModel::$buttonDefaultLabels[TableModel::BUTTON_UPDATE],
                                   "button_local" => TableModel::LOCAL_RIGHT,
                                   "right_type"   => TableModel::BTN_TYPE_PAGE,
                                   "data_extend"  => [$this->table['pk'],],
                                   "url"          => Helper::builder_table_url('page/update', ['_table' => $this->table['table']]),
                               ]
                    );
                    break;
                case TableModel::BUTTON_DELETE:
                    array_unshift(
                        $button, [
                                   "doneRefresh"  => true,
                                   "icon"         => "el-icon-close",
                                   "title"        => TableModel::$buttonDefaultLabels[TableModel::BUTTON_DELETE],
                                   "button_local" => TableModel::LOCAL_TOP,
                                   "top_type"     => TableModel::BTN_TYPE_SUBMIT,
                                   "confirm_msg"  => "确认删除？",
                                   "url"          => Helper::builder_table_url('page/delete', ['_table' => $this->table['table']]),
                               ]
                    );
                    array_unshift(
                        $button, [
                                   "doneRefresh"  => true,
                                   "icon"         => "el-icon-close",
                                   "title"        => TableModel::$buttonDefaultLabels[TableModel::BUTTON_DELETE],
                                   "button_local" => TableModel::LOCAL_RIGHT,
                                   "right_type"   => TableModel::BTN_TYPE_CONFIRM,
                                   "confirm_msg"  => "确认删除？",
                                   "data_extend"  => [$this->table['pk']],
                                   "url"          => Helper::builder_table_url('page/delete', ['_table' => $this->table['table']]),
                               ]
                    );
                    break;
                case TableModel::BUTTON_REFRESH:
                    array_unshift(
                        $button, [
                                   "icon"         => "el-icon-refresh",
                                   "title"        => TableModel::$buttonDefaultLabels[TableModel::BUTTON_REFRESH],
                                   "button_local" => TableModel::LOCAL_TOP,
                                   "top_type"     => TableModel::BTN_TYPE_REFRESH,
                               ]
                    );
                    break;
            }
        }

        foreach ($button as $b)
        {
            $this->buttons[$b['button_local']][] = $this->generateButton($b)->props('doneRefresh', $b['doneRefresh'] ?? false);
        }
    }

    private function generateButton(array $param): Button
    {
        $param = array_merge(
            [
                "icon"         => "el-icon-setting",
                "title"        => 'title',
                "button_local" => "right",
                "right_type"   => "page",
                "url"          => '',
                "data_extend"  => [],
                "btn_extend"   => [],
            ], $param
        );

        $btn = $this->surfaceTable->button($param['icon'], $param['title']);
        $type = $param[$param['button_local'].'_type'];
        switch ($type)
        {
            case TableModel::BTN_TYPE_PAGE:
                $btn->createPage($param['url'], $param['data_extend'])->props('doneRefresh', true);
                break;
            case TableModel::BTN_TYPE_CONFIRM:
                $btn->createConfirm($param['confirm_msg'] ?? '', ['method' => 'post', 'data' => $param['data_extend'], 'url' => $param['url']]);
                break;
            case TableModel::BTN_TYPE_REFRESH:
                $btn->createRefresh();
                break;
            case TableModel::BTN_TYPE_SUBMIT:
                $btn->createSubmit(
                    [
                        'method' => 'post',
                        'data'   => $param['data_extend'],
                        'url'    => $param['url'],
                    ], $param['confirm_msg'] ?? '', $this->table['pk']
                );
                break;
        }

        if (count($param['btn_extend']) > 0)
        {
            $btn->props('prop', array_merge($btn->props['prop'] ?? [], $param['btn_extend']));
        }

        return $btn;
    }

    public function getHeader(): ?Component
    {
        $buttons = $this->getButtons(TableModel::LOCAL_TOP);

        return count($buttons) < 1 ? null : (new Component())->children($buttons);
    }

    public function getOptions(): array
    {
        if (is_null($this->options))
        {
            $this->options = $this->table['extend'] ?? [];
            $this->options = isset($this->options['props']) ? $this->options : ['props' => count($this->options) > 0 ? $this->options : (object)[]];
        }

        return $this->options;
    }

    public function getPagination(): Component
    {
        return (new Component())->props(
            [
                'async' => [
                    'url' => Helper::builder_table_url('page', ['_table' => $this->table['table']]),
                ],
            ]
        );
    }

    public function getData($where = [], $order = '', $page = 1, $limit = 15): array
    {

        $condition = [];
        foreach ($where as $k => $v) {
            if (!($field = $this->table['fields'][$k] ?? null) || $field['search_type'] === '0') continue;
            $condition[] = $this->condition($field['search'], $k, $v);
        }

        $model = Model::instance($this->table['table'])->where($condition);
        $count = $model->count();
        $lists = $model->order($order ?: $this->table['pk'].' DESC')->page($page, $limit)->select()->toArray();

        foreach ($lists as $k => $v)
        {
            foreach ($this->table['fields'] as $field => $config)
            {
                if ( ! $config['table_type'])
                {
                    continue;
                }

                $value = $v[$field] ?? '';

                if ($config["option_type"])
                {
                    $row_pk = 0;
                    if ($config['relation'])
                    {
                        $relation = $config['option_remote_relation'];
                        $row_pk = $v[$relation[1]];
                    }
                    $value = $this->initFieldVal(
                        $value, $config['table_type'], $config["option_type"], $config[$config['option_type']] ?? '', $row_pk
                    );
                }

                if ($config["table_format"] ?? false)
                {
                    $value = $this->initFormat($config["table_format"], $value, $v);
                }

                if (is_array($value)) {
                    $value = implode(',', $value);
                }

                $lists[$k][$field] = $value;
            }
        }

        return [
            'count' => $count,
            'list'  => $lists,
        ];
    }
}
