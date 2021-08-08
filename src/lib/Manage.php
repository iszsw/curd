<?php
/**
 * Author: zsw zswemail@qq.com
 *
 */

namespace iszsw\curd\lib;

use iszsw\curd\Helper;
use iszsw\curd\lib\engine\File;
use iszsw\curd\model\Table as TableMode;

abstract class Manage
{
    /**
     * 表前缀
     *
     * @var string
     */
    protected static $prefix = '';

    const FILE_NAME = 'file';

    public static $engine
        = [
            self::FILE_NAME => File::class,
        ];

    /**
     * 数据库名字位置
     */
    const DATABASE_CONFIG_LOCATION = 'connections.mysql.database';

    /**
     * 数据表前缀位置
     */
    const PREFIX_CONFIG_LOCATION = 'connections.mysql.prefix';

    protected $tableContent
        = [
            'table',            // 表
            'pk',               // 索引
            'title',            // 标题
            'description',      // 描述
            'datetime_fields',   // 自动时间戳
            'button_default',   // 默认按钮
            'page',             // 分页
            'button',           // 按钮
            'extend',           // json 扩展
            'fields'            // 所有字段配置
        ];

    protected $fieldContent
        = [
            'weight',           // 权重
            'field',            // 字段
            'title',            // 名称
            'relation',         // bool 关联字段
            'default',          // 默认值
            'search',           // 搜索匹配规则
            'form_type',        // 表单样式
            'marker',           // 表单提示
            'table_type',       // 表格样式
            'search_type',      // 搜索表单类型
            'search_extend',    // 搜索扩展
            'table_extend',     // 扩展
            'form_extend',      // 扩展
            'save_format',      // 保存格式化
            'form_format',      // 表单格式化
            'table_format',     // 表格格式化
            'table_sort',       // 表格排序
            'option_type',      // 参数类型
            'option_config',    // 自定义参数
            'option_lang',      // 语言包参数
            'option_relation',  // 关联参数
            'option_remote_relation',  // 关联参数
        ];

    /**
     * 配置
     *
     * @var array
     */
    protected $config = [];

    private function __construct($config = [])
    {
        $this->config = $config;
    }

    private static $instance = null;

    /**
     * @param bool $refresh
     *
     * @return static
     * @throws \Exception
     */
    final public static function instance(bool $refresh = false): self
    {
        if (static::$instance && ! $refresh)
        {
            return static::$instance;
        }

        $config = config('curd');
        $default = require __DIR__.'/../config.php';
        $config = Helper::extends($default, $config);
        $engine = $config['save'];
        if ( ! class_exists(self::$engine[$engine]))
        {
            throw new \Exception('class ['.$engine.'] does not exient');
        }
        $instance = new self::$engine[$engine]($config['engine'][$engine]);
        if ( ! $instance instanceof static)
        {
            throw new \Exception('class ['.$engine.'] is not an instance of '.static::class);
        }
        $instance->init();

        return static::$instance = $instance;
    }

    public static function getPrefix()
    {
        return self::$prefix ?: self::$prefix = Model::instance()->getConfig(self::PREFIX_CONFIG_LOCATION);
    }

    public function init()
    {
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

        $tables = Model::instance()->query($sql, ['database' => Model::instance()->getConfig(self::DATABASE_CONFIG_LOCATION)]);

        $prefix = self::getPrefix();

        return array_map(
            function ($t) use ($prefix)
            {
                return ltrim($t['table'], $prefix);
            }, $tables
        );
    }

