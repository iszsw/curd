<?php

namespace iszsw\curd\model;

use surface\form\Form as SForm;
use surface\table\Table as STable;

class Table
{

    const KEY = 'key';
    const LABEL = 'label';
    const VALUE = 'value';

    public static $labels
        = [
            self::KEY       => '键',
            self::LABEL     => '标签',
            self::VALUE     => '值',
            'table'         => '表名',
            'title'         => '名称',
            'status'        => '启用',
            'description'   => '描述',
            'page'          => '分页',
            'button'        => '自定义按钮',
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
            'weight'        => '权重',
            'sort'          => '排序',
            'title'         => '名称',
            'default'       => '默认值',
            'type'          => '类型',
            'key'           => '索引',
            'null'          => '空',
            'comment'       => '备注',
            'engine'        => '引擎',
            'rows'          => '行数',
            'mark'          => '备注',
            'form_type'     => '表单类型',
            'table_type'    => '表格类型',
            'button_local'  => '按钮位置',
            'button_default'=> '默认按钮',
            'button_event'   => '按钮事件',
            'icon'          => '图标',
            'marker'        => '提示文本',
            'url'           => '地址',
            'tooltip'       => '提示',
            'data_extend'   => '提交参数',
            'btn_extend'    => '按钮参数',
            'confirm_msg'   => '提示文字',
            'pk'            => '主键',
            'surface_table' => '表格',
            'form'          => '表单',
            'save'          => '保存',
            'custom'        => '自定义',
            'matching'      => '匹配规划',

            'datetime_fields'        => '日期时间字段',
            'option_type'            => '值选项',
            'middle_table'           => '中间表',
            'remote_table'           => '远程表',
            'relation'               => '关联',
            'option_default'         => '默认',
            'option_config'          => '自定义',
            'option_relation'        => '表关联',
            'option_lang'            => '语言包',
            'option_remote_relation' => '远程关联',
        ];

    const ENABLE = true;
    const DISABLE = false;

    public static $statusLabels
        = [
            self::ENABLE  => '启用',
            self::DISABLE => '禁止',
        ];

    const BUTTON_CREATE = 'create';
    const BUTTON_UPDATE = 'update';
    const BUTTON_DELETE = 'delete';
    const BUTTON_REFRESH = 'refresh';

    public static $buttonDefaultLabels
        = [
            self::BUTTON_CREATE => '创建',
            self::BUTTON_UPDATE => '更新',
            self::BUTTON_DELETE => '删除',
            self::BUTTON_REFRESH => '刷新',
        ];

    public static $defaultDateTime = [
        'create_time'=>'create_time',
        'update_time'=>'update_time',
        'delete_time'=>'delete_time'
    ];

    public static $showLabels
        = [
            self::ENABLE  => '显示',
            self::DISABLE => '隐藏',
        ];

    public static $pageLabels
        = [
            self::ENABLE  => '开启',
            self::DISABLE => '关闭',
        ];

    const LOCAL_TOP = 'top';
    const LOCAL_RIGHT = 'right';

    public static $localLabels
        = [
            self::LOCAL_TOP   => '头按钮',
            self::LOCAL_RIGHT => '列按钮',
        ];

    const BTN_EVENT_PAGE = 'page';
    const BTN_EVENT_SEARCH = 'search';
    const BTN_EVENT_SUBMIT = 'submit';
    const BTN_EVENT_CONFIRM = 'confirm';
    const BTN_EVENT_REFRESH = 'refresh';

    public static $btnEventLabels
        = [
            self::BTN_EVENT_PAGE    => '页面 page',
            self::BTN_EVENT_SUBMIT  => '提交 submit',
            self::BTN_EVENT_CONFIRM => '确认 confirm',
            self::BTN_EVENT_REFRESH => '刷新 refresh',
        ];

    public static $formatTypes
        = [
            'toDatetime'  => '日期时间 Y-m-d H:i:s',
            'toDate'      => '日期 Y-m-d',
            'toTime'      => '时间 H:i:s',
            'toTimestamp' => '时间戳',
        ];

    public static $searchType
        = [
            '='           => '=',
            '!='          => '!=',
            '>'           => '>',
            '<'           => '<',
            '>='          => '>=',
            '<='          => '<=',
            'LIKE'        => 'LIKE',
            'NOT LIKE'    => 'NOT LIKE',
            'IN'          => 'IN',
            'NOT IN'      => 'NOT IN',
            'BETWEEN'     => 'BETWEEN',
            'NOT BETWEEN' => 'NOT BETWEEN',
            'NULL'        => 'NULL',
            'NOT NULL'    => 'NOT NULL',
        ];

    public static function getTableServersLabels()
    {
        return [
            "_"         => "不显示",
            "column"    => "文本",
            "writable"  => "可编辑文本",
            "select"    => "可编辑下拉",
            "switcher"  => "可编辑开关",
//            "selection" => "选择框", // 默认使用主键作为selection
            "expand"    => "展开行",
        ];
    }

    public static function getFormServersLabels()
    {
        return [
            "_"        => "不显示",
            "input"    => "文本框",
            "radio"    => "单选",
            "checkbox" => "多选",
            "switcher" => "开关",
            "select"   => "下拉",
            "date"     => "日期",
            "time"     => "时间",
            "take"     => "获取",
            "color"    => "颜色",
            "editor"   => "富文本",
            "hidden"   => "隐藏表单",
            "number"   => "数字框",
            "rate"     => "评分",
            "slider"   => "区间",
            "tree"     => "树",
            "cascader" => "联动",
            "upload"   => "上传",
            "array"    => "数组",
        ];
    }

}

