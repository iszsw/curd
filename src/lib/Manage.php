<?php
/**
 * Author: zsw zswemail@qq.com
 * Date: 2019/11/25 10:11
 */

namespace iszsw\porter\lib;

use iszsw\porter\lib\engine\File;
use \iszsw\porter\model\Table as TableModel;

abstract class Manage
{

    private static $engines = [
        'file' => File::class
    ];

    /**
     * 数据库名字位置
     */
    const TABLE_NAME_CONFIG = 'connections.mysql.database';

    protected $tableContent = [
        'table',            // 表
        'pk',               // 索引
        'title',            // 标题
        'description',      // 描述
        'auto_timestamp',   // 自动时间戳
        'page',             // int 分页
        'btn',              // json 按钮
        'extend',           // json 扩展
        'fields'            // 所有字段配置
    ];

    protected $fieldContent = [
        'sort',             // 排序
        'field',            // 字段
        'title',            // 名称
        'relation',         // 关联字段
        'form_type',        // 表单样式
        'table_type',       // 表格样式
        'default',          // 默认值
        'search',           // 搜索
        'search_type',      // 搜索表单类型
        'search_extend',    // 搜索扩展
        'table_extend',     // 扩展
        'form_extend',      // 扩展
        'save_format',      // 保存格式化
        'form_format',      // 表单格式化
        'table_format',     // 表格格式化
        'table_sort',       // 表格排序
        'option_type',      // 参数类型
        'option_default',   // 默认无值
        'option_config',    // 自定义参数
        'option_lang',      // 语言包参数
        'option_relation',  // 关联参数
        'option_remote_relation',  // 关联参数
    ];

    private function __construct(){}

    /**
     * @param string $engine
     *
     * @return self
     * @throws \Exception
     * Author: zsw zswemail@qq.com
     */
    final public static function instance()
    {
        $engine = config('porter.save', 'file');
        $engineClass = static::$engines[$engine] ?? '';
        if (!class_exists($engineClass)) {
            throw new \Exception('class ['.$engineClass.'] does not exient');
        }

        $instance = new $engineClass();
        if (!$instance instanceof static) {
            throw new \Exception('class ['.$engineClass.'] is not an instance of ' . static::class);
        }
        $instance->init(config('porter.engine.'.$engine, []));
        return $instance;
    }

    public function init(){}

    protected static $db;
    /**
     * @return \think\Db | \think\DbManager | \think\db\connector\Mysql
     * Author: zsw zswemail@qq.com
     */
    protected static function getDb()
    {
        return static::$db ?? static::$db = app()->db;
    }

    /**
     * 所有tableName
     *
     * @return array
     * @throws \think\db\BindParamException
     * @throws \think\db\PDOException
     */
    public static function tableNames()
    {
        $sql = "SELECT TABLE_NAME as `table` FROM information_schema.TABLES WHERE TABLE_SCHEMA = :database ";
        return self::getDb()->query($sql, ['database'=>static::getDb()->getConfig(self::TABLE_NAME_CONFIG)]);
    }

    /**
     * 所有数据库表详情
     *
     * @param string $table
     *
     * @return array
     * @throws \think\db\BindParamException
     * @throws \think\db\PDOException
     */
    protected function tablesInfo($table = '')
    {
        if ($table) {
            $table = static::filter($table);
            $table = " AND TABLE_NAME = '{$table}'";
        }
        $sql = "SELECT TABLE_NAME as `table`,TABLE_COMMENT as `comment`,TABLE_ROWS as `rows`,ENGINE as `engine` FROM information_schema.TABLES WHERE TABLE_SCHEMA = :table " . $table;
        $list = self::getDb()->query($sql, ['table'=>self::getDb()->getConfig(self::TABLE_NAME_CONFIG)]);

        foreach ($list as $k => $v)
        {
            $list[$k] = [
                'table'         => $v['table'],
                'title'         => $v['comment'],
                'description'   => $v['comment'],
                'rows'          => $v['rows'],
                'engine'        => $v['engine'],
                'auto_timestamp'=> 1,
                'page'          => TableModel::ENABLE,
                'fields'        => [],
                'extend'        => '',
                'pk'            => 'id',
                'btn'           => json_encode([
                                                   'create' => 'create',
                                                   'edit'   => 'edit',
                                                   'delete' => 'delete',
                                               ], JSON_UNESCAPED_UNICODE),
            ];
        }

        return $list;
    }

