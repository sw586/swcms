<?php namespace Phpcmf\Controllers\Admin;

class Home extends \Phpcmf\App
{
    private $option = [
        '1' => '后台入口名称',
        '2' => '数据缓存cache目录',
        '3' => '核心程序dayrui目录',
        '4' => '前端模板template目录',
        '5' => '文件上传存储目录',
        '6' => '用户头像上传目录',
        '7' => 'web目录权限',
        '8' => '跨站提交验证',
        '9' => '后台单独域名管理',
        '10' => '入口文件权限',
    ];
    private $phpfile = [];


    public function index() {

        $path = WRITEPATH.'app/safe/';
        $is_ok = $count = 0;
        foreach ($this->option as $key => $v) {
            if (is_file($path.$key.'.txt')) {
                $count++;
            }
        }
        if ($count == count($this->option)) {
            file_put_contents(WRITEPATH.'config/safe.php', 'ok');
            $is_ok = 1;
        }

        \Phpcmf\Service::V()->assign([
            'list' => $this->option,
            'menu' => \Phpcmf\Service::M('auth')->_admin_menu(
                [
                    '安全检测' => ['safe/home/index', 'fa fa-shield'],
                    'help' => [593],
                ]
            ),
            'is_ok' => $is_ok,
        ]);

        \Phpcmf\Service::V()->display('safe.html');
    }

    public function do_index() {

        $id = trim($_GET['id']);

        switch ($id) {

            case '1':

                if (SELF == 'admin.php') {
                    $this->_is_ok($id, 0);
                    $this->_json(0, '修改根目录admin.php的文件名，可以有效的防止被猜疑破解');
                }
                $this->_is_ok($id);
                break;


            case '2':
                if (strpos(WRITEPATH, WEBPATH) !== false) {
                    $this->_is_ok($id, 0);
                    $this->_json(0, '将/cache/目录转移到Web目录之外的目录，可以防止缓存数据不被Web读取');
                }
                $this->_is_ok($id);
                break;


            case '3':
                if (strpos(FCPATH, WEBPATH) !== false) {
                    $this->_is_ok($id, 0);
                    $this->_json(0, '将/dayrui/目录转移到Web目录之外的目录，可以防止核心程序不被Web读取');
                }
                break;


            case '4':
                if (strpos(TPLPATH, WEBPATH) !== false) {
                    $this->_is_ok($id, 0);
                    $this->_json(0, '将/template/目录转移到Web目录之外的目录，可以防止模板文件不被下载');
                }
                $this->_is_ok($id);
                break;


            case '5':
                if (strpos(SYS_UPLOAD_PATH, WEBPATH) !== false) {
                    $this->_is_ok($id, 0);
                    $this->_json(0, '将/uploadfile/目录转移到Web目录之外的目录，可以防止非法上传恶意文件');
                }

                if (!file_put_contents(SYS_UPLOAD_PATH.'test.php', '<?php echo "php7";')) {
                    $this->_is_ok($id, 0);
                    $this->_json(0,'目录['.SYS_UPLOAD_PATH.']无法写入文件');
                }

                $url = SYS_UPLOAD_URL.'test.php';
                if (!function_exists('stream_context_create')) {
                    $this->_is_ok($id, 0);
                    $this->_json(0, '函数没有被启用：stream_context_create');
                }
                $context = stream_context_create(array(
                    'http' => array(
                        'timeout' => 5 //超时时间，单位为秒
                    )
                ));
                $code = file_get_contents($url, 0, $context);
                if ($code == '<?php echo "php7";') {
                    $this->_is_ok($id);
                    $this->_json(1, '安全');
                } elseif ($code == 'php7') {
                    $this->_is_ok($id, 0);
                    $this->_json(0, '必须将附件域名使用纯静态网站');
                } else {
                    $this->_is_ok($id);
                    $this->_json(1, '域名绑定异常，无法访问：'.$url.'，可以尝试手动访问此地址，<br>如果提示<？php echo "php7";就表示成功', 0);
                }

                break;


            case '6':

                list($cache_path, $cache_url) = dr_avatar_path();
                if (strpos($cache_path, WEBPATH) !== false) {
                    $this->_is_ok($id, 0);
                    $this->_json(0, '将头像目录转移到Web目录之外的目录，可以防止非法上传恶意文件');
                }

                if (!file_put_contents($cache_path.'test.php', '<?php echo "php7";')) {
                    $this->_is_ok($id, 0);
                    $this->_json(0,'目录['.$cache_path.']无法写入文件');
                }

                $url = $cache_url.'test.php';
                if (!function_exists('stream_context_create')) {
                    $this->_is_ok($id, 0);
                    $this->_json(0, '函数没有被启用：stream_context_create');
                }
                $context = stream_context_create(array(
                    'http' => array(
                        'timeout' => 5 //超时时间，单位为秒
                    )
                ));
                $code = file_get_contents($url, 0, $context);
                if ($code == '<?php echo "php7";') {
                    $this->_is_ok($id);
                    $this->_json(1, '安全');
                } elseif ($code == 'php7') {
                    $this->_is_ok($id, 0);
                    $this->_json(0, '必须将头像域名使用纯静态网站');
                } else {
                    $this->_is_ok($id);
                    $this->_json(1, '域名绑定异常，无法访问：'.$url.'，可以尝试手动访问此地址，<br>如果提示<？php echo "php7";就表示成功', 0);
                }

                break;


            case '7':

                if (IS_EDIT_TPL) {
                    $this->_is_ok($id, 0);
                    $this->_json(0, '系统开启了在线编辑模板权限，建议关闭此权限');
                }
                $this->_is_ok($id);
                if (file_put_contents(WEBPATH.'test_phpcmf.php', '<?php echo "test_phpcmf";')) {
                    unlink(WEBPATH.'test_phpcmf.php');
                    $this->_json(1,'WEB根目录['.WEBPATH.']：除了静态生成目录之外，建议赋予只读权限，Linux555权限');
                }
                break;


            case '8':

                if (defined('SYS_CSRF') && !SYS_CSRF) {
                    $this->_json(1, '系统没有开启跨站验证，建议开启');
                }
                $this->_is_ok($id);
                break;


            case '9':
                if (!defined('IS_MY_ADMIN')) {
                    $this->_is_ok($id, 0);
                    $this->_json(0, '后台设置单独访问域名，将前后台分离部署');
                }
                $this->_is_ok($id);
                break;


            case '10':

                $v = substr(sprintf('%o', fileperms(WEBPATH.'index.php')), -3);
                if (!in_array($v, ['444', '555', '400'])) {
                    $this->_is_ok($id, 0);
                    $this->_json(0, '入口文件['.WEBPATH.'index.php]需要设置为只读权限，Linux555权限');
                }
                $this->_is_ok($id);
                break;

        }

        $this->_json(1,'合格');
    }

    private function _is_ok($key, $is_save = 1) {
        $path = WRITEPATH.'app/safe/';
        dr_mkdirs($path);
        if (!$is_save) {
            unlink($path.$key.'.txt');
        } else {
            file_put_contents($path.$key.'.txt', 'ok');
        }
    }

    public function book_index() {
        $id = intval($_GET['id']);
        if ($id == 9) {
            dr_redirect(dr_url('safe/adomain/index'));
        }
        \Phpcmf\Service::V()->display('book_'.$id.'.html');exit;
    }

}
