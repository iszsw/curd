<?php
/**
 * Author: zsw zswemail@qq.com
 *
 */

namespace iszsw\curd\lib;

use iszsw\curd\Helper;
use surface\table\Type;

abstract class Resolve
{

    /**
     * @var table文件配置
     */
    protected $table;

    public function __construct($table)
    {
        $this->table = Manage::instance()->table($table);
        if ( ! $this->table)
        {
            throw new \Exception("表【{$table}】不存在");
        }
    }

    /**
     * 默认最大限制为100条 如果需要大量数据选择请使用 take组件
     *
     * @param       $val
     * @param       $type
     * @param array $values
     *
     * @return array
     */
    protected function options($val, $type, array $values = []): array
    {
        $option = [];
        switch ($type)
        {
            case 'option_remote_relation':
                if (count($val) === 7)
                {
                    $option = Model::instance($val[4])->where(count($values) > 0 ? [[$val[5], 'in', $values]] : [])->limit(100)->column($val[6], $val[5]);
                }
                break;
            case 'option_relation':
                if (count($val) === 3)
                {
                    $option = Model::instance($val[0])->where(count($values) > 0 ? [[$val[1], 'in', $values]] : [])->limit(100)->column($val[2], $val[1]);
                }
                break;
            case 'option_config':
                $option = json_decode($val, true) ?? [];
                break;
            case 'option_lang':
            default:
                $option = __('?'.$val) ? __($val) : [];
        }

        return $option;
    }

    /**
     * 初始化字段数据
     *
     * @param $val              值
     * @param $table_type       表格类型  text text_edit ....
     * @param $option_type      字段配置类型 option_default option_config...
     * @param $option_config    字段配置参数
     * @param $pk               string        当前列主键值
     *
     * @return string
     * Author: zsw zswemail@qq.com
     */
    protected function initFieldVal($val, $table_type, $option_type, $option_config, $pk = '')
    {
        $allow = ['column', 'expand'];
        if (in_array($table_type, $allow))
        {
            switch ($option_type)
            {
                case 'option_remote_relation':
                    $val = Model::instance($option_config[4])
                        ->where(
                            $option_config[5], 'IN', function ($query) use ($option_config, $pk)
                        {
                            $query->table($option_config[0])->where($option_config[2], $pk)->field($option_config[3]);
                        }
                        )
                        ->column($option_config[6], $option_config[5]);
                    break;
                case 'option_relation':
                    if (count($option_config) === 3)
                    {
                        $val = Model::instance($option_config[0])->where([$option_config[1] => $val])->value($option_config[2]);
                    }
                    break;
                case 'option_config':
                    $option = json_decode($option_config, true) ?? [];
                    $val = $option[$val];
                    break;
                case 'option_lang':
                    $option = __('?'.$option_config) ? __($option_config) : '';
                    $val = $option[$val];
            }
        }

        return $val;
    }

    /**
     * 搜索参数配置解析
     *
     * @param      $field
     * @param null $default 默认值
     *
     * @return array
     */
    protected function resolveSearchColumn(array $field, $default = null): array
    {
        return $this->resolveColumnByProps(json_decode($field['search_extend'], true) ?? [], $field, $default, $field['search_type']);
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
    protected function resolveFormDefault(array $field, $default = '', $data = [])
    {
        if ($field['relation'])
        { // 扩展字段
            $remote_relation = $field['option_remote_relation'];
            $default = Model::instance($remote_relation[0])->where($remote_relation[2], $data[$remote_relation[1]])->column($remote_relation[3]);
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

    protected function initFormat($methods, $val, ...$args)
    {
        return Format::parse($methods, $val, $args);
    }

    public function __get($name)
    {
        return $this->table[$name];
    }

}
