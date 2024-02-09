<?php

/**
 * 自定义钩子
 *
 */

// 登录之前更新ip
\Phpcmf\Hooks::on('member_login_before', function($member) {
    if (!$member['username']) {
        return;
    }
    $user = \Phpcmf\Service::M()->table('member')->where('username', $member['username'])->getRow();
    if (!$user) {
        return;
    }
    $config = \Phpcmf\Service::M('app')->get_config('safe');
    $use = 'member';
    if (isset($config['login']['use']) && dr_in_array($use, $config['login']['use'])) {
        $attr = '';
        if ((isset($config['login']['city']) && $config['login']['city'])) {
            $attr.= \Phpcmf\Service::L('input')->ip_address();
        }
        if ((isset($config['login']['llq']) && $config['login']['llq'])) {
            $attr.= \Phpcmf\Service::L('input')->get_user_agent();
        }
        if ($attr) {
            $log = \Phpcmf\Service::M('login', 'safe')->get_log($user['id']);
            \Phpcmf\Service::M()->table('member')->update($log['uid'], [
                'login_attr' => md5($attr),
            ]);
        }
    }
});

\Phpcmf\Hooks::on('admin_login_before', function($member) {
    if (!$member or !$member['username']) {
        return;
    }
    $user = \Phpcmf\Service::M()->table('member')->where('username', $member['username'])->getRow();
    if (!$user) {
        return;
    }
    $config = \Phpcmf\Service::M('app')->get_config('safe');
    $use = 'admin';
    if (isset($config['login']['use']) && dr_in_array($use, $config['login']['use'])) {
        $attr = '';
        if ((isset($config['login']['city']) && $config['login']['city'])) {
            $attr.= \Phpcmf\Service::L('input')->ip_address();
        }
        if ((isset($config['login']['llq']) && $config['login']['llq'])) {
            $attr.= \Phpcmf\Service::L('input')->get_user_agent();
        }
        if ($attr) {
            $log = \Phpcmf\Service::M('login', 'safe')->get_log($user['id']);
            \Phpcmf\Service::M()->table('member')->update($log['uid'], [
                'login_attr' => md5($attr),
            ]);
        }
    }
});

// 登录后记录时间
\Phpcmf\Hooks::on('member_login_after', function($member) {
    $config = \Phpcmf\Service::M('app')->get_config('safe');
    $use = IS_ADMIN ? 'admin' : 'member';
    if (isset($config['pwd']['use']) && dr_in_array($use, $config['pwd']['use'])) {
        if (isset($config['safe']['wdl']) && $config['safe']['wdl']) {
            $log = \Phpcmf\Service::M('login', 'safe')->get_log($member['id']);
            \Phpcmf\Service::M()->table('app_login')->update($log['id'], [
                'logintime' => SYS_TIME,
            ]);
        }
    }
    if (isset($config['login']['use']) && dr_in_array($use, $config['login']['use'])) {
        // 操作标记
        if (isset($config['login']['is_option']) && $config['login']['is_option'] && $config['login']['exit_time']) {
            \Phpcmf\Service::L('cache')->set_auth_data($use.'_option_'. $member['id'], SYS_TIME, 1);
        }
    }
});

\Phpcmf\Hooks::on('admin_login_after', function($member) {
    $config = \Phpcmf\Service::M('app')->get_config('safe');
    $use = IS_ADMIN ? 'admin' : 'member';
    if (isset($config['pwd']['use']) && dr_in_array($use, $config['pwd']['use'])) {
        if (isset($config['safe']['wdl']) && $config['safe']['wdl']) {
            $log = \Phpcmf\Service::M('login', 'safe')->get_log($member['id']);
            \Phpcmf\Service::M()->table('app_login')->update($log['id'], [
                'logintime' => SYS_TIME,
            ]);
        }
    }
    if (isset($config['login']['use']) && dr_in_array($use, $config['login']['use'])) {
        // 操作标记
        if (isset($config['login']['is_option']) && $config['login']['is_option'] && $config['login']['exit_time']) {
            \Phpcmf\Service::L('cache')->set_auth_data($use.'_option_'. $member['id'], SYS_TIME, 1);
        }
    }
});

