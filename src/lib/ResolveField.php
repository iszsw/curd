<?php
/**
 * Author: zsw zswemail@qq.com
 * Date: 2019/11/25 10:11
 */

namespace iszsw\porter\lib;

class ResolveField extends Resolve
{

    /**
     * 列项
     * @var array
     */
    private $column;

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
     * 表格数据解析
     * Author: zsw zswemail@qq.com
     */
    protected function resolve()
    {
         $this->resolveColumn();
    }


    protected function resolveColumn()
    {
        foreach ($this->table['fields'] as $k => $config) {
            $column = $this->resolveFormColumn($config, $this->data[$config['field']] ?? null);

            if ($config["form_format"] ?? 0) {
                $column['value'] = $this->initFormat($config["form_format"], $column['value']);
            }

            $column && $this->column[] = $column;
        }
    }

    public function setData($pk)
    {
        $this->original = $this->data = Model::instance($this->table['table'])->findOrFail($pk);
        foreach ($this->table['fields'] as $v) {
            $this->data[$v['field']] = $this->resolveFormDefault($v, $this->data[$v['field']] ?? '', $this->original);
        }
    }

    /**
     * Form无需做默认配置
     * @return array
     */
    public function getDefault()
    {
        return [];
    }

    public function getColumn()
    {
        if (!$this->column) {
            $this->column = [];
            $this->resolveColumn();
        }
        return $this->column;
    }

}