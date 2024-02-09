<?php namespace Phpcmf\Controllers\Admin;

class Mm extends \Phpcmf\App
{
    private $phpfile = [];

    public function index() {

        \Phpcmf\Service::V()->assign([
            'menu' => \Phpcmf\Service::M('auth')->_admin_menu(
                [
                    '木马文件扫描' => ['safe/mm/index', 'fa fa-bug'],
                ]
            ),
        ]);
        \Phpcmf\Service::V()->display('muma.html');
    }

    // php文件个数
    public function php_count_index() {
        // 读取文件到缓存
        $this->_file_map(WEBPATH, 1);
        $this->_file_map(ROOTPATH.'config/');
        $this->_file_map(CONFIGPATH);
        if (is_file(MYPATH.'Dev.php')) {
            $this->_file_map(dr_get_app_list());
        }
        $this->_file_map(WRITEPATH);
        $this->_file_map(FCPATH);
        $this->_file_map(MYPATH);
        $this->_file_map(APPSPATH);

        $cache = [];
        $count = $this->phpfile ? count($this->phpfile) : 0;
        if ($count > 100) {
            $pagesize = ceil($count/100);
            for ($i = 1; $i <= 100; $i ++) {
                $cache[$i] = array_slice($this->phpfile, ($i - 1) * $pagesize, $pagesize);
            }
        } else {
            for ($i = 1; $i <= $count; $i ++) {
                $cache[$i] = array_slice($this->phpfile, ($i - 1), 1);
            }
        }

        // 存储文件
        \Phpcmf\Service::L('cache')->set_data('check-index', $cache, 3600);

        $this->_json($cache ? count($cache) : 0, 'ok');
    }

    public function php_check_index() {

        $page = max(1, intval($_GET['page']));
        $cache = \Phpcmf\Service::L('cache')->get_data('check-index');
        !$cache && $this->_json(0, '数据缓存不存在');

        $data = $cache[$page];
        if ($data) {
            $html = '';
            foreach ($data as $filename) {

                // 避免自杀
                if (in_array(basename($filename), [
                    'Check_bom.php',
                    'error_exception.php',
                    'Mm.php'
                ])) {
                    continue;
                }

                $contents = file_get_contents ( $filename );

                $ok = "<span class='ok'>正常</span>";
                $class = '';
                if ($this->_is_bom($contents)) {
                    $ok = "<span class='error'>存在Bom字符</span>";
                    $class = ' p_error';
                } elseif ($this->_is_muma($contents)) {
                    $ok = "<span class='error'>可能存在问题</span>";
                    $class = ' p_error';
                } elseif (strpos($filename, APPSPATH) !== false && strpos($contents, '$_POST[')) {
                    if (strpos($contents, '=$_POST[') || strpos($contents, '= $_POST[')) {
                        $ok = "<span class='error'>POST可能不安全</span>";
                        $class = ' p_error';
                    } else {
                        $ok = "<span class='ok'>正常</span>";
                    }
                } elseif (strpos($filename, APPSPATH) !== false && strpos($contents, '$_GET[')) {
                    if (strpos($contents, '=$_GET[') || strpos($contents, '= $_GET[')) {
                        $ok = "<span class='error'>GET可能不安全</span>";
                        $class = ' p_error';
                    } else {
                        $ok = "<span class='ok'>正常</span>";
                    }
                }

                $html.= '<p class="'.$class.'"><label class="rleft">'.dr_safe_replace_path($filename).'</label><label class="rright">'.$ok.'</label></p>';
                if ($class) {
                    $html.= '<p class="rbf" style="display: none"><label class="rleft">'.$filename.'</label><label class="rright">'.$ok.'</label></p>';
                }
            }
            $this->_json($page + 1, $html);
        }

        // 完成
        \Phpcmf\Service::L('cache')->clear('check-index');
        $this->_json(100, '');
    }

    private function _is_muma($contents) {
        if (stripos($contents, 'eval($_POST') !== false) {
            return 1;
        } elseif (stripos($contents, 'eval($_GET') !== false) {
            return 1;
        } elseif (stripos($contents, 'eval($_REQUEST') !== false) {
            return 1;
        } elseif (stripos($contents, 'set_time_limit(0);header(') !== false) {
            return 1;
        } elseif (stripos($contents, 'function papa($h)') !== false) {
            return 1;
        }
        return 0;
    }

    private function _is_bom($contents) {
        $charset [1] = substr ( $contents, 0, 1 );
        $charset [2] = substr ( $contents, 1, 1 );
        $charset [3] = substr ( $contents, 2, 1 );
        if (ord ( $charset [1] ) == 239 && ord ( $charset [2] ) == 187 && ord ( $charset [3] ) == 191) {
            return 1;
        }
        return 0;
    }

    private function _file_map($source_dir, $exit = 0) {
        if ($fp = opendir($source_dir)) {
            $source_dir	= rtrim($source_dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
            while (false !== ($file = readdir($fp))) {
                // Remove '.', '..', and hidden files [optional]
                if ($file === '.' || $file === '..') {
                    continue;
                }
                is_dir($source_dir.$file) && $file .= DIRECTORY_SEPARATOR;
                if (is_dir($source_dir.$file) && !$exit) {
                    $this->_file_map($source_dir.$file, $exit);
                } else {
                    trim(strtolower(strrchr($file, '.')), '.') == 'php' && $this->phpfile[] = $source_dir.$file;
                }
            }
            closedir($fp);
        }
    }
}
