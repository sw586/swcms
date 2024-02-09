<?php

$code = file_get_contents(CMSPATH.'Control/Api/Run.php');
if ($code && strpos($code, 'post_time')) {
    return; // 老程序不执行
}

// 批量执行站点动作
foreach ($this->site_info as $siteid => $site) {

    // 模块
    $module = \Phpcmf\Service::L('cache')->get('module-'.$siteid.'-content');
    if ($module) {
        foreach ($module as $dir => $mod) {
            // 删除模块首页
            if ($mod['is_index_html']) {
                if ($mod['domain']) {
                    // 绑定域名时
                    $file = 'index.html';
                } else {
                    $file = ltrim(\Phpcmf\Service::L('Router')->remove_domain($mod['url']), '/'); // 从地址中获取要生成的文件名;
                }
                if ($file) {
                    unlink(\Phpcmf\Service::L('html')->get_webpath($siteid, $dir, $file));
                    unlink(\Phpcmf\Service::L('html')->get_webpath($siteid, $dir, 'mobile/'.$file));
                }
            }
            // 定时发布动作
            $times = \Phpcmf\Service::M()->table($siteid.'_'.$dir.'_time')->where('posttime < '.SYS_TIME)->getAll(10);
            if ($times) {
                $this->_module_init($dir, $siteid, 1);
                \Phpcmf\Service::C()->module = $this->module;
                \Phpcmf\Service::C()->content_model->siteid = $siteid;
                foreach ($times as $t) {
                    $rt = $this->content_model->post_time($t);
                    if (!$rt['code']) {
                        CI_DEBUG && log_message('error', '定时发布（'.$t['id'].'）失败：'.$rt['msg']);
                    }
                }
                break;
            }
        }
    }
}