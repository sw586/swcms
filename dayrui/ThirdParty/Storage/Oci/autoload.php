<?php

function ociClassLoader($class)
{
    // 将命名空间转换为目录路径
    $path = str_replace('\\', DIRECTORY_SEPARATOR, $class);

    // 构建文件的完整路径
    $file = __DIR__ . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $path . '.php';

    // 如果文件存在，则引入该文件
    if (file_exists($file)) {
        require_once $file;
    }
}

// 注册上面定义的自动加载函数
spl_autoload_register('ociClassLoader');
