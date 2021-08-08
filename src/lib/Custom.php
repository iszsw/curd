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
    public static function toDatetime($data, &$row)
    {
        $row['title'] .= "aaa";
        return self::formatTime($data, 'Y-m-d H:i:s');
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
        return self::formatTime($data, 'Y-m-d');
    }

    /**
     * 转时间格式
     * @param $data
     * @return false|string
     * Author: zsw zswemail@qq.com
     */
    public static function toTime($data)
    {
        return self::formatTime($data, 'H:i:s');
    }


    /**
     * 转时间戳格式
     * @param $data
     * @return false|int
     * Author: zsw zswemail@qq.com
     */
    public static function toTimestamp($data)
    {
        return self::formatTime($data, true);
    }

    /**
     * 转时间
     *
     * @param             $timestamp 日期时间 | 时间戳
     * @param string|bool $format    日期格式化 | true 转时间戳
     *
     * @return string | int
     */
    private static function formatTime($timestamp, $format = 'Y-m-d H:i:s')
    {
        if (true === $format)
        {
            return self::isTimestamp($timestamp) ? $timestamp : (strtotime($timestamp)?:0);
        }

        return self::isTimestamp($timestamp) ? date($format, $timestamp) : $timestamp;
    }

    private static function isTimestamp($timestamp): bool
    {
        return ctype_digit($timestamp) && strtotime(date('Y-m-d H:i:s', $timestamp)) === (int)$timestamp;
    }

    /**
     * 字符串替换
     * @param $data
     * @param $row
     * @param string $str
     * @return false|int
     * Author: zsw zswemail@qq.com
     */
    public static function toReplace($data, $row, $str = '{data}')
    {
        $new = '';
        foreach ((array)$data as $v) {
            $new .= str_replace('{data}', $v, $str);
        }
        return $new;
    }


}
