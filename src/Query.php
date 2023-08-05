<?php

namespace curd;

use PDO;
use curd\exceptions\CurdException;
use surface\components;

class Query
{

    private PDO $pdo;

    private string $database;

    const HANDLER_LABELS = [
        Build::OPERATE_REFRESH => "刷新",
        Build::OPERATE_CREATE => "新增",
        Build::OPERATE_UPDATE => "修改",
        Build::OPERATE_DELETE => "删除",
    ];

    const LIST_COMPONENTS = [
        generator\Text::class => "文本",
        generator\Image::class => "图片",
    ];

    const VMODEL_COMPONENTS = [
        components\Arrays::class => "json数组",
        components\Cascader::class => "级联选择器",
        components\Checkbox::class => "多选框",
        components\Color::class => "颜色选择器",
        components\Date::class => "日期",
        components\Editable::class => "可编辑文本",
        components\Editor::class => "富文本",
        components\Hidden::class => "隐藏文本",
        components\Input::class => "输入框",
        components\Number::class => "数字输入框",
        components\Objects::class => "json对象",
        components\Radio::class => "单选框",
        components\Rate::class => "评分",
        components\Select::class => "下拉框",
        components\Sku::class => "sku",
        components\Slider::class => "滑块",
        components\Switcher::class => "开关",
        components\Time::class => "时间",
        components\TimeSelect::class => "时间下拉选择",
        components\Transfer::class => "穿梭框",
        components\Tree::class => "树形",
        components\Upload::class => "上传",
    ];

    public function __construct(Config $config)
    {
        try {
            $this->pdo = $config->get('db_pdo');
            $this->database = $config->get('db_database');
        } catch (\Throwable $e) {
            throw new CurdException("config.db_pdo 必须传入 PDO 实例", $e->getCode(), $e);
        }
    }

    /**
     * 所有表
     *
     * @return array
     */
    public function tables(): array
    {
        $sql = "SELECT TABLE_NAME as `table`, TABLE_COMMENT as `comment`, TABLE_ROWS as `rows`,ENGINE as `engine` FROM information_schema.TABLES WHERE TABLE_SCHEMA = :database ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':database', $this->database);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * 数据表详情
     *
     * @return array
     */
    public function tablesInfo(): array
    {
        $tables = [];
        foreach ($this->tables() as $table) {
            $tables[$table['table']] = array_merge(
                $table,
                [
                    'pk'      => 'id',
                    'fields'  => [],
                ]
            );
        }
        $tablesStr = array_reduce(array_keys($tables), fn($str, $table) => $str . ($str ? "," : '') . "'{$table}'", '');
        $sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :database AND TABLE_NAME IN ({$tablesStr})";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':database', $this->database);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($result as $item) {
            $isPK = strtolower($item['COLUMN_KEY']) === 'pri';
            $tables[$item['TABLE_NAME']]['fields'][] = [
                'sort'    => count($tables[$item['TABLE_NAME']]['fields']) + 1,
                'name'    => $item['COLUMN_NAME'],
                'type'    => $item['COLUMN_TYPE'],
                'notnull' => strtolower($item['IS_NULLABLE']) === 'no',
                'default' => $item['COLUMN_DEFAULT'],
                'primary' => $isPK,
                'autoinc' => strtolower($item['EXTRA']) === 'auto_increment',
                'comment' => $item['COLUMN_COMMENT'],
            ];
            if ($isPK) {
                $tables[$item['TABLE_NAME']]['pk'] = $item['COLUMN_NAME'];
            }
        }
        return array_values($tables);
    }


    /**
     * 获取字段类型.
     *
     * @param string $type 字段类型
     *
     * @return string
     */
    protected function getFieldType(string $type): string
    {
        if (0 === stripos($type, 'set') || 0 === stripos($type, 'enum')) {
            $result = 'string';
        } elseif (preg_match('/(double|float|decimal|real|numeric)/is', $type)) {
            $result = 'float';
        } elseif (preg_match('/(int|serial|bit)/is', $type)) {
            $result = 'int';
        } elseif (preg_match('/bool/is', $type)) {
            $result = 'bool';
        } elseif (0 === stripos($type, 'timestamp')) {
            $result = 'timestamp';
        } elseif (0 === stripos($type, 'datetime')) {
            $result = 'datetime';
        } elseif (0 === stripos($type, 'date')) {
            $result = 'date';
        } else {
            $result = 'string';
        }

        return $result;
    }

}

