<?php
/*
 * Author: zsw zswemail@qq.com
 */
namespace iszsw\curd\lib;

trait Condition
{

    private $match = [
        'EQ'        => '=',
        'NEQ'       => '!=',
        'EQUAL'     => '=',
        'NOTEQUAL'  => '!=',
        'GT'        => '>',
        'EGT'       => '>=',
        'LT'        => '<',
        'ELT'       => '<=',
    ];

    /**
     * table搜索规则
     *
     * @param $condition
     * @param $key
     * @param $val
     * @return array
     * Author: zsw zswemail@qq.com
     */
    protected function condition($condition, $key, $val) {
        $where = [];
        $condition = strtoupper($condition);
        $condition = $this->match[$condition] ?? $condition;
        switch ($condition) {
            case '=':
            case '!=':
            case 'EQ':
            case 'NEQ':
            case 'EQUAL':
            case 'NOTEQUAL':
                $where = [$key, $condition, (string)$val];
                break;
            case 'LIKE':
            case 'NOT LIKE':
            case 'LIKE %...%':
            case 'NOT LIKE %...%':
                $where = [$key, trim(str_replace('%...%', '', $condition)), "%{$val}%"];
                break;
            case '>':
            case '>=':
            case '<':
            case '<=':
            case 'GT':
            case 'EGT':
            case 'LT':
            case 'ELT':
                $where = [$key, $condition, intval($val)];
                break;
            case 'IN':
            case 'IN(...)':
            case 'NOT IN':
            case 'NOT IN(...)':
                is_array($val) || $val = explode(',', $val);
                $val = array_filter($val);
                if (count($val) < 1) {
                    break;
                }
                $where = [$key, str_replace('(...)', '', $condition), $val];
                break;
            case 'BETWEEN':
            case 'NOT BETWEEN':
            case 'RANGE':
            case 'NOT RANGE':
                $old = $val;
                if (!is_array($val)) {
                    $val = strpos($val, ' - ') !== false ? str_replace(' - ', ',', $val) : $val;
                    $arr = array_slice(explode(',', $val), 0, 2);
                }else{
                    $arr = $val;
                }
                if (count(array_filter($arr)) < 1) {
                    break;
                }
                //处理时间格式
                if (date('Y-m-d H:i:s', strtotime($arr[0])) == $arr[0] || date('Y-m-d', strtotime($arr[0])) == $arr[0]) {
                    $arr[0] = strtotime($arr[0]);
                }
                $arr[1] = $arr[1] ?? '';
                if (date('Y-m-d H:i:s', strtotime($arr[1])) == $arr[1] || date('Y-m-d', strtotime($arr[1])) == $arr[1]) {
                    $arr[1] = strtotime($arr[1]);
                }
                //当出现一边为空时改变操作符
                if ($arr[0] === '') {
                    $condition = $condition == 'RANGE' ? '<=' : '>';
                    $arr = $arr[1];
                } else if ($arr[1] === '') {
                    $condition = $condition == 'RANGE' ? '>=' : '<';
                    $arr = $arr[0];
                }
                $where = [$key, str_replace('RANGE', 'BETWEEN', $condition), $arr];
                $val = $old;//数据还原
                unset($old);
                break;
            case 'NULL':
            case 'IS NULL':
            case 'NOT NULL':
            case 'IS NOT NULL':
                $where = [$key, strtolower(str_replace('IS ', '', $condition))];
                break;
            default:
                break;
        }
        return $where;
    }
}
