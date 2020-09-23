<?php

namespace iszsw\porter\lib\engine;

use iszsw\porter\lib\Manage;
use iszsw\porter\model\Table;

/**
 * 文件引擎 配置文件保存到data/
 *
 * Class File
 *
 * @package iszsw\porter\lib\engine
 * Author: zsw zswemail@qq.com
 */
class File extends Manage
{

    private $suffix = '.php';

    private $config = [
        'save_path' => ''
    ];

    public function init($config = [])
    {
        $this->config = array_merge($this->config, $config);

        if (!is_dir($this->config['save_path']) || !is_writable($this->config['save_path'])) {
            throw new \Exception('数据表配置文件路径 '.$this->config['save_path'].' 不存在或者不可写');
        }
    }

    private function getTableFilePath($table)
    {
        return $this->config['save_path'] . $table . $this->suffix;
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
            $sort = array_column($fieldInfo,'sort');
            array_multisort($sort,SORT_DESC, $fieldInfo);
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

        foreach ($fields as $v) {
            if (isset($v['relation']) && $v['relation'] && count($v['option_remote_relation']) !== 7) {
                throw new \Exception(Table::$labels['option_remote_relation'] . "不能为空");
            }
        }

        foreach ($this->fieldsInfo($table) as $f) {
            $field = $f['field'];
            if (isset($oldFields[$field])) {
                $fields[$field] = array_merge($oldFields[$field], $fields[$field] ?? []);
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
        $sort = array_column($fields,'sort');
        array_multisort($sort,SORT_DESC, $fields);

        $data = array_merge($info, $data);
        $data['fields'] = $fields;

        return $this->saveData($table, $data);
    }

    /**
     * @param      $table 删除表
     * @param null $field 删除字段
     *
     * @return mixed|void
     * @throws \app\exception\BaseException
     * @throws \think\db\BindParamException
     * @throws \think\db\PDOException
     * Author: zsw zswemail@qq.com
     */
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
            throw new \app\exception\BaseException(__('File {:file} does not have {:type} permission', ['file'=>$file, 'type' => 'write']));
        }

        return true;
    }

}