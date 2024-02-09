<?php namespace Phpcmf\Controllers\Admin;

class Config extends \Phpcmf\Common
{

    public function index() {

        $data = \Phpcmf\Service::M('app')->get_config(APP_DIR);

        if (is_file(WRITEPATH.'config/system.php')) {
            $system = require WRITEPATH.'config/system.php'; // 加载网站系统配置文件
        } else {
            $system = [];
        }

        $table = \Phpcmf\Service::M()->dbprefix('member_setting');
        if (\Phpcmf\Service::M()->db->tableExists($table)) {
            // 获取会员全部配置信息
            $member = [];
            $result = \Phpcmf\Service::M()->db->table('member_setting')->get()->getResultArray();
            if ($result) {
                foreach ($result as $t) {
                    $member[$t['name']] = dr_string2array($t['value']);
                }
            }
        } else {
            \Phpcmf\Service::M()->query(dr_format_create_sql('CREATE TABLE IF NOT EXISTS `'.$table.'` (
              `name` varchar(50) NOT NULL,
              `value` mediumtext NOT NULL,
              PRIMARY KEY (`name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT=\'用户属性参数表\';'));
        }

        if (IS_AJAX_POST) {

            // 更新到后台表

            $post_system = \Phpcmf\Service::L('input')->post('system');
            $system['SYS_ADMIN_LOGIN_TIME'] = $post_system['SYS_ADMIN_LOGIN_TIME'];
            $system['SYS_ADMIN_LOGINS'] = $post_system['SYS_ADMIN_LOGINS'];
            \Phpcmf\Service::M('System')->save_config($system, $system);

            $post = \Phpcmf\Service::L('input')->post('data');
            $post['link'] = $data['link']; // 避免清空
            \Phpcmf\Service::M('app')->save_config(APP_DIR, $post);

            $post_member = \Phpcmf\Service::L('input')->post('member');
            $member['config']['pwdlen'] = $post_member['config']['pwdlen'];
            $member['config']['user2pwd'] = $post_member['config']['user2pwd'];
            $member['config']['pwdpreg'] = $post_member['config']['pwdpreg'];
            \Phpcmf\Service::M()->db->table('member_setting')->replace([
                'name' => 'config',
                'value' => dr_array2string($member['config'])
            ]);

            $this->_json(1, dr_lang('操作成功'));
        }

        $page = intval(\Phpcmf\Service::L('input')->get('page'));

        \Phpcmf\Service::V()->assign([
            'page' => $page,
            'data' => $data,
            'form' => dr_form_hidden(['page' => $page]),
            'menu' => \Phpcmf\Service::M('auth')->_admin_menu(
                [
                    '插件配置' => [APP_DIR.'/config/index', 'fa fa-cog'],
                ]
            ),
            'system' => $system,
            'member_config' => $member,
            'password_rule' => [
                '无要求' => '',
                '大写字母+小写字母+特殊字符+数字' => '/^[\w_-]+$/',
            ],
        ]);
        \Phpcmf\Service::V()->display('config.html');
    }



}
