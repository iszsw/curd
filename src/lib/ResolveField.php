<?php
/**
 * Author: zsw zswemail@qq.com
 *
 */

namespace iszsw\curd\lib;

use iszsw\curd\Helper;
use surface\Component;
use surface\Factory;
use surface\form\Form;

class ResolveField extends Resolve
{

    /**
     * 列项
     * @var array
     */
    private $columns;

    /**
     * 搜索列
     * @var
     */
    private $searchColumns;

    /**
     * 解析数据
     * @var array
     */
    private $data = [];

    /**
     * 原始数据
     * @var array
     */
    private $original = [];

    /**
     * @var Form
     */
    private $surfaceForm;

    /**
     * 表单参数配置解析
     *
     * @param      $field
     * @param null $default 默认值
     * @param string $type 类型
     *
     * @return array
     */
    protected function resolveFormColumn(array $field, $default = null, $type = 'input'): array
    {
        return $this->resolveColumnByProps($field['form_extend'], $field, $default, $type);
    }

    protected function resolveColumnByProps(array $props, array $field, $default, $type = 'input')
    {
        if ( ! $type )
        {
            return [];
        }

        $column = [
            'type'  => $type,
            'field' => $field['field'],
            'title' => $field['title'],
            'value' => is_null($default) ? $field['default'] ?? '' : $default,
            'props' => $props,
            'options' => null,
        ];

        switch ($type)
        {
            case 'cascader':
            case 'select':
            case 'radio':
            case 'checkbox':
            case 'switcher':
            case 'take':
                $in = $type === 'take' ? (array)$column['value'] : [];
                if ($field['relation'])
                { // 扩展字段
                    $column['options'] = Helper::formatOptions($this->options($field['option_remote_relation'], 'option_remote_relation', $in));
                } else
                {
                    $column['options'] = Helper::formatOptions($this->options($field[$field['option_type']] ?? '', $field['option_type'], $in));
                }
                break;
            default:
        }

        return $column;
    }

    protected function resolveColumns($search = false):void
    {
        if (!$this->surfaceForm) {
            $this->surfaceForm = Factory::Form();
        }
        $columns = [];
        foreach ($this->table['fields'] as $k => $config) {
            $type = $config[$search ? 'search_type' : 'form_type'];
            if ( $type === "_" ) continue;
            $column = $this->resolveFormColumn($config, $this->data[$config['field']] ?? null, $type);
            if (count($column) > 0) {
                if (count($config["form_format"]) > 0) {
                    $column['value'] = $this->initFormat($config["form_format"], $column['value']);
                }
                $columns[] = $this->generateForm($column['type'], $column['field'], $column['title'], $column['value'], $column['props'], $column['options']);
            }
        }
        if ($search) {
            $this->searchColumns = $columns;
        }else {
            $this->columns = $columns;
        }
    }

    private function generateForm($type, $prop, $label, $value, $props, ?array $options = null): Component
    {
        /**@var $component Component */
        $value = $value ?? '';
        if ($type === 'hidden') {
            $component = $this->surfaceForm->$type($prop, $value);
        }else{
            $component = $this->surfaceForm->$type($prop, $label, $value);
        }

        count($props) > 0 && $component->props($props);
        if (is_array($options)) {
            $component->options($options);
        }

        return $component;
    }

    public function setData($pk)
    {
        $this->original = $data = Model::instance($this->table['table'])->findOrFail($pk);
        foreach ($this->table['fields'] as $v) {
            $this->data[$v['field']] = $this->resolveFormDefault($v, $data[$v['field']] ?? '', $this->original);
        }
    }

    /**
     * @param bool $search 搜索页面
     *
     * @return array[]
     */
    public function getOptions($search = false)
    {
        $options = [];
        if (!$search) {
            $options['async'] = [
                'url' => Helper::builder_table_url('page/update', ['_table' => $this->table['table']]),
            ];
        }

        return $options;
    }

    public function getColumns()
    {
        if (!$this->columns) {
            $this->resolveColumns(false);
        }
        return $this->columns;
    }

    public function getSearchColumns()
    {
        if (!$this->searchColumns) {
            $this->resolveColumns(true);
        }
        return $this->searchColumns;
    }

}
