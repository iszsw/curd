<?php
/**
 * Author: zsw zswemail@qq.com
 *
 */

namespace iszsw\curd\lib;

/**
 * 格式处理
 * Author: zsw zswemail@qq.com
 */
class Custom
{
    /**
     * 转日期时间格式
     * @param $data
     * @return false|string
     * Author: zsw zswemail@qq.com
     */
    public static function datetime($data)
    {
        if (is_numeric($data)) {
            $data = date('Y-m-d H:i:s', $data);
        }
        return $data;
    }


    /**
     * 转日期格式
     * @param $data
     *
     * @return false|string
     * Author: zsw zswemail@qq.com
     */
    public static function toDate($data)
    {
        if (is_numeric($data)) {
            $data = date('Y-m-d', $data);
        }
        return $data;
    }

    /**
     * 转时间格式
     * @param $data
     * @return false|string
     * Author: zsw zswemail@qq.com
     */
    public static function toTime($data)
    {
        if (is_numeric($data)) {
            $data = date('H:i:s', $data);
        }
        return $data;
    }

    /**
     * 转时间戳格式
     * @param $data
     * @return false|int
     * Author: zsw zswemail@qq.com
     */
    public static function timestamp($data)
    {
        if (is_string($data)) {
            $data = strtotime($data);
        }
        return $data;
    }

    /**
     * 字符串替换
     * @param $data
     * @param string $str
     * @return false|int
     * Author: zsw zswemail@qq.com
     */
    public static function replace($data, $str = '{data}')
    {
        $new = '';
        foreach ((array)$data as $v) {
            $new .= str_replace('{data}', $v, $str);
        }
        return $new;
    }


}