// 修改密码之后
\Phpcmf\Hooks::on('member_edit_password_after', function($member) {
    $config = \Phpcmf\Service::M('app')->get_config('safe');
    $use = IS_ADMIN ? 'admin' : 'member';
    if (isset($config['pwd']['use']) && dr_in_array($use, $config['pwd']['use'])) {
        $log = \Phpcmf\Service::M('login', 'safe')->get_log($member['id']);
        if (IS_ADMIN && \Phpcmf\Service::L('router')->class.'/'.\Phpcmf\Service::L('router')->method == 'member/edit') {
            // 表示管理员重置密码
            \Phpcmf\Service::M()->table('app_login')->update($log['id'], [
                'is_repwd' => 0,
                'updatetime' => 0,
            ]);
        } else {
            \Phpcmf\Service::M()->table('app_login')->update($log['id'], [
                'is_login' => SYS_TIME,
                'is_repwd' => SYS_TIME,
                'updatetime' => SYS_TIME,
            ]);
        }
    }
});

// 每次运行
\Phpcmf\Hooks::on('cms_init', function() {

    if (IS_API || IS_API_HTTP) {
        return;
    }

    $member = \Phpcmf\Service::C()->member;
    if ($member) {

        if (!IS_ADMIN && isset($config['login']['admin_not']) && $config['login']['admin_not'] && $member['is_admin']) {
            if (in_array(\Phpcmf\Service::L('router')->class, ['api', 'login', 'register'])) {
                return; // 本身控制器不判断
            }
            \Phpcmf\Service::C()->_admin_msg(0, '管理员账号禁止操作前台');
        }

        $uri = \Phpcmf\Service::L('router')->class.'/'.\Phpcmf\Service::L('router')->method;
        $log = \Phpcmf\Service::M('login', 'safe')->get_log($member['uid']);
        $config = \Phpcmf\Service::M('app')->get_config('safe');

        $use = IS_ADMIN ? 'admin' : (IS_MEMBER ? 'member' : '');
        if (!$use) {
            return;
        }

        if (isset($config['pwd']['use']) && dr_in_array($use, $config['pwd']['use'])) {
            // 重置密码后首次登录是否强制修改密码
            if (!$log['is_repwd'] && isset($config['pwd']['is_rlogin_edit']) && $config['pwd']['is_rlogin_edit']) {
                // 该改密码了
                if (\Phpcmf\Service::L('router')->class == 'api' || in_array($uri, ['api/my', 'account/password'])) {
                    return; // 本身控制器不判断
                } elseif (in_array(\Phpcmf\Service::L('router')->class, ['api', 'login', 'register'])) {
                    return;
                }
                \Phpcmf\Service::C()->_admin_msg(0, '首次登录需要强制修改密码', IS_ADMIN ? dr_url('api/my') : dr_member_url('account/password'));
            }

            // 首次登录是否强制修改密码
            if (!$log['is_login'] && isset($config['pwd']['is_login_edit']) && $config['pwd']['is_login_edit']) {
                // 该改密码了
                if (\Phpcmf\Service::L('router')->class == 'api' || in_array($uri, ['api/my', 'account/password'])) {
                    return; // 本身控制器不判断
                } elseif (in_array(\Phpcmf\Service::L('router')->class, ['api', 'login', 'register'])) {
                    return;
                }
                \Phpcmf\Service::C()->_admin_msg(0, '首次登录需要强制修改密码', IS_ADMIN ? dr_url('api/my') : dr_member_url('account/password'));
            }

            // 判断定期修改密码
            if (isset($config['pwd']['is_edit']) && $config['pwd']['is_edit']
                && isset($config['pwd']['day_edit']) && $config['pwd']['day_edit']) {
                if ($log['updatetime']) {
                    // 存在修改过密码才判断
                    $time = $config['pwd']['day_edit'] * 3600 * 24;
                    if (SYS_TIME - $log['updatetime'] > $time) {
                        // 该改密码了
                        if (\Phpcmf\Service::L('router')->class == 'api' || in_array($uri, ['api/my', 'account/password'])) {
                            return; // 本身控制器不判断
                        } elseif (in_array(\Phpcmf\Service::L('router')->class, ['api', 'login', 'register'])) {
                            return;
                        }
                        \Phpcmf\Service::C()->_admin_msg(0, '您需要定期修改密码', IS_ADMIN ? dr_url('api/my') : dr_member_url('account/password'));
                    }
                }
            }
        }

        if (isset($config['login']['use']) && dr_in_array($use, $config['login']['use'])) {
            // 操作标记
            if (in_array(\Phpcmf\Service::L('router')->class, ['api', 'login', 'register'])) {
                return; // 本身控制器不判断
            }
            if (isset($config['login']['is_option']) && $config['login']['is_option'] && $config['login']['exit_time']) {
                $time = (int)\Phpcmf\Service::L('cache')->get_auth_data($use.'_option_'.$member['id'], 1);
                $ctime = SYS_TIME - $time;
                if ($time && $ctime > $config['login']['exit_time'] * 60) {
                    // 长时间不动作退出
                    \Phpcmf\Service::M()->table('member')->update($log['uid'], [
                        'login_attr' => rand(0, 99999),
                    ]);
                    \Phpcmf\Service::M('member')->logout();
                    \Phpcmf\Service::C()->_admin_msg(0,
                        '长时间（'.ceil($ctime/60).'分钟）未操作，当前账号自动退出',
                        IS_ADMIN ? dr_url('login/index', ['go' => urlencode(FC_NOW_URL)]) : dr_member_url('login/index', ['back' => urlencode(FC_NOW_URL)])
                    );
                }
                \Phpcmf\Service::L('cache')->set_auth_data($use.'_option_'.$member['id'], SYS_TIME, 1);
            }
        }

    }
});

