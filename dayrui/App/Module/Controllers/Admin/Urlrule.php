<?php namespace Phpcmf\Controllers\Admin;

// URL规则
class Urlrule extends \Phpcmf\Table
{
    public $type;

    public function __construct()
    {
        parent::__construct();
        $this->type = array(
            //0 => dr_lang('自定义页面插件'),
            1 => dr_lang('独立模块'),
            2 => dr_lang('共享模块搜索'),
            3 => dr_lang('共享栏目和内容页'),
            //5 => dr_lang('模块表单'),
        );
        if (!dr_is_app('page')) {
            unset($this->type[0]);
        }
        \Phpcmf\Service::V()->assign([
            'menu' => \Phpcmf\Service::M('auth')->_admin_menu(
                [
                    'URL规则' => [APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/index', 'fa fa-link'],
                    '添加' => [APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/add', 'fa fa-plus'],
                    '修改' => ['hide:'.APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/edit', 'fa fa-edit'],
                    '伪静态' => [APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/rewrite_index', 'fa fa-cog'],
                    '导入' => ['add:'.APP_DIR.'/urlrule/import_add', 'fa fa-sign-in', '60%', '70%'],
                    'help' => [418],
                ]
            ),
        ]);
        // 支持附表存储
        $this->is_data = 0;
        $this->my_field = array(
            'name' => array(
                'ismain' => 1,
                'name' => dr_lang('名称'),
                'fieldname' => 'name',
                'fieldtype' => 'Text',
                'setting' => array(
                    'option' => array(
                        'width' => 200,
                    ),
                    'validate' => array(
                        'required' => 1,
                    )
                )
            ),
        );
        // url显示名称
        $this->name = dr_lang('URL规则');
        // 初始化数据表
        $this->_init([
            'table' => 'urlrule',
            'field' => $this->my_field,
            'order_by' => 'id desc',
        ]);
    }

    // 后台查看url列表
    public function index() {
        
        $this->_List([], -1);
        \Phpcmf\Service::V()->assign('color', [
            0 => 'default',
            1 => 'info',
            2 => 'success',
            3 => 'warning',
            4 => 'danger',
            5 => '',
            6 => 'primary',
        ]);
        \Phpcmf\Service::V()->display('urlrule_index.html');
    }

    // 伪静态
    public function rewrite_index() {

        $domain = [];
        list($module, $site) = \Phpcmf\Service::M('Site')->domain();
        $domain[$site['site_domain']] = dr_lang('本站电脑域名');
        $site['mobile_domain'] && $domain[$site['mobile_domain']] = dr_lang('本站手机域名');
        if ($module) {
            foreach ($module as $dir => $t) {
                if ($site['module_'.$dir]) {
                    $domain[$site['module_'.$dir]] = dr_lang('%s电脑域名', $t['name']);
                }
                if ($site['module_mobile_'.$dir]) {
                    $domain[$site['module_mobile_'.$dir]] = dr_lang('%s手机域名', $t['name']);
                }
            }
        }

        $site = \Phpcmf\Service::M('Site')->config(SITE_ID);
        if ($site['client']) {
            foreach ($site['client'] as $t) {
                if ($t['domain']) {
                    $domain[$t['domain']] = dr_lang('%s终端域名', $t['name']);
                }
            }
        }

        if (strpos($site['config']['SITE_DOMAIN'], '/') !== false) {
            list($a, $b) = explode('/', $site['config']['SITE_DOMAIN']);
            $root = '/'.$b;
        } else {
            $root = '';
        }
        $server = strtolower($_SERVER['SERVER_SOFTWARE']);
        if (strpos($server, 'apache') !== FALSE) {
            $name = 'Apache';
            $note = '<font color=red><b>将以下内容保存为.htaccess文件，放到每个域名所绑定的根目录</b></font>';
            $code = '';

            // 子目录
            $code.= '###当存在多个子目录格式的域名时，需要多写几组RewriteBase标签：RewriteBase /目录/ '.PHP_EOL;
            if (isset($site['mobile']['mode']) && $site['mobile']['mode'] && $site['mobile']['dirname']) {
                $code.= 'RewriteEngine On'.PHP_EOL.PHP_EOL;
                $code.= 'RewriteBase /'.$site['mobile']['dirname'].'/'.PHP_EOL
                    .'RewriteCond %{REQUEST_FILENAME} !-f'.PHP_EOL
                    .'RewriteCond %{REQUEST_FILENAME} !-d'.PHP_EOL
                    .'RewriteRule !.(js|ico|gif|jpe?g|bmp|png|css)$ /'.$site['mobile']['dirname'].'/index.php [NC,L]'.PHP_EOL.PHP_EOL;
                $code.= '####以上目录需要单独保持到/'.$site['mobile']['dirname'].'/.htaccess文件中';
            }
            // 主目录
            $code.= 'RewriteEngine On'.PHP_EOL.PHP_EOL;
            $code.= 'RewriteBase '.$root.'/'.PHP_EOL
                .'RewriteCond %{REQUEST_FILENAME} !-f'.PHP_EOL
                .'RewriteCond %{REQUEST_FILENAME} !-d'.PHP_EOL
                .'RewriteRule !.(js|ico|gif|jpe?g|bmp|png|css)$ '.$root.'/index.php [NC,L]'.PHP_EOL.PHP_EOL;
        } elseif (strpos($server, 'nginx') !== FALSE) {
            $name = $server;
            $note = '<font color=red><b>将以下代码放到Nginx配置文件中去（如果是绑定了域名，所绑定目录也要配置下面的代码）</b></font>';
            // 子目录
            $code = '###当存在多个子目录格式的域名时，需要多写几组location标签：location /目录/ '.PHP_EOL;
            if (isset($site['mobile']['mode']) && $site['mobile']['mode'] && $site['mobile']['dirname']) {
                $code.= 'location '.$root.'/'.$site['mobile']['dirname'].'/ { '.PHP_EOL
                    .'    if (-f $request_filename) {'.PHP_EOL
                    .'           break;'.PHP_EOL
                    .'    }'.PHP_EOL
                    .'    if ($request_filename ~* "\.(js|ico|gif|jpe?g|bmp|png|css)$") {'.PHP_EOL
                    .'        break;'.PHP_EOL
                    .'    }'.PHP_EOL
                    .'    if (!-e $request_filename) {'.PHP_EOL
                    .'        rewrite . '.$root.'/'.$site['mobile']['dirname'].'/index.php last;'.PHP_EOL
                    .'    }'.PHP_EOL
                    .'}'.PHP_EOL.PHP_EOL;
            }
            // 主目录
            $code.= 'location '.$root.'/ { '.PHP_EOL
                .'    if (-f $request_filename) {'.PHP_EOL
                .'           break;'.PHP_EOL
                .'    }'.PHP_EOL
                .'    if ($request_filename ~* "\.(js|ico|gif|jpe?g|bmp|png|css)$") {'.PHP_EOL
                .'        break;'.PHP_EOL
                .'    }'.PHP_EOL
                .'    if (!-e $request_filename) {'.PHP_EOL
                .'        rewrite . '.$root.'/index.php last;'.PHP_EOL
                .'    }'.PHP_EOL
                .'}'.PHP_EOL;
        } else {
            $name = $server;
            $note = '<font color=red><b>无法为此服务器提供伪静态规则，建议让运营商帮你把下面的Apache规则做转换</b></font>';
            $code = 'RewriteEngine On'.PHP_EOL
                .'RewriteBase /'.PHP_EOL
                .'RewriteCond %{REQUEST_FILENAME} !-f'.PHP_EOL
                .'RewriteCond %{REQUEST_FILENAME} !-d'.PHP_EOL
                .'RewriteRule !.(js|ico|gif|jpe?g|bmp|png|css)$ /index.php [NC,L]';
        }

        \Phpcmf\Service::V()->assign([
            'name' => $name,
            'code' => $code,
            'note' => $note,
            'site' => $site,
            'menu' => \Phpcmf\Service::M('auth')->_admin_menu(
                [
                    'URL规则' => [APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/index', 'fa fa-link'],
                    '伪静态' => [APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/rewrite_index', 'fa fa-cog'],
                    'help' => [21],
                ]
            ),
            'count' => $code ? dr_count(explode(PHP_EOL, $code)) : 0,
            'domain' => $domain,
        ]);
        \Phpcmf\Service::V()->display('urlrule_rewrite.html');
    }

    // 后台添加url内容
    public function add() {
        $this->_Post(0);
        \Phpcmf\Service::V()->display('urlrule_add.html');
    }

    // 后台修改url内容
    public function edit() {
        $this->_Post(intval(\Phpcmf\Service::L('input')->get('id')));
        \Phpcmf\Service::V()->display('urlrule_add.html');
    }

    // 复制url
    public function copy_edit() {

        $id = intval(\Phpcmf\Service::L('input')->get('id'));
        $data = \Phpcmf\Service::M()->db->table('urlrule')->where('id', $id)->get()->getRowArray();
        if (!$data) {
            $this->_json(0, dr_lang('数据#%s不存在', $id));
        }

        unset($data['id']);
        $data['name'].= '_copy';

        $rt = \Phpcmf\Service::M()->table('urlrule')->insert($data);
        if (!$rt['code']) {
            $this->_json(0, dr_lang($rt['msg']));
        }

        \Phpcmf\Service::M('cache')->sync_cache('urlrule', 'module');
        $this->_json(1, dr_lang('复制成功'));
    }

    // 保存
    protected function _Save($id = 0, $data = [], $old = [], $func = null, $func2 = null) {
        return parent::_Save($id, $data, $old, function($id, $data){
            // 保存前的格式化
            $type = (int)\Phpcmf\Service::L('input')->post('type');
            $value = \Phpcmf\Service::L('input')->post('value');
            if ($value[$type]) {
                foreach ($value[$type] as $i => $t) {
                    if (strpos($t, '?') !== false) {
                        $this->_json(0, dr_lang('URL规则中不能包含%s号', '?'));
                    } elseif (strpos($t, '#') !== false) {
                        $this->_json(0, dr_lang('URL规则中不能包含%s号', '#'));
                    } elseif (strpos($t, '(')
                        && preg_match('/\{([a-z0-9_]+)\(\$data\)\}/iU', $t, $mt)) {
                        if (!function_exists($mt[1])) {
                            $this->_json(0, dr_lang('URL规则中函数%s未定义', $mt[1]));
                        }
                    }
                    $value[$type][$i] = trim($t);
                }
            }
            $data[1]['type'] = $type;
            $join = \Phpcmf\Service::L('input')->post('catjoin');
            $value[$type]['catjoin'] = $join ? $join : '/';
            $data[1]['value'] = dr_array2string($value[$type]);
            return dr_return_data(1, 'ok', $data);
        }, function ($id, $data, $old) {
            \Phpcmf\Service::M('cache')->sync_cache('urlrule', 'module');
        });
    }

    // 导出
    public function export_edit() {

        $id = intval(\Phpcmf\Service::L('input')->get('id'));
        $data = \Phpcmf\Service::M()->table('urlrule')->get($id);
        if (!$data) {
            $this->_admin_msg(0, dr_lang('URL规则（%s）不存在', $id));
        }

        \Phpcmf\Service::V()->assign([
            'data' => dr_array2string($data),
        ]);
        \Phpcmf\Service::V()->display('api_export_code.html');exit;
    }

    // 导入
    public function import_add() {

        if (IS_AJAX_POST) {
            $data = \Phpcmf\Service::L('input')->post('code');
            $data = dr_string2array($data);
            if (!is_array($data)) {
                $this->_json(0, dr_lang('导入信息验证失败'));
            } elseif (!$data['value']) {
                $this->_json(0, dr_lang('导入信息不完整'));
            }
            unset($data['id']);
            $rt = \Phpcmf\Service::M()->table('urlrule')->insert($data);
            if (!$rt['code']) {
                $this->_json(0, $rt['msg']);
            }
            \Phpcmf\Service::M('cache')->sync_cache('urlrule', 'module');
            $this->_json(1, dr_lang('操作成功'));
        }

        \Phpcmf\Service::V()->assign([
            'data' => '',
            'form' => dr_form_hidden(),
        ]);
        \Phpcmf\Service::V()->display('api_export_code.html');
        exit;
    }

    /**
     * 获取内容
     * $id      内容id,新增为0
     * */
    protected function _Data($id = 0) {
        $data = parent::_Data($id);
        $data['value'] = dr_string2array($data['value']);
        return $data;
    }

    // 后台删除url内容
    public function del() {
        $this->_Del(
            \Phpcmf\Service::L('input')->get_post_ids(),
            null,
            function ($r) {
                \Phpcmf\Service::M('cache')->sync_cache('urlrule', 'module');
            }
        );
    }

    // 生成伪静态解析文件规则
    public function rewrite_add() {
        $rt = $this->get_rewrite_code();
        $this->_json($rt['code'], $rt['msg'], $rt['data']);
    }

    // 生成伪静态解析代码
    private function get_rewrite_code() {

        $data = \Phpcmf\Service::M()->table('urlrule')->getAll();
        if (!$data) {
            return dr_return_data(0, dr_lang('你没有设置URL规则'));
        }

        $code = '';
        $error = '';
        $write = []; // 防止重复
        foreach ($data as $r) {
            $value = dr_string2array($r['value']);
            if ($r['type'] == 1) {
                // 独立模块
                $code.= PHP_EOL.'	// '.$r['name'].'---解析规则----开始'.PHP_EOL;
                if ($value['module']) {
                    $rule = $value['module'];
                    $cname = "【".$r['name']."】模块首页（{$rule}）";
                    list($preg, $rname) = $this->_rule_preg_value($rule);
                    if (!$preg || !$rname) {
                        $error.= "<p>".$cname."无法识别，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{modname}'])) {
                        $error.= "<p>".$cname."缺少{modname}标签，需要手动写解析规则</p>";
                    } else {
                        $rule = 'index.php?s=$'.$rname['{modname}'];
                        if (isset($write[$preg])) {
                            $error.= "<p>".$cname."与".$write[$preg]."规则存在冲突，需要手动写解析规则</p>";
                        } else {
                            $write[$preg] = $cname;
                            $code.= '<textarea class="form-control" rows="1">    "'.$preg.'" => "'.$rule.'",  //'.$cname.'（此规则由系统生成，不一定会准确，请开发者自行调整）</textarea>';
                        }
                    }
                }
                if ($value['list_page']) {
                    $rule = $value['list_page'];
                    $cname = "【".$r['name']."】模块栏目列表(分页)（{$rule}）";
                    list($preg, $rname) = $this->_rule_preg_value($rule);
                    if (!$preg || !$rname) {
                        $error.= "<p>".$cname."无法识别，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{page}'])) {
                        $error.= "<p>".$cname."缺少{page}标签，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{modname}'])) {
                        $error.= "<p>".$cname."缺少{modname}标签，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{dirname}']) && !isset($rname['{id}']) && !isset($rname['{pdirname}'])) {
                        $error.= "<p>".$cname."缺少{dirname}或{id}或{pdirname}标签，需要手动写解析规则</p>";
                    } else {
                        if (isset($rname['{dirname}'])) {
                            // 目录格式
                            $rule = 'index.php?s=$'.$rname['{modname}'].'&c=category&dir=$'.$rname['{dirname}'].'&page=$'.$rname['{page}'];
                        } elseif (isset($rname['{pdirname}'])) {
                            // 层次目录格式
                            $rule = 'index.php?s=$'.$rname['{modname}'].'&c=category&dir=$'.$rname['{pdirname}'].'&page=$'.$rname['{page}'];
                        } else {
                            // id模式
                            $rule = 'index.php?s=$'.$rname['{modname}'].'&c=category&id=$'.$rname['{id}'].'&page=$'.$rname['{page}'];
                        }
                        if (isset($write[$preg])) {
                            $error.= "<p>".$cname."与".$write[$preg]."规则存在冲突，需要手动写解析规则</p>";
                        } else {
                            $write[$preg] = $cname;
                            $code.= '<textarea class="form-control" rows="1">   "'.$preg.'" => "'.$rule.'",  //'.$cname.'（此规则由系统生成，不一定会准确，请开发者自行调整）</textarea>';
                        }
                    }
                }
                if ($value['list']) {
                    $rule = $value['list'];
                    $cname = "【".$r['name']."】模块栏目列表（{$rule}）";
                    list($preg, $rname) = $this->_rule_preg_value($rule);
                    if (!$preg || !$rname) {
                        $error.= "<p>".$cname."无法识别，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{modname}'])) {
                        $error.= "<p>".$cname."缺少{modname}标签，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{dirname}']) && !isset($rname['{id}']) && !isset($rname['{pdirname}'])) {
                        $error.= "<p>".$cname."缺少{dirname}或{id}或{pdirname}标签，需要手动写解析规则</p>";
                    } else {
                        if (isset($rname['{dirname}'])) {
                            // 目录格式
                            $rule = 'index.php?s=$'.$rname['{modname}'].'&c=category&dir=$'.$rname['{dirname}'];
                        } elseif (isset($rname['{pdirname}'])) {
                            // 层次目录格式
                            $rule = 'index.php?s=$'.$rname['{modname}'].'&c=category&dir=$'.$rname['{pdirname}'];
                        } else {
                            // id模式
                            $rule = 'index.php?s=$'.$rname['{modname}'].'&c=category&id=$'.$rname['{id}'];
                        }
                        if (isset($write[$preg])) {
                            $error.= "<p>".$cname."与".$write[$preg]."规则存在冲突，需要手动写解析规则</p>";
                        } else {
                            $write[$preg] = $cname;
                            $code.= '<textarea class="form-control" rows="1">    "'.$preg.'" => "'.$rule.'",  //'.$cname."（此规则由系统生成，不一定会准确，请开发者自行调整）</textarea>";
                        }
                    }
                }
                if ($value['show_page']) {
                    $rule = $value['show_page'];
                    $cname = "【".$r['name']."】模块内容页(分页)（{$rule}）";
                    list($preg, $rname) = $this->_rule_preg_value($rule);
                    if (!$preg || !$rname) {
                        $error.= "<p>".$cname."无法识别，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{modname}'])) {
                        $error.= "<p>".$cname."缺少{modname}标签，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{id}'])) {
                        $error.= "<p>".$cname."缺少{id}标签，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{page}'])) {
                        $error.= "<p>".$cname."缺少{page}标签，需要手动写解析规则</p>";
                    } else {
                        $rule = 'index.php?s=$'.$rname['{modname}'].'&c=show&id=$'.$rname['{id}'].'&page=$'.$rname['{page}'];
                        if (isset($write[$preg])) {
                            $error.= "<p>".$cname."与".$write[$preg]."规则存在冲突，需要手动写解析规则</p>";
                        } else {
                            $write[$preg] = $cname;
                            $code.= '<textarea class="form-control" rows="1">    "'.$preg.'" => "'.$rule.'",  //'.$cname."（此规则由系统生成，不一定会准确，请开发者自行调整）</textarea>";
                        }
                    }
                }
                if ($value['show']) {
                    $rule = $value['show'];
                    $cname = "【".$r['name']."】模块内容页（{$rule}）";
                    list($preg, $rname) = $this->_rule_preg_value($rule);
                    if (!$preg || !$rname) {
                        $error.= "<p>".$cname."无法识别，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{id}'])) {
                        $error.= "<p>".$cname."缺少{id}标签，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{modname}'])) {
                        $error.= "<p>".$cname."缺少{modname}标签，需要手动写解析规则</p>";
                    } else {
                        $rule = 'index.php?s=$'.$rname['{modname}'].'&c=show&id=$'.$rname['{id}'];
                        if (isset($write[$preg])) {
                            $error.= "<p>".$cname."与".$write[$preg]."规则存在冲突，需要手动写解析规则</p>";
                        } else {
                            $write[$preg] = $cname;
                            $code.= '<textarea class="form-control" rows="1">    "'.$preg.'" => "'.$rule.'",  //'.$cname."（此规则由系统生成，不一定会准确，请开发者自行调整）</textarea>";
                        }
                    }
                }
                if ($value['search_page']) {
                    $rule = $value['search_page'];
                    $cname = "【".$r['name']."】模块搜索页(分页)（{$rule}）";
                    list($preg, $rname) = $this->_rule_preg_value($rule);
                    if (!$preg || !$rname) {
                        $error.= "<p>".$cname."无法识别，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{modname}'])) {
                        $error.= "<p>".$cname."缺少{modname}标签，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{param}'])) {
                        $error.= "<p>".$cname."缺少{param}标签，需要手动写解析规则</p>";
                    } else {
                        $rule = 'index.php?s=$'.$rname['{modname}'].'&c=search&rewrite=$'.$rname['{param}'];
                        if (isset($write[$preg])) {
                            $error.= "<p>".$cname."与".$write[$preg]."规则存在冲突，需要手动写解析规则</p>";
                        } else {
                            $write[$preg] = $cname;
                            $code.= '<textarea class="form-control" rows="1">    "'.$preg.'" => "'.$rule.'",  //'.$cname."（此规则由系统生成，不一定会准确，请开发者自行调整）</textarea>";
                        }
                    }
                }
                if ($value['search']) {
                    $rule = $value['search'];
                    $cname = "【".$r['name']."】模块搜索页（{$rule}）";
                    list($preg, $rname) = $this->_rule_preg_value($rule);
                    if (!$preg || !$rname) {
                        $error.= "<p>".$cname."无法识别，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{modname}'])) {
                        $error.= "<p>".$cname."缺少{modname}标签，需要手动写解析规则</p>";
                    } else {
                        $rule = 'index.php?s=$'.$rname['{modname}'].'&c=search';
                        if (isset($write[$preg])) {
                            $error.= "<p>".$cname."与".$write[$preg]."规则存在冲突，需要手动写解析规则</p>";
                        } else {
                            $write[$preg] = $cname;
                            $code.= '<textarea class="form-control" rows="1">    "'.$preg.'" => "'.$rule.'",  //'.$cname."（此规则由系统生成，不一定会准确，请开发者自行调整）</textarea>";
                        }
                    }
                }

                $code.= PHP_EOL.'	// '.$r['name'].'---解析规则----结束'.PHP_EOL;
            } elseif ($r['type'] == 3 ) {
                // 共享栏目
                $code.= PHP_EOL.'	// '.$r['name'].'---解析规则----开始'.PHP_EOL;
                if ($value['list_page']) {
                    $rule = $value['list_page'];
                    $cname = "【".$r['name']."】模块栏目列表(分页)（{$rule}）";
                    list($preg, $rname) = $this->_rule_preg_value($rule);
                    if (!$preg || !$rname) {
                        $error.= "<p>".$cname."无法识别，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{page}'])) {
                        $error.= "<p>".$cname."缺少{page}标签，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{dirname}']) && !isset($rname['{id}']) && !isset($rname['{pdirname}'])) {
                        $error.= "<p>".$cname."缺少{dirname}或{id}或{pdirname}标签，需要手动写解析规则</p>";
                    } else {
                        if (isset($rname['{dirname}'])) {
                            // 目录格式
                            $rule = 'index.php?c=category&dir=$'.$rname['{dirname}'].'&page=$'.$rname['{page}'];
                        } elseif (isset($rname['{pdirname}'])) {
                            // 层次目录格式
                            $rule = 'index.php?c=category&dir=$'.$rname['{pdirname}'].'&page=$'.$rname['{page}'];
                        } else {
                            // id模式
                            $rule = 'index.php?c=category&id=$'.$rname['{id}'].'&page=$'.$rname['{page}'];
                        }
                        if (isset($write[$preg])) {
                            $error.= "<p>".$cname."与".$write[$preg]."规则存在冲突，需要手动写解析规则</p>";
                        } else {
                            $write[$preg] = $cname;
                            $code.= '<textarea class="form-control" rows="1">    "'.$preg.'" => "'.$rule.'",  //'.$cname."（此规则由系统生成，不一定会准确，请开发者自行调整）</textarea>";
                        }
                    }
                }
                if ($value['list']) {
                    $rule = $value['list'];
                    $cname = "【".$r['name']."】模块栏目列表（{$rule}）";
                    list($preg, $rname) = $this->_rule_preg_value($rule);
                    if (!$preg || !$rname) {
                        $error.= "<p>".$cname."无法识别，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{dirname}']) && !isset($rname['{id}']) && !isset($rname['{pdirname}'])) {
                        $error.= "<p>".$cname."缺少{dirname}或{id}或{pdirname}标签，需要手动写解析规则</p>";
                    } else {
                        if (isset($rname['{dirname}'])) {
                            // 目录格式
                            $rule = 'index.php?c=category&dir=$'.$rname['{dirname}'];
                        } elseif (isset($rname['{pdirname}'])) {
                            // 层次目录格式
                            $rule = 'index.php?c=category&dir=$'.$rname['{pdirname}'];
                        } else {
                            // id模式
                            $rule = 'index.php?c=category&id=$'.$rname['{id}'];
                        }
                        if (isset($write[$preg])) {
                            $error.= "<p>".$cname."与".$write[$preg]."规则存在冲突，需要手动写解析规则</p>";
                        } else {
                            $write[$preg] = $cname;
                            $code.= '<textarea class="form-control" rows="1">    "'.$preg.'" => "'.$rule.'",  //'.$cname."（此规则由系统生成，不一定会准确，请开发者自行调整）</textarea>";
                        }
                    }
                }
                if ($value['show_page']) {
                    $rule = $value['show_page'];
                    $cname = "【".$r['name']."】模块内容页(分页)（{$rule}）";
                    list($preg, $rname) = $this->_rule_preg_value($rule);
                    if (!$preg || !$rname) {
                        $error.= "<p>".$cname."无法识别，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{page}'])) {
                        $error.= "<p>".$cname."缺少{page}标签，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{id}'])) {
                        $error.= "<p>".$cname."缺少{id}标签，需要手动写解析规则</p>";
                    } else {
                        $rule = 'index.php?c=show&id=$'.$rname['{id}'].'&page=$'.$rname['{page}'];
                        if (isset($write[$preg])) {
                            $error.= "<p>".$cname."与".$write[$preg]."规则存在冲突，需要手动写解析规则</p>";
                        } else {
                            $write[$preg] = $cname;
                            $code.= '<textarea class="form-control" rows="1">    "'.$preg.'" => "'.$rule.'",  //'.$cname."（此规则由系统生成，不一定会准确，请开发者自行调整）</textarea>";
                        }
                    }
                }
                if ($value['show']) {
                    $rule = $value['show'];
                    $cname = "【".$r['name']."】模块内容页（{$rule}）";
                    list($preg, $rname) = $this->_rule_preg_value($rule);
                    if (!$preg || !$rname) {
                        $error.= "<p>".$cname."无法识别，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{id}'])) {
                        $error.= "<p>".$cname."无法识别，需要手动写解析规则</p>";
                    } else {
                        $rule = 'index.php?c=show&id=$'.$rname['{id}'];
                        if (isset($write[$preg])) {
                            $error.= "<p>".$cname."与".$write[$preg]."规则存在冲突，需要手动写解析规则</p>";
                        } else {
                            $write[$preg] = $cname;
                            $code.= '<textarea class="form-control" rows="1">    "'.$preg.'" => "'.$rule.'",  //'.$cname."（此规则由系统生成，不一定会准确，请开发者自行调整）</textarea>";
                        }
                    }
                }
                $code.= PHP_EOL.'	// '.$r['name'].'---解析规则----结束'.PHP_EOL;
            } elseif ($r['type'] == 2 ) {
                // 共享模块
                $code.= PHP_EOL.'	// '.$r['name'].'---解析规则----开始'.PHP_EOL;
                if ($value['search_page']) {
                    $rule = $value['search_page'];
                    $cname = "【".$r['name']."】模块搜索页(分页)（{$rule}）";
                    list($preg, $rname) = $this->_rule_preg_value($rule);
                    if (!$preg || !$rname) {
                        $error.= "<p>".$cname."无法识别，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{modname}'])) {
                        $error.= "<p>".$cname."缺少{modname}标签，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{param}'])) {
                        $error.= "<p>".$cname."缺少{param}标签，需要手动写解析规则</p>";
                    } else {
                        $rule = 'index.php?s=$'.$rname['{modname}'].'&c=search&rewrite=$'.$rname['{param}'];
                        if (isset($write[$preg])) {
                            $error.= "<p>".$cname."与".$write[$preg]."规则存在冲突，需要手动写解析规则</p>";
                        } else {
                            $write[$preg] = $cname;
                            $code.= '<textarea class="form-control" rows="1">    "'.$preg.'" => "'.$rule.'",  //'.$cname."（此规则由系统生成，不一定会准确，请开发者自行调整）</textarea>";
                        }
                    }
                }
                if ($value['search']) {
                    $rule = $value['search'];
                    $cname = "【".$r['name']."】模块搜索页（{$rule}）";
                    list($preg, $rname) = $this->_rule_preg_value($rule);
                    if (!$preg || !$rname) {
                        $error.= "<p>".$cname."无法识别，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{modname}'])) {
                        $error.= "<p>".$cname."缺少{modname}标签，需要手动写解析规则</p>";
                    } else {
                        $rule = 'index.php?s=$'.$rname['{modname}'].'&c=search';
                        if (isset($write[$preg])) {
                            $error.= "<p>".$cname."与".$write[$preg]."规则存在冲突，需要手动写解析规则</p>";
                        } else {
                            $write[$preg] = $cname;
                            $code.= '<textarea class="form-control" rows="1">    "'.$preg.'" => "'.$rule.'",  //'.$cname."（此规则由系统生成，不一定会准确，请开发者自行调整）</textarea>";
                        }
                    }
                }
                $code.= PHP_EOL.'	// '.$r['name'].'---解析规则----结束'.PHP_EOL;
            } elseif ($r['type'] == 4 ) {
                // 关键词库插件
                $code.= PHP_EOL.'	// '.$r['name'].'---解析规则----开始'.PHP_EOL;
                if ($value['tag']) {
                    $rule = $value['tag'];
                    $cname = "【".$r['name']."】TagURL（{$rule}）";
                    list($preg, $rname) = $this->_rule_preg_value($rule);
                    if (!$preg || !$rname) {
                        $error.= "<p>".$cname."无法识别，需要手动写解析规则</p>";
                    } elseif (!isset($rname['{tag}'])) {
                        $error.= "<p>".$cname."缺少{tag}标签，需要手动写解析规则</p>";
                    } else {
                        $rule = 'index.php?s=tag&name=$'.$rname['{tag}'];
                        if (isset($write[$preg])) {
                            $error.= "<p>".$cname."与".$write[$preg]."规则存在冲突，需要手动写解析规则</p>";
                        } else {
                            $write[$preg] = $cname;
                            $code.= '<textarea class="form-control" rows="1">    "'.$preg.'" => "'.$rule.'",  //'.$cname."（此规则由系统生成，不一定会准确，请开发者自行调整）</textarea>";
                        }
                    }
                }

                $code.= PHP_EOL.'	// '.$r['name'].'---解析规则----结束'.PHP_EOL;
            }
        }

        return dr_return_data(1, dr_lang('生成成功'), [
            'code' => nl2br($code),
            'error' => $error,
        ]);
    }


    // 正则解析
    private function _rule_preg_value($rule) {

        $rule = trim(trim($rule, '/'));

        if (preg_match_all('/\{(.*)\}/U', $rule, $match)) {

            $value = [];
            foreach ($match[0] as $k => $v) {
                $value[$v] = ($k + 1);
            }

            $preg = preg_replace(
                [
                    '#\{id\}#U',
                    '#\{uid\}#U',
                    '#\{mid\}#U',
                    '#\{fid\}#U',
                    '#\{page\}#U',

                    '#\{pdirname\}#Ui',
                    '#\{dirname\}#Ui',
                    '#\{opdirname\}#Ui',
                    '#\{otdirname\}#Ui',
                    '#\{modname\}#Ui',
                    '#\{name\}#Ui',

                    '#\{tag\}#U',
                    '#\{param\}#U',

                    '#\{y\}#U',
                    '#\{m\}#U',
                    '#\{d\}#U',

                    '#\{\.+}#U',
                    '#/#'
                ],
                [
                    '([0-9]+)',
                    '([0-9]+)',
                    '(\d+)',
                    '(\w+)',
                    '([0-9]+)',

                    '([\w\/]+)',
                    '([A-za-z0-9 \-\_]+)',
                    '([A-za-z0-9 \-\_]+)',
                    '([A-za-z0-9 \-\_]+)',
                    '([a-z]+)',
                    '([a-z]+)',

                    '(.+)',
                    '(.+)',

                    '([0-9]{4})',
                    '([0-9]{2})',
                    '([0-9]{2})',

                    '(.+)',
                    '\/'
                ],
                $rule
            );

            // 替换特殊的结果
            $preg = str_replace(
                ['(.+))}-', '.html'],
                ['(.+)-', '\.html'],
                $preg
            );

            return [$preg, $value];
        }

        return [$rule, []];
    }


}
