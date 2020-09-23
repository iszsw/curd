<?php

namespace iszsw\porter\model;

use surface\form\Form as SForm;
use surface\table\Table as STable;

class Table
{

    public static $labels = [
        'table'         => '表名',
        'title'         => '名称',
        'description'   => '描述',
        'page'          => '分页',
        'btn'           => '操作',
        'extend'        => '扩展',
        'table_extend'  => '表格扩展',
        'form_extend'   => '表单扩展',
        'search_type'   => '搜索类型',
        'search_extend' => '搜索扩展',
        'form_format'   => '表单格式化',
        'save_format'   => '保存格式化',
        'table_format'  => '表格格式化',
        'table_sort'    => '排序',
        'search'        => '搜索',
        'field'         => '字段',
        'sort'          => '权重',
        'title'         => '名称',
        'default'       => '默认',
        'type'          => '类型',
        'key'           => '索引',
        'null'          => '空',
        'comment'       => '备注',
        'engine'        => '引擎',
        'rows'          => '行数',
        'mark'          => '备注',
        'form_type'     => '表单类型',
        'table_type'    => '表格类型',

        'auto_timestamp'=> '自动写入时间',
        'option'        => '值选项',
        'middle_table'  => '中间表',
        'remote_table'  => '远程表',
        'relation'      => '关联',
        'option_default'=> '默认',
        'option_config' => '自定义',
        'option_relation' => '表关联',
        'option_lang'       => '语言包',
        'option_remote_relation' => '远程关联',
    ];

    const SHOW = 1;
    const HIDE = 0;

    const ENABLE    = 1;
    const DISABLE   = 0;

    public static $statusLabels = [
        self::ENABLE => '启用',
        self::DISABLE => '禁止',
    ];

    public static $showLabels = [
        self::SHOW => '显示',
        self::HIDE => '隐藏',
    ];


    public static $formatTypes = [
        'datetime' => '日期时间 Y-m-d H:i:s',
        'toDate' => '日期 Y-m-d',
        'toTime' => '时间 H:i:s',
        'timestamp' => '时间戳',
    ];

    public static $searchType = [
        '=' => '=',
        '!=' => '!=',
        '>' => '>',
        '<' => '<',
        '>=' => '>=',
        '<=' => '<=',
        'LIKE' => 'LIKE',
        'NOT LIKE' => 'NOT LIKE',
        'IN' => 'IN',
        'NOT IN' => 'NOT IN',
        'BETWEEN' => 'BETWEEN',
        'NOT BETWEEN' => 'NOT BETWEEN',
        'NULL' => 'NULL',
        'NOT NULL' => 'NOT NULL',
    ];

    public static function getServersLabels()
    {
        $formTypes  = array_keys(SForm::getServers());
        $tableTypes = array_keys(STable::getServers());
        sort($formTypes);
        sort($tableTypes);
        $formTypes  = array_combine($formTypes, $formTypes);
        $tableTypes  = array_combine($tableTypes, $tableTypes);
        array_unshift($formTypes, '不显示');
        array_unshift($tableTypes, '不显示');

        return [$formTypes, $tableTypes];
    }

}

