<?php

namespace iszsw\curd\lib\engine;

use iszsw\curd\Helper;
use iszsw\curd\lib\Manage;
use iszsw\curd\model\Table;

/**
 * 文件引擎 配置文件保存到data/
 *
 * Class File
 *
 * @package iszsw\curd\lib\engine
 * Author: zsw zswemail@qq.com
 */
class File extends Manage
{

    private $suffix = '.php';

    private $path;

    public function init()
    {
        $tablePath = $this->config['save_path'];
        if (!is_dir($tablePath)) {
            mkdir($tablePath, 0777, true);
        }
        $this->path = $tablePath;
    }

    private function getTableFilePath($table)
    {
        return $this->path . $table . $this->suffix;
    }

    private function getData($table)
    {
        $tablePath = $this->getTableFilePath($table);
        return is_file($tablePath) ? include $tablePath : [];
    }

    public function table($table): array
    {
        return $this->tables($table)[0] ?? [];
    }

    public function tables($table = ''): array
    {
        $default = $this->tablesInfo($table);
        foreach ($default as $k => $v)
        {
            $tableData = $this->getData($v['table']);
            if (!is_array($tableData) || count($tableData) < 1) {
                $this->save($v);
                $tableData = $this->getData($v['table']);
            }
            $default[$k] = array_merge($v, $tableData);
        }
        return $default;
    }

    public function field($table, $field): array
    {
        return $this->fields($table, $field)[0] ?? [];
    }

    public function fields($table, $field = ''): array
    {
        $tableField = $this->table($table)['fields'] ?? [];
        if (!$tableField) {return [];}
        $fieldInfo = $this->fieldsInfo($table, $field);
        foreach ($fieldInfo as $k => $v)
        {
            if (!isset($tableField[$v['field']])) {
                $tableField[$v['field']] = [];
            }
            $fieldInfo[$k] = array_merge($v, $tableField[$v['field']]);
            unset($tableField[$v['field']]);
        }

        // 补充自定义字段
        foreach ($tableField as $v)
        {
            if ($v['relation'] && (!$field || $field === $v['field']))
            {
                $fieldInfo[] = $v;
            }
        }

        // fields 排序
        if (count($fieldInfo) > 1) {
            $sort = array_column($fieldInfo,'weight');
            array_multisort($sort,SORT_ASC, $fieldInfo);
        }

        return $fieldInfo;
    }

    public function save($data):bool
    {
        $table = $data['table'];
        $fields = $oldFields = [];
        if (isset($data['fields'])) {
            $fields = $data['fields'];
            unset($data['fields']);
        }
        $info = $this->getData($table);
        if (isset($info['fields'])) {
            $oldFields = $info['fields'];
            unset($data['fields']);
        }

        foreach ($fields as &$v) {
            if (isset($v['relation']) && $v['relation']) {
                if (count($v['option_remote_relation']) !== 7) {
                    throw new \Exception(Table::$labels['option_remote_relation'] . "不能为空");
                }
                $v['option_remote_relation'] = array_map(function ($r) {
                    return explode('.', $r)[0];
                }, $v['option_remote_relation']);
            }
        }
        unset($v);
        foreach ($this->fieldsInfo($table) as $f) {
            $field = $f['field'];
            if (isset($oldFields[$field])) {
                $fields[$field] = Helper::extends($oldFields[$field], $fields[$field] ?? [], true);
                unset($oldFields[$field]);
            }else{
                if ($f['key'] === 'PRI') { // 主键默认值
                    $data['pk'] = $field;
                }
                if (isset($fields[$field])) {
                    $fields[$field] = array_merge($f, $fields[$field]);
                }else{
                    $fields[$field] = $f;
                }
            }
        }

        // 自定义字段补充
        foreach ($oldFields as $k => $v) {
            if ($v['relation']) {
                $fields[$k] = array_merge($v, $fields[$k] ?? []);
            }
        }

        // fields 排序
        $sort = array_column($fields,'weight');
        array_multisort($sort,SORT_ASC, $fields);

        $data = array_merge($info, $data);
        $data['fields'] = $fields;

        // 格式化字段
        $data['extend'] = $data['extend'] ? Helper::simpleOptions($data['extend']) : [];

        return $this->saveData($table, $data);
    }

    public function delete($table, $fields = null)
    {
        foreach ((array)$table as $t) {
            $path = $this->getTableFilePath($t);
            if ($fields) {
                $info = require $path;
                is_array($fields) || $fields = (array)$fields;
                foreach ($fields as $f) {
                    unset($info['fields'][$f]);
                    if ($default = $this->fieldsInfo($t, $f)) { // 数据库字段自动补充
                        $info['fields'][$f] = $default[0];
                    }
                }
                $this->saveData($t, $info);
            } else {
                @unlink($path);
            }
        }
    }

    private function saveData($table, array $data)
    {
        $file = $this->getTableFilePath($table);
        $string = "<?php\r\n return " . var_export($this->checkTableContent($data), true) . ';';

        if ($handle = fopen($file, 'w')) {
            fwrite($handle, $string);
            fclose($handle);
        } else {
            throw new \Exception(__('File {:file} does not have {:type} permission', ['file'=>$file, 'type' => 'write']));
        }

        return true;
    }

}