\Phpcmf\Hooks::on('module_search_data', function($data) {
    $config = \Phpcmf\Service::M('app')->get_config('safe');
    if (intval($data['contentid']) == 0 && isset($config['link']['search_seo']) && $config['link']['search_seo']) {
        \Phpcmf\Service::V()->assign(\Phpcmf\Service::C()->content_model->_format_search_seo(\Phpcmf\Service::C()->module, 0, [], 0));
        if (isset($config['link']['search_404']) && $config['link']['search_404']) {
            \Phpcmf\Service::C()->goto_404_page('内容匹配结果为空');
        }
    }
});


if (! function_exists('dr_redirect_safe_check'))
{
    /**
     * 跳转地址安全检测
     */
    function dr_redirect_safe_check($url) {
        if (strpos(urldecode($url), '()') !== false) {
            \Phpcmf\Service::C()->goto_404_page('此链接不允许跳转');
        }
        if (strpos($url, 'http://') === false
            && strpos($url, 'http://') === false
            && strpos($url, '//') === false
            && strpos($url, '/\\') === false
        ) {
            return $url; // 表示内链
        }
        $config = \Phpcmf\Service::M('app')->get_config('safe');
        if (isset($config['link']['use']) && $config['link']['use']) {
            $domain = \Phpcmf\Service::R(WRITEPATH.'config/domain_sso.php');
            if ($config['link']['domain']) {
                $arr = explode(PHP_EOL,  $config['link']['domain']);
                $domain = array_merge($domain, $arr);
            }
            $ok = 0;
            foreach ($domain as $t) {
                if (stripos($url, $t) !== false) {
                    $ok = 1;
                    break;
                }
            }
            if (!$ok) {
                \Phpcmf\Service::C()->_msg(0, '此链接不允许跳转');
            }
        }
        return $url;
    }
}