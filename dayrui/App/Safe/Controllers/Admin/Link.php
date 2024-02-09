<?php namespace Phpcmf\Controllers\Admin;

class Link extends \Phpcmf\Common
{

    public function index() {

        $data = \Phpcmf\Service::M('app')->get_config(APP_DIR);

        $domain = \Phpcmf\Service::R(WRITEPATH.'config/domain_sso.php');

        if (IS_AJAX_POST) {

            $post = \Phpcmf\Service::L('input')->post('data');
            $data['link'] = $post;
            \Phpcmf\Service::M('app')->save_config(APP_DIR, $data);

            $this->_json(1, dr_lang('操作成功'));
        }

        $page = intval(\Phpcmf\Service::L('input')->get('page'));

        \Phpcmf\Service::V()->assign([
            'page' => $page,
            'data' => $data['link'],
            'form' => dr_form_hidden(['page' => $page]),
            'menu' => \Phpcmf\Service::M('auth')->_admin_menu(
                [
                    '插件配置' => [APP_DIR.'/link/index', 'fa fa-link'],
                ]
            ),
            'domain' => $domain,
        ]);
        \Phpcmf\Service::V()->display('link.html');
    }



}
