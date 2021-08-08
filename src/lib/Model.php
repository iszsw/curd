<?php
/**
 * Author: zsw zswemail@qq.com
 */

namespace iszsw\curd\lib;


class Model
{


    private static $instance;

    /**
     * Author: zsw zswemail@qq.com
     */
    public static function instance()
    {
        return self::$instance ?? self::$instance = app()->db;
    }

}