    /**
     * 所有数据库表详情
     *
     * @param string $table
     *
     * @return array
     */
    protected function tablesInfo(string $table = '')
    {
        $prefix = self::getPrefix();
        if ($table)
        {
            $table = static::sqlFilter($table);
            $table = " AND TABLE_NAME = '{$prefix}{$table}'";
        }

        $sql
            = "SELECT TABLE_NAME as `table`,TABLE_COMMENT as `comment`,TABLE_ROWS as `rows`,ENGINE as `engine` FROM information_schema.TABLES WHERE TABLE_SCHEMA = :table "
            .$table;
        $list = Model::instance()->query($sql, ['table' => Model::instance()->getConfig(self::DATABASE_CONFIG_LOCATION)]);

        foreach ($list as $k => $v)
        {
            $list[$k] = [
                'table'          => ltrim($v['table'], $prefix),
                'title'          => $v['comment'],
                'description'    => $v['comment'],
                'rows'           => $v['rows'],
                'engine'         => $v['engine'],
                'datetime_fields' => [],
                'button_default' => array_keys(TableMode::$buttonDefaultLabels),
                'page'           => true,
                'fields'         => [],
                'extend'         => '',
                'pk'             => 'id',
                'button'         => [],
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
    protected function fieldsInfo($table, $field = null)
    {
        $fields = Model::instance()->query("SHOW full columns FROM " . self::getPrefix() . $table);
        $lists = [];

        is_string($field) && $field = (array)$field;

        $weight = 10;
        foreach ($fields as $info)
        {
            if ( null === $field || in_array($info['Field'], $field) )
            {
                $list = [
                    'title'          => $info['Comment'] ?: $info['Field'],
                    'field'          => $info['Field'],
                    'type'           => $info['Type'],
                    'key'            => $info['Key'],
                    'null'           => $info['Null'],
                    'default'        => $info['Default'],
                    'auto_increment' => strpos($info['Extra'], 'auto_increment') !== false,
                    'weight'         => $weight,

                    "search_type"   => "_",
                    "search"        => "=",
                    "search_extend" => [],

                    "table_type"   => "column",
                    "table_format" => [],
                    "table_sort"   => false,
                    "table_extend" => [],

                    "form_type"   => "input",
                    "marker"      => '',
                    "form_format" => [],
                    "form_extend" => [],

                    "save_format" => [],

                    'relation'               => false,
                    'option_type'            => 'option_default',
                    'option_config'          => [],
                    'option_lang'            => '',
                    'option_relation'        => '',
                    'option_remote_relation' => '',
                ];

                if ($list['key'] === 'PRI')
                { // 主键默认值
                    $list['weight'] = 1;
                    if ($list['auto_increment'])
                    {
                        $list['form_type'] = 'hidden';
                    }
                }
                $lists[] = $list;
            }

            $weight += 5;
        }

        return $lists;
    }

    /**
     * sql注入过滤
     *
     * @param string $str
     *
     * @return string
     */
    protected static function sqlFilter(string $str): string
    {
        $str = addslashes($str);

        $str = str_replace("%", "\%", $str);

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
    protected function checkTableContent(array $data): array
    {
        foreach ($data as $k => $v)
        {
            if ( ! in_array($k, $this->tableContent))
            {
                unset($data[$k]);
            }
            if ($k == 'fields')
            {
                foreach ($v as $kk => $vv)
                {
                    $v[$kk] = $this->checkFieldContent($vv);
                }
                $data['fields'] = $v;
            }
        }

        return $data;
    }

    /**
     * 过滤非注册字段
     *
     * @param array $data
     *
     * @return array
     */
    protected function checkFieldContent(array $data): array
    {
        foreach ($data as $k => $v)
        {
            if ( ! in_array($k, $this->fieldContent))
            {
                unset($data[$k]);
            }
        }

        return $data;
    }

    /**
     * 返回单表信息
     *
     * @param string $table
     *
     * @return array
     * Author: zsw zswemail@qq.com
     */
    public function table($table): array
    {
        return $this->tables($table)[0] ?? [];
    }

    /**
     * 返回所有表信息
     *
     * @param string $table
     *
     * @return array
     * Author: zsw zswemail@qq.com
     */
    abstract public function tables($table = ''): array;

    /**
     * 返回单条字段信息
     *
     * @param string $table
     * @param string $field
     *
     * @return array
     * Author: zsw zswemail@qq.com
     */
    public function field($table, $field): array
    {
        return $this->fields($table, $field)[0] ?? [];
    }

    /**
     * 返回所有字段信息
     *
     * @param string $table
     * @param string $field
     *
     * @return array
     * Author: zsw zswemail@qq.com
     */
    abstract public function fields($table, $field = ''): array;

    /**
     * 保存数据
     *
     * @param string $data
     *
     * @return bool
     * Author: zsw zswemail@qq.com
     */
    abstract public function save($data): bool;


    /**
     * 清理配置
     *
     * @param      $table  删除表
     * @param null $fields 删除字段
     *
     * @return mixed
     */
    abstract public function delete($table, $fields = null);
}
