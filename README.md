<p align="center">

# 无任何框架依赖可视化CURD代码生成工具.

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

## 1、在线编辑生成页面

> 控制器中粘贴下面代码

```php
use curd\Config;

$config = new Config();
// 代码保存的绝对路径
$config->save_path = __DIR__ . "/curd"; 
// PDO连接，读取数据库的表、字段预载入（如果不需要读取数据表结构可以不配置）
// 如果在框架中可以通过助手获取 (ThinkPHP: Db::connect()->connect()、Laravel：DB::getPdo())
$config->db_pdo = new \PDO("mysql:host=localhost;charset=utf8", 'root', 'root');
// 指定表名
$config->db_database = 'surface';

// 显示页面
$data = (new \curd\View($config))->fetch();
echo is_array($data) ? json_encode($data) : $data;

```
![image962824033a72fdbc.png](https://img.picgo.net/2023/08/05/image962824033a72fdbc.png)
![image0cd4b57dd58335ab.png](https://img.picgo.net/2023/08/05/image0cd4b57dd58335ab.png)

## 2、引入生成的页面代码

> 在线编辑保存之后会在相应目录生成curd代码，路由控制器引入生成的类就完成了

```php
if ($this->request->isAjax()){
    // 异步请求返回表格数据
    return json_encode([
        "code" => 0,
        "data" => [
            'data' => [], // 记录列表
            'total' => 10, // 总记录数
        ],
        'msg' => "success"
    ],JSON_UNESCAPED_UNICODE);
}
return (new \app\curd\Admin())->view();
```

![image7e7493f861a0f836.png](https://img.picgo.net/2023/08/05/image7e7493f861a0f836.png)
