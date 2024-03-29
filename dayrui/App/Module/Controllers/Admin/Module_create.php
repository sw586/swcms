<?php namespace Phpcmf\Controllers\Admin;


class Module_create extends \Phpcmf\Common
{

    // 创建模块
    public function index() {

        if (IS_AJAX_POST) {

            $data = \Phpcmf\Service::L('input')->post('data');

            // 参数判断
            if (!$data['name']) {
                $this->_json(0, dr_lang('名称不能为空'), ['field' => 'name']);
            } elseif (!$data['dirname']) {
                $this->_json(0, dr_lang('目录不能为空'), ['field' => 'dirname']);
            }

            $data['dirname'] = strtolower($data['dirname']);
            if (!preg_match('/^[a-z]+$/i', $data['dirname'])) {
                $this->_json(0, dr_lang('目录只能是英文字母'), ['field' => 'dirname']);
            } elseif (is_dir(APPSPATH.ucfirst($data['dirname']))) {
                $this->_json(0, dr_lang('此目录已经存在'), ['field' => 'dirname']);
            } elseif (!$data['icon']) {
                $this->_json(0, dr_lang('模块图标不能为空'), ['field' => 'icon']);
            } elseif (!dr_check_put_path(APPSPATH)) {
                $this->_json(0, dr_lang('服务器没有创建目录的权限'), ['field' => 'dirname']);
            } elseif (\Phpcmf\Service::M('app')->is_sys_dir($data['dirname'])) {
                $this->_json(0, dr_lang('目录[%s]名称是系统保留名称，请重命名', $data['dirname']));
            }

            // 开始复制到指定目录
            $path = APPSPATH.ucfirst($data['dirname']).'/';
            \Phpcmf\Service::L('File')->copy_file(APPPATH.'Temps/Module/', $path);
            if (!is_file($path.'Config/App.php')) {
                $this->_json(0, dr_lang('目录创建失败，请检查文件权限'), ['field' => 'dirname']);
            }

            // 替换模块配置文件
            $app = file_get_contents($path.'Config/App.php');
            $app = str_replace(['{name}', '{icon}'], [dr_safe_filename($data['name']), dr_safe_replace($data['icon'])], $app);
            file_put_contents($path.'Config/App.php', $app);

            $this->_json(1, dr_lang('模块创建成功'), [
                'url' => dr_url('module/module/index')
            ]);
            exit;

        }

        \Phpcmf\Service::V()->assign([
            'form' => dr_form_hidden(),
            'menu' =>  \Phpcmf\Service::M('auth')->_admin_menu([
                '模块管理' => [APP_DIR.'/module/index', 'fa fa-cogs'],
                '创建模块' => [APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/index', 'fa fa-plus'],
                'help' => [24]
            ])
        ]);
        \Phpcmf\Service::V()->display('module_create.html');
    }




}
