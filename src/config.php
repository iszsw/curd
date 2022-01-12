<?php

return [
    // 路由前缀 通过访问 /curd.html 访问表格页面
    'route_prefix' => 'curd',
    // 默认 存储类型
    'save' => \iszsw\curd\lib\Manage::FILE_NAME,
    'engine' => [
        // 目前只有文件存储
        \iszsw\curd\lib\Manage::FILE_NAME => [
            // 数据保存地址 确保路径的可读写
            'save_path' => runtime_path('curd'),
        ]
    ],
    'middleware' => '', // array|string 设置路由的中间件

    /*
     * surface配置 配置优先级: 自定义配置 > 默认配置
     *
     * globals参数为VUE组件（props，domProps，style，class ....）
     *
     * 前端使用 ElementUI
     *
     * Form.globals.props 参数 https://element.eleme.cn/#/zh-CN/component/form#form-attributes
     * Table.globals.props 参数 https://element.eleme.cn/#/zh-CN/component/table#table-attributes
     */
    'surface' => [
        // 静态资源CDN地址 默认使用公共cdn 如果需要自定义cdn地址 请下载静态资源 https://gitee.com/iszsw/surface-src
        'cdn'   => '',
        'table' => [
            'style' => [ // 公共资源
            ],
            'script' => [ // 公共脚本
            ],
            'globals' => [
                'props' => [ // props参数
                    'emptyText' => "没有更多",
                ],
            ],
        ],
        'form'  => [
            'style' => [ // 公共资源
            ],
            'script' => [ // 公共脚本
            ],
            'globals' => [

            ],
            'upload' => [
                'props' => [
                    'action' => '/upload.php',
                ]
            ]
        ],
    ]
];
