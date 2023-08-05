<?php


namespace curd\generator;


use curd\Build;
use surface\Config;



/**
 * CURD 配置
 *
 * @property int $sort
 * @property string $comment
 * @property string $name
 * @property string $table_type
 * @property string $search_type
 * @property string $form_type
 *
 */
class Column extends Config
{

    protected array $config = [
        "sort"        => 99,
        "comment"     => '',
        "name"        => '',
        "table_type"  => '',
        "search_type" => '',
        "form_type" => '',
    ];


}

