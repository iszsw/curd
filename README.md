# Tp6 数据库管理工具，数据库内容的搬运工

### 支持MySQL数据库，可视化配置直接生成前后台CRUD页面减少重复劳动，可以直接作为后台页面使用。前端页面技术基于 [iszsw/surface](https://zsw.ink) 

- 支持多表关联、一对一、一对多、远程一对多
- 支持页面自定义按钮，样式
- 支持每个表保存修改删除事件绑定
- 支持每一个字段变更前置后置事件绑定
- 还有更多功能后续会在我的主页发布文档...

### 作者
> zsw zswemail@qqcom

> 主页  [https://zsw.ink](https://zsw.ink) 查看介绍和演示

> github  [https://github.com/iszsw/porter](https://github.com/iszsw/porter)

> gitee  [https://gitee.com/iszsw/porter](https://gitee.com/iszsw/porter)

## 使用

> 1、安装 

```composer require iszsw/porter:dev-master```

> 2、访问 http://site.com/table 可以查看相应功能

> 3、涉及到一些surface的样式功能，再config目录下添加surface.php文件，粘贴下面内容
```php
<?php
/**
 * 默认值配置文件
 * 类型名称必须小写 upload|colorpicker
 * 继承关系的插件将覆盖上级
 * 配置值为空将读取系统默认配置
 */

$upload_url = '图片上传地址';
$manage_url = '图片管理地址';

return [
    'upload'  => [
        'manageShow' => true,    // 图片管理
        'manageUrl'  => $manage_url,    // 文件管理地址
        'action'     => $upload_url,    // 文件上传地址
        'uploadType' => 'image', // 文件类型 支持image|file
        'multiple'   => false,
        'limit'      => 1,
    ],
    'uploads' => [ // uploads继承自upload 将覆盖upload配置
        'multiple' => true,
        'limit'    => 9,
    ],
    'range'   => [
        'range' => true,
    ],
    'selects' => [
        'multiple'   => true,
        'filterable' => true,
    ],
    'frame'   => [
        'icon'   => 'el-icon-plus',
        'height' => '550px',
        'width'  => '976px', // 90%
    ],
    'editor'  => [
        'theme'           => 'black', // 主题 primary|black|grey|blue
        'items'           => null,    // 菜单内容
        'editorUploadUrl' => $upload_url,
        'editorManageUrl' => $manage_url,
        'editorMediaUrl'  => $upload_url,
        'editorFlashUrl'  => $upload_url,
        'editorFileUrl'   => $upload_url,
    ],
];
```

![https://s.zsw.ink/porter/table.png](https://s.zsw.ink/porter/table.png)

![https://s.zsw.ink/porter/fields.png](https://s.zsw.ink/porter/fields.png)

![https://s.zsw.ink/porter/page.png](https://s.zsw.ink/porter/page.png)

图片被吃了 直接打开链接吧