    /**
     * 所有表中字段详情
     *
     * @param string $table
     * @param string $field
     *
     * @return array
     * @throws \think\db\BindParamException
     * @throws \think\db\PDOException
     */
    protected function fieldsInfo($table, $field = '')
    {
        $fields = self::getDb()->query("SHOW full columns FROM " . $table);
        $lists = [];
        foreach ($fields as $info)
        {
            if(!$field || $info['Field'] == $field) {
                $list = [
                    'title'             => $info['Comment'] ?: $info['Field'],
                    'field'             => $info['Field'],
                    'type'              => $info['Type'],
                    'key'               => $info['Key'],
                    'null'              => $info['Null'],
                    'default'           => $info['Default'],
                    'sort'              => 0,
                    'search'            => 0,
                    'table_sort'        => 0,
                    'auto_increment'    => strpos($info['Extra'], 'auto_increment') !== false ,
                    'search_type'       => 'text',
                    'search_extend'     => '',
                    'table_extend'      => '',
                    'form_extend'       => '',
                    'form_format'       => '',
                    'table_format'      => '',
                    'save_format'       => '',
                    'form_type'         => 'text',
                    'table_type'        => 'text',
                    'relation'          => false,
                    'option_type'       => 'option_default',
                    'option_config'     => '',
                    'option_lang'       => '',
                    'option_relation'   => '',
                    'option_remote_relation'   => '',
                ];

                if ($list['key'] === 'PRI') { // 主键默认值
                    $list['sort'] = 99;
                    if ($list['auto_increment']) {
                        $list['form_type'] = 'hidden';
                    }
                }
                $lists[] = $list;
            }

        }
        return $lists;
    }

    /**
     * sql注入过滤
     * @param string $str
     *
     * @return string
     */
    protected static function filter(string $str):string
    {
        if (!get_magic_quotes_gpc())
        {
            $str = addslashes($str);
        }
        $str = nl2br($str);
        return htmlspecialchars($str);
    }

    /**
     * 过滤非字段
     *
     * @param array $data
     *
     * @return array
     */
    protected function checkTableContent(array $data):array
    {
        foreach ($data as $k => $v) {
            if (!in_array($k, $this->tableContent)) {
                unset($data[$k]);
            }
            if ($k == 'fields') {
                foreach ($v as $kk=>$vv) {
                    $v[$kk] = $this->checkFieldContent($vv);
                }
                $data['fields'] = $v;
            }
        }
        return $data;
    }

    /**
     * 过滤非字段
     *
     * @param array $data
     *
     * @return array
     */
    protected function checkFieldContent(array $data):array
    {
        foreach ($data as $k => $v) {
            if (!in_array($k, $this->fieldContent)) {
                unset($data[$k]);
            }
        }
        return $data;
    }

    /**
     * 返回单表信息
     * @param string $table
     * @return array
     * Author: zsw zswemail@qq.com
     */
    abstract public function table($table):array;

    /**
     * 返回所有表信息
     * @param string $table
     * @return array
     * Author: zsw zswemail@qq.com
     */
    abstract public function tables($table = ''):array;

    /**
     * 返回单条字段信息
     * @param string $table
     * @param string $field
     * @return array
     * Author: zsw zswemail@qq.com
     */
    abstract public function field($table, $field):array;

    /**
     * 返回所有字段信息
     * @param string $table
     * @param string $field
     * @return array
     * Author: zsw zswemail@qq.com
     */
    abstract public function fields($table, $field = ''):array;

    /**
     * 保存数据
     * @param string $data
     * @return bool
     * Author: zsw zswemail@qq.com
     */
    abstract public function save($data):bool;


    /**
     * 清理配置
     * @param      $table
     * @param null $field
     *
     * @return mixed
     */
    abstract public function delete($table, $field = null);
}