<?php

// 长时间未登录的用户就锁定起来
$config = \Phpcmf\Service::M('app')->get_config('safe');
if ($config['use'] && isset($config['safe']['wdl']) && $config['safe']['wdl']) {
    $time = $config['safe']['wdl'] * 3600 * 24;
    $log = \Phpcmf\Service::M()->table('app_login')->where('logintime < '.(SYS_TIME - $time))->getAll();
    if ($log) {
        foreach ($log as $t) {
            Phpcmf\Service::M()->table('member_data')->update($t['uid'], [
                'is_lock' => 1,
            ]);
        }
    }
}