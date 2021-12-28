<?php
/**
 * Author: zsw iszsw@qq.com
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
     *
     * @var array
     */
    private $columns;

    /**
     * 搜索列
     *
     * @var
     */
    private $searchColumns;

    /**
     * 解析数据
     *
     * @var array
     */
    private $data = [];

    /**
     * 原始数据
     *
     * @var array
     */
    private $original = [];

    /**
     * @var Form
     */
    private $surfaceForm;

    private $fieldsType;

    protected function getFieldsType(?string $field = null)
    {
        if ( ! $this->fieldsType)
        {
            $this->fieldsType = Model::instance()->name($this->table['table'])->getFields();
        }

        return $field ? $this->fieldsType[$field] ?? null : $this->fieldsType;
    }

    private $fieldsBindType;

    protected function getFieldsBindType(?string $field = null)
    {
        if ( ! $this->fieldsBindType)
        {
            $this->fieldsBindType = Model::instance()->name($this->table['table'])->getFieldsType();
        }

        return $field ? $this->fieldsBindType[$field] ?? null : $this->fieldsBindType;
    }

    /**
     * 表单参数配置解析
     *
     * @param        $config
     * @param null   $default 默认值
     * @param string $type    类型
     * @param bool $search    搜索类型
     *
     * @return array
     */
    protected function resolveFormColumn(array $config, $default = null, $type = 'input', $search = false): array
    {
        return $this->resolveColumnByProps(Helper::paramsFormat($config[$search ? 'search_extend' : 'form_extend'] ?? []), $config, $default, $type);
    }

    protected function resolveColumnByProps(array $props, array $config, $default, $type = 'input')
    {
        if ( ! $type)
        {
            return [];
        }

        $column = [
            'type'    => $type,
            'field'   => $config['field'],
            'title'   => $config['title'],
            'value'   => is_null($default) ? $config['default'] ?? '' : $default,
            'props'   => $props,
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
                $in = [];
                if ($type === 'take') {
                    $in = (array)$column['value'];
                    if ( $config['option_type'] === 'option_relation' ) {
                        $column['props']['selection_label'] = $config['option_relation'][2];
                    }elseif( $config['option_type'] === 'option_remote_relation' ){
                        $column['props']['selection_label'] = $config['option_remote_relation'][6];
                    }
                }

                $column['options'] = Helper::formatOptions(
                    $this->options( $config[$config['option_type']] ?? '', $config['option_type'], $in)
                );
                break;
            default:
        }

        return $column;
    }

    protected function resolveColumns($search = false): void
    {
        if ( ! $this->surfaceForm)
        {
            $this->surfaceForm = Factory::Form();
        }
        $columns = [];
        foreach ($this->fields as $k => $config)
        {
            $type = $config[$search ? 'search_type' : 'form_type'];
            if ($type === "_")
            {
                continue;
            }
            $column = $this->resolveFormColumn($config, $this->data[$config['field']] ?? null, $type, $search);
            if (count($column))
            {
                if (count($config["form_format"]))
                {
                    $column['value'] = $this->invoke($config["form_format"], $column['value'], $this->data);
                }

                $cModel = $this->generateForm($column['type'], $column['field'], $column['title'], $column['value'], $column['props'], $column['options']);
                if ($config['marker'])
                {
                    $cModel->marker($config['marker']);
                }
                $columns[] = $cModel;
            }
        }
        if ($search)
        {
            $this->searchColumns = $columns;
        } else
        {
            $this->columns = $columns;
        }
    }

    private function generateForm($type, $prop, $label, $value, $props, ?array $options = null): Component
    {
        /**@var $component Component */
        $value = $value ?? '';
        $params = $type === 'hidden' ? [$prop, $value] : [$prop, $label, $value];
        $component = call_user_func_array([$this->surfaceForm, $type], $params);
        count($props) > 0 && $component->props($props);
        $options && $component->options($options);

        return $component;
    }

    public function setData($pk)
    {
        $this->original = is_array($pk) ? $pk : Model::instance()->name($this->table['table'])->findOrFail($pk);
        foreach ($this->fields as $v)
        {
            $this->data[$v['field']] = $this->resolveFormDefault($v, $this->original[$v['field']] ?? '', $this->original);
        }
    }

    /**
     * 默认值解析
     *
     * @param        $field
     * @param string $default 默认值
     * @param array  $data    默认值
     *
     * @return array
     */
    private function resolveFormDefault(array $field, $default = '', $data = [])
    {
        if ($field['relation'])
        { // 扩展字段
            $remote_relation = $field['option_remote_relation'];
            $default = Model::instance()->name($remote_relation[0])->where($remote_relation[2], $data[$remote_relation[1]])
                ->column($remote_relation[3]);
            $default = array_values($default);
        } else
        {
            switch ($field['form_type'])
            {
                case 'cascader':
                case 'select':
                case 'selects':
                case 'radio':
                case 'checkbox':
                case 'switcher':
                case 'upload':
                case 'take':
                    $default = json_decode($default, true) ?? $default;
                    break;
                default:
            }
        }

        return $default;
    }


    /**
     * @param bool $search 搜索页面
     *
     * @return array[]
     */
    public function getOptions($search = false)
    {
        $options = [];
        if ( ! $search)
        {
            $options['async'] = [
                'url' => Helper::builder_table_url('page/update/'.$this->table['table']),
            ];
        }

        return $options;
    }

    public function getColumns()
    {
        if ( ! $this->columns)
        {
            $this->resolveColumns(false);
        }

        return $this->columns;
    }

    public function getSearchColumns()
    {
        if ( ! $this->searchColumns)
        {
            $this->resolveColumns(true);
        }

        return $this->searchColumns;
    }

    public function save(array $post): bool
    {
        $model = Model::instance()->name($this->table['table']);
        try
        {
            $model->startTrans();
            $pkField = $this->table['pk'];
            $pk = $post[$pkField] ?? '';

            if ( ! $pk )
            {
                unset($post[$pkField]);
            }

            $relation = [];
            $fields = array_keys($this->getFieldsType());
            foreach ($post as $k => $v)
            {
                $field = $this->fields[$k] ?? null;
                if ($field['relation'])
                {
                    $relation[$k] = $v;
                } elseif (in_array($k, $fields))
                {
                    $post[$k] = $v = $this->invoke($field['save_format'], $v, $post);
                    if ($v !== null)
                    {
                        if (is_array($v))
                        {
                            $post[$k] = json_encode($v, JSON_UNESCAPED_UNICODE);
                        } else
                        {
                            $post[$k] = $this->detection($k, $v);
                        }
                        continue;
                    }
                }

                unset($post[$k]);
            }

            if ( ! $pk )
            {
                $pk = $model->insert($post, true);
            }else{
                $model->update($post);
            }

            // 远程更新
            if (count($relation) > 0)
            {
                foreach ($relation as $f => $val)
                {
                    $remote_relation_config = $this->fields[$f]['option_remote_relation'];
                    $middleModel = Model::instance()->name($remote_relation_config[0]);
                    $remoteModel = Model::instance()->name($remote_relation_config[4]);

                    $columns = $middleModel->where($remote_relation_config[2], $pk)->column($remote_relation_config[3], $remote_relation_config[1]);
                    $insert = [];
                    foreach ($val as $v)
                    {
                        if (false !== $index = array_search($v, $columns))
                        {
                            unset($columns[$index]);
                        } else
                        {
                            // 默认 数字为PK
                            if ( ! $remote_id = $remoteModel->where([is_numeric($v) ? $remote_relation_config[5] : $remote_relation_config[6] => $v])
                                ->value($remote_relation_config[5])
                            )
                            {
                                $remote_id = $remoteModel->insertGetId([$remote_relation_config[6] => $v]);
                            }
                            $insert[] = [
                                $remote_relation_config[2] => $pk,
                                $remote_relation_config[3] => $remote_id,
                            ];
                        }
                    }

                    if (count($insert) > 0)
                    {
                        $middleModel->insertAll($insert);
                    }
                    if (count($columns) > 0)
                    {
                        $middleModel->where($middleModel->getPk(), 'in', array_keys($columns))->delete();
                    }
                }
            }

            $model->commit();
        } catch (\Exception $e)
        {
            $model->rollback();

            $this->error = $e->getMessage() ?: '修改失败';

            return false;
        }

        return true;
    }

    /**
     * 字段类型检测
     *
     * @param $field
     * @param $value
     *
     * @return mixed
     */
    protected function detection($field, $value)
    {
        $type = $this->parseFieldType($this->getFieldsType($field)['type']);

        $value = $this->formatDatetime($field, $value, $type);

        $bindType = $this->getFieldsBindType($field);
        switch ($bindType){
            case 'int':
                $value = (int)$value;
                break;
        }

        return $value;
    }

    /**
     * 自动转换日期格式
     *
     * @param $field
     * @param $value
     * @param $type     数据库值类型
     *
     * @return mixed
     */
    protected function formatDatetime($field, $value, $type)
    {
        if (in_array($field, $this->table['datetime_fields']))
        {
            switch (strtolower($type))
            {
                case 'timestamp':
                case 'datetime':
                    $value = Custom::toDatetime($value);
                    break;
                case 'date':
                    $value = Custom::toDate($value);
                    break;
                case 'time':
                    $value = Custom::toTime($value);
                    break;
                case 'int':
                case 'bigint':
                case 'integer':
                    $value = Custom::toTimestamp($value);
                    break;
            }
        }

        return $value;
    }

    private function parseFieldType(string $type)
    {
        preg_match('/(\w+)?(\(\d+\))?/', $type, $match);

        return $match[1] ?? '';
    }


    /**
     * 删除数据
     *
     * @param array $post 主键数组
     *
     * @return bool
     */
    public function delete(array $post): bool
    {
        if ( empty($post) ) {
            return true;
        }

        $model = Model::instance()->name($this->table['table']);
        try
        {
            $model->startTrans();

            $model->whereIn($this->pk, $post)->delete();

            foreach ($this->fields as $k => $v) { // 关联字段 主动删除
                if ($v['relation']) {
                    $remote_relation_config = $v['option_remote_relation'];
                    $middleModel = Model::instance()->name($remote_relation_config[0]);
                    $middleModel->whereIn($remote_relation_config[2], $post)->delete();
                }
            }

            $model->commit();
        } catch (\Exception $e)
        {
            $model->rollback();

            $this->error = $e->getMessage() ?: '删除失败';

            return false;
        }

        return true;
    }


}
