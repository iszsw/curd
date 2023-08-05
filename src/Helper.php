<?php

namespace curd;

class Helper
{

    /**
     * 解析字符串类返回命名空间或者类名
     *
     * @param string $class
     * @param int $result
     *
     * @return array|string
     */
    public static function parseClass(string $class, int $result = 3): array|string
    {
        $class = str_replace("/", "\\", $class);
        $namespaces = explode("\\", $class);
        $class = array_pop($namespaces);
        $namespace = implode("\\", $namespaces);
        if ($result === 1) {
            return $class;
        }
        if ($result === 2) {
            return $namespace;
        }
        return [$namespace, $class];
    }

    /**
     * 下划线转驼峰
     *
     * @param string $str
     * @param bool $capitalized 首字母大写
     *
     * @return string
     */
    public static function camel(string $str, bool $capitalized = true): string
    {
        $result = str_replace('_', '', ucwords($str, '_'));
        if ($capitalized) {
            $result = ucfirst($result);
        }
        return $result;
    }

}
