<?php

namespace curd;

use surface\Config as BaseConfig;

/**
 * CURD 配置
 *
 * @property string $save_path
 * @property \PDO $db_pdo
 * @property string $db_database
 *
 */
class Config extends BaseConfig
{

    protected array $config = [
        'save_path' => '', //项目目录
        'db_pdo' => null,   // PDO 连接器
        'db_database' => '', // 数据表前缀
    ];

}

