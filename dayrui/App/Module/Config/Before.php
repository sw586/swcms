<?php

if (\Phpcmf\Service::M()->is_table_exists('module')) {
    // 表示模块表已经操作，手动安装模块
    $rs = file_put_contents(dr_get_app_dir('module').'/install.lock', 'fix');
    if (!$rs) {
        return dr_return_data(0, '目录（'.dr_get_app_dir('module').'）无法写入');
    }
    return dr_return_data(0, '【内容系统】插件已被安装');
}

return dr_return_data(1, 'ok');