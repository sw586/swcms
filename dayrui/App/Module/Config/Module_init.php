<?php

!$dirname && $dirname = APP_DIR;

if ($this->is_module_init == $dirname.'-'.$siteid) {
    // 防止模块重复初始化
    return 1;
}

$this->is_module_init = $dirname.'-'.$siteid;

// 判断模块是否安装在站点中
$cache = \Phpcmf\Service::L('cache')->get('module-'.$siteid);
$this->module = [];
if ($dirname == 'share' || (isset($cache[$dirname]) && $cache[$dirname])) {
    $this->module = \Phpcmf\Service::L('cache')->get('module-'.$siteid.'-'.$dirname);
}

// 判断模块是否存在
if (!$this->module) {
    // 重新生成一次缓存
    \Phpcmf\Service::M('cache')->sync_cache('');
    $this->module = \Phpcmf\Service::L('cache')->get('module-'.$siteid.'-'.$dirname);
    if (!$this->module) {
        if (IS_ADMIN) {
            if ($dirname == 'share') {
                if ($rt) {
                    return 0;
                } else {
                    CI_DEBUG && log_message('debug', $dirname.' - '.dr_lang('系统未安装共享模块，无法使用栏目'));
                    if (SITE_ID > 1) {
                        $this->_admin_msg(0, dr_lang('系统未安装共享模块，无法使用栏目').'<br>'.dr_lang('点击下方链接进行装载共享模块'), dr_url('module/module/index'), 10);
                    } else {
                        $this->_admin_msg(0, dr_lang('系统未安装共享模块，无法使用栏目'), dr_url('module/module/index'), 10);
                    }
                }
            } else {
                if ($rt) {
                    return 0;
                } else {
                    CI_DEBUG && log_message('error', $dirname.' - '.dr_lang('模块【%s】不存在', $dirname));
                    $this->_admin_msg(0, dr_lang('模块缓存【%s】不存在', $dirname));
                }
            }
        } else {
            if ($rt) {
                return 0;
            } else {
                CI_DEBUG && log_message('error', $dirname.' - '.dr_lang('模块【%s】不存在', $dirname));
                $this->goto_404_page(dr_lang('模块缓存【%s】不存在', $dirname));
            }
        }
    }
}

// 无权限访问模块
if (!defined('SC_HTML_FILE') && !IS_ADMIN && !IS_MEMBER && IS_USE_MEMBER
    && \Phpcmf\Service::L('member_auth', 'member')->module_auth($dirname, 'show', $this->member)) {
    if ($rt) {
        CI_DEBUG && log_message('debug', $dirname.' - '.dr_lang('您的用户组无权限访问模块'));
        return 0;
    }
    $this->_msg(0, dr_lang('您的用户组无权限访问模块'), $this->uid || !defined('SC_HTML_FILE') ? '' : dr_member_url('login/index'));
}

// 初始化数据表
$this->content_model = \Phpcmf\Service::M('Content', $dirname);
$this->content_model->_init($dirname, $siteid, $this->module['share']);

// 共享模块时，单页界面时，排除
if ($dirname == 'share') {
    return 0;
}

$this->module['comment'] = dr_is_app('comment') && \Phpcmf\Service::L('cache')->get('app-comment-'.SITE_ID, 'module', $dirname, 'use') ? 1 : 0;

// 兼容老版本
define('MOD_DIR', $dirname);
define('IS_SHARE', $this->module['share']);
define('IS_COMMENT', $this->module['comment']);
define('MODULE_URL', $this->module['share'] ? '/' : $this->module['url']); // 共享模块没有模块url
define('MODULE_NAME', dr_lang($this->module['name']));

$this->content_model->is_hcategory = $this->is_hcategory = isset($this->module['config']['hcategory']) && $this->module['config']['hcategory'];

// 设置模板到模块下
!$this->module['url'] && \Phpcmf\Service::V()->module($dirname);

// 初始化加载
$this->init_file($dirname);