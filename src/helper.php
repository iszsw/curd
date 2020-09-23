<?php

use surface\table\Table;

if (!function_exists('create_table_btn')) {
    /**
     * 默认生成table的操作图标
     * 1 create
     * 2 edit
     * 3 create + edit
     * 4 delete
     * 5 create + delete
     * 6 edit + delete
     * 7 create + edit + delete
     *
     * @param int $handle 表单会携带当前列的数据一起提交
     * @param string|array $params ['id'(读取列字段参数), 'id'=>1(设置默认携带参数)]
     * @return array
     */
    function create_table_btn($handle = 7, $params = 'id', $prefix = '')
    {
        $top = [];
        $operations = [];
        $ext = [];
        if (is_array($params)) {
            foreach ($params as $k => $p) {
                if (!is_numeric($k)) {
                    $ext[$k] = $p;
                    unset($params[$k]);
                }
            }
        } elseif (is_string($params)) {
            $params = format_explode($params, ',');
        }

        if (1 === ($handle & 1)) {
            $createUrl = builder_table_url($prefix . 'create', $ext);
            $top[] = Table::button(
                'page', '', [
                'title' => "添加",
                'refresh' => true,
                'url' => $createUrl,
            ], 'fa fa-plus');
        }

        if (2 === ($handle & 2)) {
            $editUrl = builder_table_url($prefix . 'edit', $ext);
            $operations[] = Table::button('page', '', [
                'title' => '编辑',
                'url' => $editUrl,
                'method' => 'get',
                'refresh' => true,
                'params' => $params,
            ], 'fa fa-edit');
        }
        if (4 === ($handle & 4)) {
            $deleteUrl = builder_table_url($prefix . 'delete', $ext);
            $top[] = Table::button('submit', '', [
                'text' => '确认删除选中项？',
                'url' => $deleteUrl,
                'method' => 'POST',
                'refresh' => true,
            ], 'fa fa-remove');
            $operations[] = Table::button(
                'confirm', '', [
                'text' => '确认删除？',
                'url' => $deleteUrl,
                'method' => 'POST',
                'refresh' => true,
                'params' => $params,
            ], 'fa fa-remove');
        }

        return [
            'topBtn' => $top,
            'operations' => $operations,
        ];
    }
}

if (!function_exists('format_explode')) {
    /**
     * 带过滤的 explode
     *
     * explode 方法格式化
     * @param $str
     * @param string $delimiter
     * @return array
     * Author: zsw zswemail@qq.com
     */
    function format_explode($str, $delimiter = ',')
    {
        return array_map('trim', array_filter(explode($delimiter, $str)));
    }
}


if (!function_exists('builder_table_url')) {
    /**
     * 生成 Table Url 地址
     *
     * @param string $url
     * @param array $param
     * @param bool|string $domain
     * @return string
     * Author: zsw zswemail@qq.com
     */
    function builder_table_url(string $url, array $param = [], $domain = false)
    {
        $url = '/' . config('porter.route_prefix') . '/' . trim($url, "\\/");
        $domain && $url = request()->domain() . $url;
        if ($param) {
            $url .= (strpos( $url, '?' ) === false ? '?' : '&' ) .http_build_query($param);
        }
        return $url;
    }
}
