<?php
// dayrui/ThirdParty/Storage/Oci/autoload.php

spl_autoload_register(function ($class) {
    // 定义命名空间前缀
    $prefix = 'Oracle\\Oci\\ObjectStorage\\';
    // 定义类文件的基本目录
    $baseDir = __DIR__ . '/src/';

    // 检查是否以命名空间前缀开头
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    // 获取相对于命名空间的类名
    $relativeClass = substr($class, $len);
    // 构建类文件的完整路径
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    // 如果文件存在，则加载之
    if (file_exists($file)) {
        require $file;
    }
});
