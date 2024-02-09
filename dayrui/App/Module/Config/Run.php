<?php

$client = \Phpcmf\Service::R(WRITEPATH.'config/domain_client.php'); // 电脑域名对应的手机域名

// 开启自动跳转手机端(api、admin、member不跳转)
if (!IS_API // api不跳转
    && !IS_ADMIN // 后台不跳转
    && !IS_MEMBER // 会员中心不跳
    && !IS_API_HTTP // API请求不跳
    && !IS_CLIENT // 终端不跳
    //&& !defined('IS_NOT_301') // 定义禁止301不跳
    //&& !defined('IS_NOT_301') // 定义禁止301不跳
    && $client // 没有客户端不跳
    && $this->site_info[SITE_ID]['SITE_MOBILE'] // 没有绑定移动端域名不跳
    //&& !in_array(DOMAIN_NAME, $client) // 当前域名不存在于客户端中时
    && $this->site_info[SITE_ID]['SITE_AUTO'] // 开启自动识别跳转
) {
    $domain = trim(DOMAIN_NAME.WEB_DIR, '/');
    if (\Phpcmf\Service::IS_MOBILE_USER()) {
        // 这是移动端
        if (isset($client[$domain])) {
            // 表示这个域名属于电脑端,需要跳转到移动端
            \Phpcmf\Service::L('Router')->auto_redirect(str_replace(dr_http_prefix($domain), dr_http_prefix($client[$domain]), dr_now_url()));
        }
    } else {
        // 这是电脑端
        if (dr_in_array($domain, $client)) {
            // 表示这个域名属于移动端,需要跳转到pc
            $arr = array_flip($client);
            \Phpcmf\Service::L('Router')->auto_redirect(str_replace(dr_http_prefix($domain), dr_http_prefix($arr[$domain]), dr_now_url()));
        }
    }
}

// 判断网站是否关闭
if (!IS_DEV && !IS_ADMIN && !IS_API
    && $this->site_info[SITE_ID]['SITE_CLOSE']
    && (!$this->member || !$this->member['is_admin'])) {
    // 挂钩点 网站关闭时
    \Phpcmf\Hooks::trigger('cms_close');
    $this->_msg(0, $this->get_cache('site', SITE_ID, 'config', 'SITE_CLOSE_MSG'));
}

// 站群系统接入
if (is_file(ROOTPATH.'api/fclient/sync.php')) {
    $sync = \Phpcmf\Service::R(ROOTPATH.'api/fclient/sync.php') ;
    if ($sync['status'] == 4) {
        if ($sync['close_url']) {
            dr_redirect($sync['close_url']);
        } else {
            $this->_msg(0, '网站被关闭');
        }
    } elseif ($sync['status'] == 3 || ($sync['endtime'] && SYS_TIME > $sync['endtime'])) {
        if ($sync['pay_url']) {
            dr_redirect($sync['pay_url']);
        } else {
            $this->_msg(0, '网站已过期');
        }
    }
}