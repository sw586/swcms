<?php

// 自动加载识别文件

return [

    /**
     * 命名空间映射关系
     */
    'psr4' => [


    ],

    /**
     * 类名映射关系
     */
    'classmap' => [

        'Phpcmf\Member\Module'        => IS_USE_MODULE.'Extends/Member/Module.php',

    ],


];