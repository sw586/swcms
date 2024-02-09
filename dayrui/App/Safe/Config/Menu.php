<?php

/**
 * 菜单配置
 */


return [

    'admin' => [

        'app' => [

            'left' => [

                // 分组菜单
                'app-safe' => [
                    'name' => '系统安全',
                    'icon' => 'fa fa-shield',
                    'link' => [
                        'app-safe-1' =>[
                            'name' => '安全监测',
                            'icon' => 'fa fa-shield',
                            'uri' => 'safe/home/index',
                        ],
                        'app-safe-2' =>[
                            'name' => '木马扫描',
                            'icon' => 'fa fa-bug',
                            'uri' => 'safe/Mm/index',
                        ],
                        'app-safe-5' =>[
                            'name' => '文件检测',
                            'icon' => 'fa fa-code',
                            'uri' => 'safe/check_bom/index',
                        ],
                        'app-safe-3' =>[
                            'name' => '后台域名',
                            'icon' => 'fa fa-cog',
                            'uri' => 'safe/adomain/index',
                        ],
                        'app-safe-4' => [
                            'name' => '账号安全',
                            'icon' => 'fa fa-expeditedssl',
                            'uri' => 'safe/config/index',
                        ],
                        'app-safe-6' => [
                            'name' => '站点安全',
                            'icon' => 'fa fa-share-alt',
                            'uri' => 'safe/link/index',
                        ],

                    ]
                ],


            ],



        ],

    ],
];