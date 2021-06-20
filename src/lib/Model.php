<?php
/**
 * Author: zsw zswemail@qq.com
 */

namespace iszsw\curd\lib;

class Model
{

    private $error;

    /**
     * @var \think\Model
     */
    private $model;

    private $config = [];

    private static $instances = [];

    private function __construct(\think\Model $model)
    {
        $this->model  = $model;
        $this->config = Manage::instance()->table($this->model->getTable());
    }

    /**
     * 删除事件
     * @param \think\Model $data
     *
     * @throws \Exception
     * @throws \think\db\exception\DbException
     * Author: zsw zswemail@qq.com
     */
    public static function onBeforeDelete(\think\Model $data)
    {
        $config = Manage::instance()->table($data->getTable());
        $dataPk = $data->getPk();
        foreach ($config['fields'] as $k => $v) {
            // 关联字段 主动删除
            if ($v['relation']) {
                $remote_relation_config = $v['option_remote_relation'];
                $middleModel = static::instance($remote_relation_config[0]);
                $columns = $middleModel->where($remote_relation_config[2], $data->$dataPk)->column($middleModel->getPk());
                if (count($columns) > 0) {
                    $middleModel->where($middleModel->getPk(), 'in', array_keys($columns))->delete();
                }
            }
        }
    }

    /**
     * @param $table
     * @return self
     * Author: zsw zswemail@qq.com
     */
    public static function instance($table) : self
    {
        $model = self::$instances[$table] ?? null;
        if (!$model instanceof self) {
            self::$instances[$table] = new self(new class($data = [], $table) extends \think\Model
            {

                /**
                 *  constructor.
                 * * @param array $data
                 *
                 * @param $table
                 *
                 */
                public function __construct(array $data = [], $table)
                {
                    $this->table = $table;
                    parent::__construct($data);
                }

                public static function onBeforeDelete($data)
                {
                    return Model::onBeforeDelete($data);
                }

                // 重写模型的部分方法 开始
                public function newInstance(array $data = [], $where = null): \think\Model
                {
                    if (empty($data)) {
                        return new static();
                    }

                    $model = (new static($data, $this->table))->exists(true);
                    $model->setUpdateWhere($where);

                    $model->trigger('AfterRead');

                    return $model;
                }
                // 重写模型的部分方法 结束

            });
        }
        return self::$instances[$table];
    }

    /**
     * 只获取表字段
     * @return array
     * Author: zsw zswemail@qq.com
     */
    private function getFields()
    {
        return array_keys($this->model->getFieldsBindType());
    }

    public function save($post)
    {
        try{
            $this->model->startTrans();
            $pkName = $this->model->getPk();
            $pk = $post[$pkName] ?? '';
            $model = $this->model;
            if (!$pk) {
                unset($post[$pkName]);
            }else{
                $model = $model->find($pk);
            }

            $relation = [];
            $fields = $this->getFields();
            foreach ($post as $k => $v) {
                $field = $this->config['fields'][$k] ?? null;
                if ($field) {
                    if ($field['relation']) {
                        $relation[$k] = $v;
                    } elseif (in_array($k, $fields)) {
                        $post[$k] = $this->saveFormat($field['save_format'], $post[$k], $post);
                        if ($post[$k] === null) {
                            unset($post[$k]);
                        } else if (is_array($post[$k])) {
                            $post[$k] = json_encode($post[$k], JSON_UNESCAPED_UNICODE);
                        }
                        continue;
                    }
                }
                unset($post[$k]);
            }

            // 自动时间注册
            isset($this->config['auto_timestamp']) && $model->isAutoWriteTimestamp(!!$this->config['auto_timestamp']);
            $model->save($post);

            // 远程更新
            if (count($relation) > 0) {
                if (!$pk) {$pk = $this->model->getLastInsID();}

                foreach ($relation as $f => $val) {
                    $remote_relation_config = $this->config['fields'][$f]['option_remote_relation'];
                    $middleModel = static::instance($remote_relation_config[0]);
                    $remoteModel = static::instance($remote_relation_config[4]);

                    $columns = $middleModel->where($remote_relation_config[2], $pk)->column($remote_relation_config[3], $middleModel->getPk());
                    $insert = [];
                    foreach ($val as $v) {
                        if (false !== $index = array_search($v, $columns)) {
                            unset($columns[$index]);
                        }else{
                            // 默认 数字为PK
                            if (!$remote_id = $remoteModel->where([is_numeric($v) ? $remote_relation_config[5] : $remote_relation_config[6] => $v])->value('id')){
                                $remote_id = $remoteModel->insertGetId([$remote_relation_config[6] => $v]);
                            }
                            $insert[] = [
                                $remote_relation_config[2] => $pk,
                                $remote_relation_config[3] => $remote_id,
                            ];
                        }
                    }

                    if (count($insert) > 0) {
                        $middleModel->insertAll($insert);
                    }
                    if (count($columns) > 0) {
                        $middleModel->where($middleModel->getPk(), 'in', array_keys($columns))->delete();
                    }
                }
            }

            $this->model->commit();
        }catch (\Exception $e) {
            $this->model->rollback();
            return $e->getMessage();
        }
        return true;
    }

    private function saveFormat($methods, $val, ...$args)
    {
        return Format::parse($methods, $val, $args);
    }

    /**
     *
     * 删除记录
     * @access public
     * @param mixed $data  主键列表 支持闭包查询条件
     * @param bool  $force 是否强制删除
     *
     * @return bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     * Author: zsw zswemail@qq.com
     */
    public function destroy($data = [], bool $force = false)
    {
        if (empty($data) && 0 !== $data) {
            return false;
        }
        $query = $this->model->db();
        if (is_array($data) && key($data) !== 0) {
            $query->where($data);
            $data = null;
        } elseif ($data instanceof \Closure) {
            $data($query);
            $data = null;
        }
        $resultSet = $query->select($data);
        $delNum = 0;
        foreach ($resultSet as $result) {
            $result->force($force)->delete() && $delNum++;
        }
        return count($resultSet) === $delNum;
    }

    public function getError()
    {
        return $this->error;
    }

    public function __call($name, $arguments)
    {
        $forbid = ['create', 'update', 'destroy']; // 禁止访问Think/Model中的静态方法 使用save、delete代替 防止模型类中使用了创建新实例
        return in_array($name, $forbid) ? null : call_user_func_array([$this->model, $name], $arguments);
    }

}
