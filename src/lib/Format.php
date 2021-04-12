<?php
/**
 * Author: zsw zswemail@qq.com
 *
 */

namespace iszsw\curd\lib;

use iszsw\curd\model\Table;
/**
 * 格式处理
 * Author: zsw zswemail@qq.com
 */
class Format
{

    public static function parse($methods, $val, array $args = [])
    {
        is_array($methods) || $methods = (array)$methods;
        foreach ($methods as $func) {
            $params = [$val];

            $func = trim($func);
            if (strpos($func, '::')) {
                $func = explode('::', $func, 2);
            } elseif (strpos($func, ':') === 0) {
                $params[] = ltrim($func, ':');
                $func = [Custom::class, 'replace'];
            } elseif (isset(Table::$formatTypes[$func])) {
                $func = [Custom::class, $func];
            }
            $params = array_merge($params, $args);
            if (is_array($func) && call_user_func_array('method_exists', $func)) {
                $val = call_user_func_array($func, $params);
            }elseif(is_string($func) && function_exists($func)){
                $val = static::invoke($func, $params);
            }
        }

        return $val;
    }

    public static function invoke($function, array $vars = [])
    {
        $reflect = new \ReflectionFunction($function);
        $paramsNum = $reflect->getNumberOfParameters();
        $i = $paramsNum;
        while ($i > 0) {
            $args[] = array_shift($vars);
            $i--;
        }
        return $function(...$args);
    }

}
