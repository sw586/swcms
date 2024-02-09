<?php namespace Phpcmf\Controllers\Admin;

class Adomain extends \Phpcmf\App
{
    private $phpfile = [];


    public function index() {

        \Phpcmf\Service::V()->assign([
            'list' => \Phpcmf\Service::M()->table('site')->order_by('id asc')->getAll(),
            'data' => \Phpcmf\Service::M('app')->get_config(APP_DIR),
            'menu' => \Phpcmf\Service::M('auth')->_admin_menu(
                [
                    '后台域名' => ['safe/adomain/index', 'fa fa-cog'],
                    'help' => [731],
                ]
            ),
        ]);

        \Phpcmf\Service::V()->display('admin.html');
    }

    public function add() {

        $id = (int)$_GET['id'];
        $path = trim($_GET['path']);
        if (!$id) {
            $this->_json(0, '站点id异常');
        } elseif (!$path) {
            $this->_json(0, '目录未填写');
        } elseif (!is_dir($path)) {
            $this->_json(0, '目录['.$path.']不存在，请填写绝对路径的目录');
        } elseif (is_file($path.'/index.php')) {
            if (strpos(file_get_contents($path.'/index.php'), 'IS_MY_ADMIN') === false) {
                //$this->_json(0, '目录中存在index.php文件，无法生成');
            }
        }

        $data = \Phpcmf\Service::M()->table('site')->get($id);
        if (!$data) {
            $this->_json(0, '站点不存在');
        }

        $rt = file_put_contents($path.'/index.php', '<?php
define(\'SITE_ID\', '.$id.');
define(\'IS_MY_ADMIN\', '.$id.');
require "'.WEBPATH.'index.php";
');
        if (!$rt) {
            $this->_json(0, '目录['.$path.']无法创建文件');
        }
        $rt = file_put_contents($path.'/'.SELF, '<?php
define(\'IS_ADMIN\', TRUE); // 项目标识
define(\'SELF\', pathinfo(__FILE__, PATHINFO_BASENAME)); // 该文件的名称
require(\'index.php\'); // 引入主文件
');
        if (!$rt) {
            $this->_json(0, '目录['.$path.']无法创建文件');
        }

        $rt = file_put_contents($path.'/rewrite.php', '<?php
error_reporting(0);
define(\'SELF\', \'index.php\');
// 伪静态字符串
$uu = isset($_SERVER[\'HTTP_X_REWRITE_URL\']) || trim($_SERVER[\'REQUEST_URI\'], \'/\') == SELF ? trim($_SERVER[\'HTTP_X_REWRITE_URL\'], \'/\') : ($_SERVER[\'REQUEST_URI\'] ? trim($_SERVER[\'REQUEST_URI\'], \'/\') : NULL);
$uri = strpos($uu, SELF) === 0 ? \'\' : $uu;

header("HTTP/1.1 301 Moved Permanently");
header("Location: '.dr_http_prefix($data['domain']).'/".$uri);
exit();
');
        if (!$rt) {
            $this->_json(0, '目录['.$path.']无法创建文件');
        }

        // 复制百度编辑器到当前目录
        \Phpcmf\Service::M('cache')->cp_ueditor_file($path.'/');

        $data = \Phpcmf\Service::M('app')->get_config(APP_DIR);
        if (!$data) {
            $data = [
                $id => '',
            ];
        }
        $data[$id] = $path;
        \Phpcmf\Service::M('app')->save_config(APP_DIR, $data);

        $this->_json(1, '生成文件成功');
    }

}
