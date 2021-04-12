<?php

return [
    'route_prefix' => 'curd',     // 路由前缀 通过访问 /curd 访问表格页面
    'save' => \iszsw\curd\lib\Manage::FILE_NAME, // 默认 存储类型
    'engine' => [
        'file' => [
            'save_path' => runtime_path('curd'), // 数据保存地址 确保路径的可读写
        ]
    ],

    // surface配置 配置优先级  自定义配置 > 默认配置
    'surface' => [
        'table' => [
            'globals' => [ // table公共配置
                           'props' => [
                               'emptyText' => "没有更多",
                           ],
            ],
        ],
        'form'  => [
            'globals' => [ // form公共配置

            ],
            'upload' => [
                'props' => [
                    'action' => '/upload.php',
                ]
            ]
        ],
    ]
];
