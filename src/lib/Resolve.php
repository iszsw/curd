<?php
/**
 * Author: zsw zswemail@qq.com
 *
 */

namespace iszsw\curd\lib;

use iszsw\curd\model\Table;
use surface\table\Type;
use think\exception\HttpException;

abstract class Resolve
{

    protected $error;

    /**
     * @var table文件配置
     */
    protected $table;

    public function __construct($table)
    {
        $this->table = Manage::instance()->table($table);
        if ( ! $this->table)
        {
            throw new HttpException(404, "表【{$table}】不存在");
        }
    }

    public function getError()
    {
        return $this->error;
    }

    /**
     * 默认最大限制为100条 如果需要大量数据选择请使用 take组件
     *
     * @param       $val
     * @param       $type
     * @param array $in
     *
     * @return array
     */
    protected function options($val, $type, array $in = []): array
    {
        $option = [];
        switch ($type)
        {
            case 'option_remote_relation':
                $option = Model::instance()->name($val[4])->where(count($in) > 0 ? [[$val[5], 'in', $in]] : [])->limit(100)->column($val[6], $val[5]);
                break;
            case 'option_relation':
                $option = Model::instance()->name($val[0])->where(count($in) > 0 ? [[$val[1], 'in', $in]] : [])->limit(100)->column($val[2], $val[1]);
                break;
            case 'option_config':
                $option = $val;
                break;
            case 'option_lang':
                $option = lang('?'.$val) ? lang($val) : [];
                break;
            default:
        }

        return $option + array_combine($in, $in);
    }

    /**
     * 初始化方法解析
     *
     * Form 页面中不支持直接修改$row
     *
     * @param      $methods
     * @param      $val
     * @param null $row        当前列 地址传递
     *
     * @return mixed
     * @throws \Exception
     */
    protected function invoke($methods, $val, &$row = null)
    {
        is_array($methods) || $methods = (array)$methods;
        foreach ($methods as $func) {
            $params = [];

            $func = trim($func);
            if (strpos($func, '::')) {
                $func = explode('::', $func, 2);
            } elseif (strpos($func, ':') === 0) {
                $params[] = ltrim($func, ':');
                $func = [Custom::class, 'toReplace'];
            } elseif (isset(Table::$formatTypes[$func])) {
                $func = [Custom::class, $func];
            }

            if (is_array($func) && count($func) === 2)
            {
                try {
                    $reflect = new \ReflectionClass($func[0]);
                } catch (\Exception $e) {
                    throw new \Exception('class not exists: ' . $func[0]);
                }
                if ($reflect->hasMethod($func[1])) {
                    $method = $reflect->getMethod($func[1]);
                    if ($method->isStatic()) {
                        $method = $func[1];
                        $val = $func[0]::$method($val, $row, ...$params);
                    }elseif ($method->isPublic()) {
                        $val = (new $func[0]())->$func[1]($val, $row, ...$params);
                    }
                }else{
                    throw new \Exception('method not exists: ' . $func[1]);
                }
            } elseif (is_string($func) && function_exists($func))
            {
                $val = $func($val, $row, ...$params);
            }
        }

        return $val;
    }

    public function __get($name)
    {
        return $this->table[$name];
    }

}
