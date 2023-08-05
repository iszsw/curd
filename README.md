<p align="center">

# 基于 [surface](https://gitee.com/iszsw/surface) 的代码生成器.

</p>

## 源码

gitee地址：[https://gitee.com/iszsw/curd](https://gitee.com/iszsw/curd)

github地址：[https://github.com/iszsw/curd](https://github.com/iszsw/curd)

## 文档

[https://doc.zsw.ink](https://doc.zsw.ink)


## 安装

```bash
# 运行环境要求 PHP8+
composer require iszsw/curd
```

## 在线编辑页面

> 控制器中粘贴下面代码

```php
use curd\Config;

$config = new Config();
// 保存目录，设置之后页面自动填充
$config->save_path = app()->getRootPath() . "curd"; 
// PDO连接，读取数据库的表、字段预载入
$config->db_pdo = Db::connect()->connect();
// 指定表名
$config->db_database = 'surface';
// 显示页面
return (new View($config))->fetch();

```

## 生成页面

> 在线编辑保存之后在相应目录生成curd代码，需要在知道路由控制器引入

```php
if ($this->request->isAjax()){
    // 异步请求返回表格数据
    return json_encode([
        "code" => 0,
        "data" => [
            'data' => [],
            'total' => [],
        ],
        'msg' => "success"
    ],JSON_UNESCAPED_UNICODE);
}
return (new RecordCurd())->view();
```

