<?php namespace Phpcmf\Model\Module;


class Site extends \Phpcmf\Model {

    // 设置风格
    public function set_theme($name, $siteid) {

        $site = $this->table('site')->get($siteid);
        if (!$site) {
            return [];
        }

        $site['setting'] = dr_string2array($site['setting']);
        $site['setting']['config']['SITE_THEME'] = $name;

        $this->table('site')->update($siteid, [
            'setting' => dr_array2string($site['setting']),
        ]);
    }

    // 设置模板
    public function set_template($name, $siteid) {

        $site = $this->table('site')->get($siteid);
        if (!$site) {
            return [];
        }

        $site['setting'] = dr_string2array($site['setting']);
        $site['setting']['config']['SITE_TEMPLATE'] = $name;

        $this->table('site')->update($siteid, [
            'setting' => dr_array2string($site['setting']),
        ]);
    }

    // 获取网站配置
    public function config($siteid, $name = '', $data = []) {

        !$siteid && $siteid = SITE_ID;
        $site = $this->table('site')->get($siteid);
        if (!$site) {
            return [];
        }

        $site['setting'] = dr_string2array($site['setting']);
        $site['setting']['config']['SITE_NAME'] = $site['name'];
        $site['setting']['config']['SITE_DOMAIN'] = strtolower((string)$site['domain']);

        if ($name && $data) {
            // 更新数据
            if ($data['SITE_NAME']) {
                $site['name'] = $data['SITE_NAME'];
            }
            if ($data['SITE_DOMAIN']) {
                $site['domain'] = $data['SITE_DOMAIN'];
            }
            $site['setting'][$name] = $data;
            $this->table('site')->update($siteid, [
                'name' => $site['name'],
                'domain' => strtolower((string)$site['domain']),
                'setting' => dr_array2string($site['setting']),
            ]);
        }

        return $site['setting'];
    }

    // 存储网站配置
    public function save_config($siteid, $name, $data) {

        !$siteid && $siteid = SITE_ID;
        $site = $this->table('site')->get($siteid);
        if (!$site) {
            return [];
        }

        $site['setting'] = dr_string2array($site['setting']);
        $site['setting']['config']['SITE_NAME'] = $site['name'];
        $site['setting']['config']['SITE_DOMAIN'] = strtolower((string)$site['domain']);

        // 更新数据
        if ($data['SITE_NAME']) {
            $site['name'] = $data['SITE_NAME'];
        }
        if ($data['SITE_DOMAIN']) {
            $site['domain'] = $data['SITE_DOMAIN'];
        }
        $site['setting'][$name] = $data;
        $this->table('site')->update($siteid, [
            'name' => $site['name'],
            'domain' => strtolower((string)$site['domain']),
            'setting' => dr_array2string($site['setting']),
        ]);

        return $site['setting'];
    }

    // 设置网站单个配置
    public function config_value($siteid, $group, $value) {

        !$siteid && $siteid = SITE_ID;
        $site = $this->table('site')->get($siteid);
        if (!$site || !$value) {
            return;
        }

        $site['setting'] = dr_string2array($site['setting']);

        foreach ($value as $n => $v) {
            $site['setting'][$group][$n] = $v;
        }

        $this->table('site')->update($siteid, [
            'setting' => dr_array2string($site['setting']),
        ]);

        return;
    }

    // 新增
    public function create($data) {

        $save = [
            'name' => $data['name'],
            'domain' => (string)$data['domain'],
            'setting' => dr_array2string([
                'webpath' => $data['webpath'],
            ]),
            'disabled' => 0,
            'displayorder' => 0,
        ];
        if (defined('IS_INSTALL')) {
            $save['id'] = 1;
        }
        $this->db->table('site')->replace($save);

        $siteid = $this->db->insertID();

        if ($siteid == 1) {
            return $siteid; // 安装不执行后面操作
        }

        if (dr_is_app('sites')) {
            $obj = \Phpcmf\Service::M('sites', 'sites');
            if (method_exists($obj, 'create')) {
                $obj->create($siteid, $data);
            }
        }

        return $siteid;
    }

    // 变更主域名
    public function edit_domain($value) {

        $site = $this->config(1);
        $value = trim(strtolower($value), '/');
        $this->db->table('site')->where('id', 1)->update([
            'domain' => $value,
            'setting' => dr_array2string($site),
        ]);
        // 替换栏目编辑器域名
        $table = $this->dbprefix(SITE_ID.'_share_category');
        if ($this->is_table_exists($table)) {
            $this->db->query('UPDATE `'.$table.'` SET `content`=REPLACE(`content`, \''.$site['config']['SITE_DOMAIN'].'\', \''.$value.'\')');
        }
    }

    // 设置域名
    public function domain($value = []) {

        $data = [];
        $site = $this->config(SITE_ID);
        if ($value) {
            $site['webpath'] = $value['webpath'];
            $this->db->table('site')->where('id', SITE_ID)->update([
                'domain' => trim(strtolower($value['site_domain'] ? $value['site_domain'] : $site['domain']), '/'),
                'setting' => dr_array2string($site),
            ]);
        }

        $data['webpath'] = $site['webpath'];
        $data['site_domain'] = strtolower((string)$site['config']['SITE_DOMAIN']);

        // 识别手机域名
        if (isset($site['mobile']['mode']) && $site['mobile']['mode'] != -1) {
            if (!$site['mobile']['mode']) {
                $data['mobile_domain'] = $site['mobile']['domain'];
            } else {
                $data['mobile_domain'] = $site['config']['SITE_DOMAIN'].'/'.trim($site['mobile']['dirname'] ? $site['mobile']['dirname'] : 'mobile');
            }
        } else {
            $data['mobile_domain'] = $site['mobile']['domain'];
        }

        if ($site['client']) {
            foreach ($site['client'] as $c) {
                if ($c['name'] && $c['domain']) {
                    $data['client_'.$c['name']] = $c['domain'];
                }
            }
        }

        // 模块域名
        $my = [];
        if (IS_USE_MODULE) {
            list($my, $data) = \Phpcmf\Service::M('module', 'module')->domian($value, $my, $data);
        }

        return [$my, $data];
    }

    // 站点缓存缓存
    public function cache($siteid = null, $data = null, $module = null) {

        !$data && $data = $this->table('site')->where('disabled', 0)->order_by('displayorder ASC,id ASC')->getAll();
        $sso_domain = $client_domain = $webpath = $app_domain = $site_domain = $config = $cache = [];
        if ($data) {
            foreach ($data as $t) {
                if ($t['id'] > 1 && !dr_is_app('sites')) {
                    break;
                }

                $t['setting'] = dr_string2array($t['setting']);
                $mobile_dirname = 'mobile';

                // 识别手机域名
                if (isset($t['setting']['mobile']['mode']) && $t['setting']['mobile']['mode'] != -1) {
                    if (!$t['setting']['mobile']['mode']) {
                        $mobile_domain = (string)$t['setting']['mobile']['domain'];
                    } else {
                        $mobile_dirname = trim($t['setting']['mobile']['dirname'] ? $t['setting']['mobile']['dirname'] : 'mobile');
                        $mobile_domain = $t['domain'].'/'.$mobile_dirname;
                    }
                } else {
                    $mobile_domain = (string)$t['setting']['mobile']['domain'];
                }

                $config[$t['id']] = [
                    'SITE_NAME' => $t['name'],
                    'SITE_DOMAIN' => strtolower($t['domain']),
                    'SITE_LOGO' => $t['setting']['config']['logo'] ? dr_get_file($t['setting']['config']['logo']) : ROOT_THEME_PATH.'assets/logo-web.png',
                    'SITE_MOBILE' => $mobile_domain,
                    'SITE_MOBILE_DIR' => $mobile_dirname,
                    'SITE_AUTO' => (string)$t['setting']['mobile']['auto'],
                    'SITE_IS_MOBILE_HTML' => (string)$t['setting']['mobile']['tohtml'],
                    'SITE_MOBILE_NOT_PAD' => (string)$t['setting']['mobile']['not_pad'],
                    'SITE_CLOSE' => $t['setting']['config']['SITE_CLOSE'],
                    'SITE_THEME' => $t['setting']['config']['SITE_THEME'],
                    'SITE_TEMPLATE' => $t['setting']['config']['SITE_TEMPLATE'],
                    'SITE_REWRITE' => $t['setting']['seo']['SITE_REWRITE'],
                    'SITE_SEOJOIN' => $t['setting']['seo']['SITE_SEOJOIN'],
                    'SITE_LANGUAGE' => $t['setting']['config']['SITE_LANGUAGE'],
                    'SITE_TIMEZONE' => $t['setting']['config']['SITE_TIMEZONE'],
                    'SITE_TIME_FORMAT' => $t['setting']['config']['SITE_TIME_FORMAT'],
                    'SITE_INDEX_HTML' => (string)$t['setting']['config']['SITE_INDEX_HTML'],
                    'SITE_THUMB_WATERMARK' => (int)$t['setting']['watermark']['thumb'],
                ];
                unset($t['setting']['mobile']['auto'],
                    $t['setting']['mobile']['domain'],
                    $t['setting']['seo']['SITE_REWRITE'],
                    $t['setting']['seo']['SITE_SEOJOIN'],
                    $t['setting']['config']['SITE_THEME'],
                    $t['setting']['config']['SITE_TEMPLATE'],
                    $t['setting']['config']['SITE_LANGUAGE'],
                    $t['setting']['config']['SITE_TIME_FORMAT'],
                    $t['setting']['config']['SITE_NAME'],
                    $t['setting']['config']['SITE_TIMEZONE'],
                    $t['setting']['config']['SITE_DOMAIN'],
                    $t['setting']['config']['SITE_CLOSE']
                );
                // 本站的全部域名归属
                $site_domain[$t['domain']] = $t['id'];
                $sso_domain[] = $t['domain'];
                if ($config[$t['id']]['SITE_MOBILE']) {
                    $site_domain[$config[$t['id']]['SITE_MOBILE']] = $t['id'];
                    $client_domain[$t['domain']] = $config[$t['id']]['SITE_MOBILE'];
                    $sso_domain[] = $config[$t['id']]['SITE_MOBILE'];
                }
                // 自定义终端
                if ($t['setting']['client']) {
                    $_save = [];
                    foreach ($t['setting']['client'] as $c) {
                        $site_domain[$c['domain']] = $t['id'];
                        $_save[$c['name']] = $sso_domain[] = $c['domain'];
                    }
                    $t['setting']['client'] = $_save;
                }

                // 网站路径
                $webpath[$t['id']] = [
                    'site' => ROOTPATH,
                ];
                if ($t['id'] > 1 && $t['setting']['webpath']) {
                    $webpath[$t['id']]['site'] = dr_get_dir_path($t['setting']['webpath']);
                    if (!is_dir($webpath[$t['id']]['site'])) {
                        log_message('error', '多站点：站点【'.$t['id'].'】目录【'.$webpath[$t['id']]['site'].'】不存在');
                        //continue;
                    }
                }

                // 自定义站点字段
                $field = \Phpcmf\Service::M('field')->get_mysite_field($t['id']);
                if ($field && $t['setting']['param']) {
                    $t['setting']['param'] = \Phpcmf\Service::L('Field')->app('')->format_value($field, $t['setting']['param'], 1);
                }

                // 删除首页静态文件
                //unlink($webpath[$t['id']]['site'].'index.html');
                //unlink($webpath[$t['id']]['site'].$mobile_dirname.'/index.html');

                $cache[$t['id']] = $t['setting'];
            }

            list($webpath, $site_domain, $app_domain, $sso_domain, $client_domain) = \Phpcmf\Service::M('module', 'module')->sync_site_cache(
                $module,
                $webpath,
                $site_domain,
                $app_domain,
                $sso_domain,
                $client_domain,
                [],
                $data
            );
        }

        \Phpcmf\Service::L('Cache')->set_file('site', $cache);
        \Phpcmf\Service::L('Config')->file(WRITEPATH.'config/site.php', '站点配置文件', 32)->to_require($config);
        \Phpcmf\Service::L('Config')->file(WRITEPATH.'config/domain_sso.php', '同步域名配置文件', 32)->to_require_one($sso_domain);
        \Phpcmf\Service::L('Config')->file(WRITEPATH.'config/domain_app.php', '网站域名配置文件', 32)->to_require_one($app_domain);
        \Phpcmf\Service::L('Config')->file(WRITEPATH.'config/domain_site.php', '站点域名配置文件', 32)->to_require_one($site_domain);
        \Phpcmf\Service::L('Config')->file(WRITEPATH.'config/domain_client.php', '客户端域名配置文件', 32)->to_require_one($client_domain);
        \Phpcmf\Service::L('Config')->file(WRITEPATH.'config/webpath.php', '入口文件目录配置文件', 32)->to_require($webpath);
    }


    // 更新全部网站缓存
    public function update_site_cache() {

        $site_cache = $this->table('site')->where('disabled', 0)->order_by('displayorder ASC,id ASC')->getAll();
        $module_cache = $this->table('module')->order_by('displayorder ASC,id ASC')->getAll();

        // 按网站更新的缓存
        $cache = [];

        if (is_file(MYPATH.'/Config/Cache.php')) {
            $_cache = require MYPATH.'/Config/Cache.php';
            $_cache && $cache = dr_array22array($cache, $_cache);
        }

        // 执行插件自己的缓存程序
        $local = \Phpcmf\Service::Apps();
        $app_cache = [];
        foreach ($local as $dir => $path) {
            if (is_file($path.'install.lock')
                && is_file($path.'Config/Cache.php')) {
                $_cache = require $path.'Config/Cache.php';
                $_cache && $app_cache[$dir] = $_cache;
            }
        }

        $page = intval($_GET['page']);
        $tpage = dr_count($site_cache);

        if (!$page) {
            // 全局系统缓存
            dr_dir_delete(WRITEPATH.'data');
            \Phpcmf\Service::M('site')->cache(0, $site_cache, $module_cache);
            foreach (['auth', 'email', 'member', 'attachment', 'system'] as $m) {
                \Phpcmf\Service::M($m)->cache();
            }
            \Phpcmf\Service::C()->_json(1, dr_lang('正在缓存数据'), 1);
        }

        $key = $page - 1;
        if (!isset($site_cache[$key])) {
            \Phpcmf\Service::M('menu')->cache();
            \Phpcmf\Service::M('cache')->update_data_cache();
            \Phpcmf\Service::C()->_json(1, dr_lang('更新完成'));
        }

        foreach ([ $site_cache[$key] ] as $t) {

            \Phpcmf\Service::M('table')->cache($t['id'], $module_cache);
            \Phpcmf\Service::M('module')->cache($t['id'], $module_cache);

            foreach ($cache as $m => $namespace) {
                \Phpcmf\Service::M($m, $namespace)->cache($t['id']);
            }

            // 插件缓存
            $apps = [];
            if ($app_cache) {
                foreach ($app_cache as $namespace => $c) {
                    \Phpcmf\Service::C()->init_file($namespace);
                    foreach ($c as $i => $apt) {
                        $class = is_numeric($i) ? $apt : $i;
                        $apps[] = '['.$namespace.'-'.$class.']';
                        \Phpcmf\Service::M($class, $namespace)->cache($t['id']);
                    }
                }
            }

            // 记录日志
            CI_DEBUG && \Phpcmf\Service::L('input')->system_log('更新[网站#'.$t['id'].']缓存： '.implode(' - ', $apps));
        }

        \Phpcmf\Service::C()->_json(1, dr_lang('正在更新中（%s/%s）', $page+1, $tpage), $page + 1);
    }

    // 更新当前网站缓存
    public function update_cache() {

        $site_cache = $this->table('site')->where('disabled', 0)->order_by('displayorder ASC,id ASC')->getAll();
        $module_cache = $this->table('module')->order_by('displayorder ASC,id ASC')->getAll();
        \Phpcmf\Service::M('site')->cache(0, $site_cache, $module_cache);

        // 全局缓存
        foreach (['auth', 'email', 'member', 'attachment', 'system'] as $m) {
            \Phpcmf\Service::M($m)->cache();
        }

        // 按网站更新的缓存
        $cache = [];

        if (is_file(MYPATH.'/Config/Cache.php')) {
            $_cache = require MYPATH.'/Config/Cache.php';
            $_cache && $cache = dr_array22array($cache, $_cache);
        }

        // 执行插件自己的缓存程序
        $local = \Phpcmf\Service::Apps();
        $app_cache = [];
        foreach ($local as $dir => $path) {
            if (is_file($path.'install.lock')
                && is_file($path.'Config/Cache.php')) {
                $_cache = require $path.'Config/Cache.php';
                $_cache && $app_cache[$dir] = $_cache;
            }
        }

        foreach ($site_cache as $t) {

            if (!in_array($t['id'], [SITE_ID, 1])) {
                continue;
            }

            \Phpcmf\Service::M('table')->cache($t['id'], $module_cache);
            \Phpcmf\Service::M('module')->cache($t['id'], $module_cache);

            if ($cache) {
                foreach ($cache as $m => $namespace) {
                    \Phpcmf\Service::M($m, $namespace)->cache($t['id']);
                }
            }

            // 插件缓存
            $apps = [];
            if ($app_cache) {
                foreach ($app_cache as $namespace => $c) {
                    \Phpcmf\Service::C()->init_file($namespace);
                    foreach ($c as $i => $apt) {
                        $class = is_numeric($i) ? $apt : $i;
                        $apps[] = '['.$namespace.'-'.$class.']';
                        \Phpcmf\Service::M($class, $namespace)->cache($t['id']);
                    }
                }
            }

            // 记录日志
            CI_DEBUG && \Phpcmf\Service::L('input')->system_log('更新[网站#'.$t['id'].']缓存： '.implode(' - ', $apps));
        }

        \Phpcmf\Service::M('menu')->cache();

    }


    // 重建索引
    public function update_search_index() {

        $site_cache = $this->table('site')->where('disabled', 0)->getAll();
        $module_cache = $this->table('module')->getAll();
        if (!$module_cache) {
            return;
        }

        foreach ($site_cache as $t) {
            foreach ($module_cache as $m ) {
                $table = dr_module_table_prefix($m['dirname'], $t['id']);
                // 判断是否存在表
                if (!$this->db->tableExists($table)) {
                    continue;
                }
                $this->db->table($table.'_search')->truncate();
            }
        }
    }

    public function update_site_sync($t) {
        $t['setting'] = dr_string2array($t['setting']);
        if ($t['id'] > 1 && $t['setting']['webpath']) {
            $rt = $this->update_webpath('Web', $t['setting']['webpath'], [
                'SITE_ID' => $t['id'],
                'FIX_WEB_DIR' => strpos($t['setting']['webpath'], '/') === false && strpos($t['domain'], $t['setting']['webpath']) !== false ? $t['setting']['webpath'] : '',
                'MOBILE_DIR' => $t['setting']['mobile']['mode'] == 1 ? $t['setting']['mobile']['dirname'] : '',
            ]);
            if ($rt) {
                $this->_error_msg('网站['.$t['domain'].']: '.$rt);
            }
            $path = rtrim($t['setting']['webpath'], '/').'/';
        } else {
            $path = ROOTPATH;
        }
        if ($t['setting']['client']) {
            foreach ($t['setting']['client'] as $c) {
                if ($c['name'] && $c['domain']) {
                    $rt = $this->update_webpath('Client', $path.$c['name'].'/', [
                        'CLIENT' => $c['name'],
                        'SITE_ID' => $t['id'],
                        'FIX_WEB_DIR' => $c['domain'] == $t['domain'].'/'.$c['name'] ? $c['name'] : '',
                        'SITE_FIX_WEB_DIR' => $t['id'] > 1 && $t['setting']['webpath'] && strpos($t['setting']['webpath'], '/') === false && strpos($t['domain'], $t['setting']['webpath']) !== false ? $t['setting']['webpath'] : '',
                    ]);
                    if ($rt) {
                        $this->_error_msg('网站['.$t['domain'].']的终端['.$c['name'].']: '.$rt);
                    }
                }
            }
        }
    }


    // 重建子站配置文件
    public function update_site_config() {

        $page = intval($_GET['page']);
        if (!$page) {
            $site_cache = $this->table('site')->where('disabled', 0)->getAll();
            foreach ($site_cache as $t) {
                $this->update_site_sync($t);
            }
            \Phpcmf\Service::L('cache')->set_auth_data('update_site_config', $this->table('module')->where('share', 0)->getAll());
            \Phpcmf\Service::C()->_json(1, dr_lang('正在准备更新'), 1);
        }

        $module = \Phpcmf\Service::L('cache')->get_auth_data('update_site_config');
        if (!$module) {
            \Phpcmf\Service::C()->_json(1, dr_lang('无可用更新'), 0);
        }

        $key = $page - 1;
        if (!isset($module[$key])) {
            \Phpcmf\Service::C()->_json(1, dr_lang('更新完成'), 0);
        }

        \Phpcmf\Service::M('module', 'module')->update_site_config($module[$key]);

        \Phpcmf\Service::C()->_json(1, dr_lang('正在更新中（%s）', $page), $page + 1);
    }

    // 生成目录式手机目录
    public function update_mobile_webpath($path, $dirname) {

        foreach (['api.php', 'index.php'] as $file) {
            if (is_file(IS_USE_MODULE.'Temps/Web/mobile/'.$file)) {
                $dst = $path.$dirname.'/'.$file;
                dr_mkdirs(dirname($dst));
                $size = file_put_contents($dst, str_replace([
                    '{FIX_WEB_DIR}'
                ], [
                    (defined('FIX_WEB_DIR') && FIX_WEB_DIR ? FIX_WEB_DIR.'/' : '').$dirname
                ], file_get_contents(IS_USE_MODULE.'Temps/Web/mobile/'.$file)));
                if (!$size) {
                    return '文件['.$dst.']无法写入';
                }
            }
        }

        return;
    }

    // 更新网站
    public function update_webpath($name, $path, $value, $root = IS_USE_MODULE.'Temps/') {

        if (!$path) {
            return '目录为空';
        } elseif (strpos($path, ' ') === 0) {
            return '不能用空格开头';
        }

        $path = dr_get_dir_path($path);
        if (!$path) {
            return '目录为空';
        }

        dr_mkdirs($path);
        if (!is_dir($path)) {
            return '目录['.$path.']不存在';
        }

        // 创建入口文件
        //(defined('FIX_WEB_DIR') && FIX_WEB_DIR ? FIX_WEB_DIR.'/' : '').

        foreach ([
                     'admin.php',
                     'index.php',
                     'api.php',
                     'mobile/api.php',
                     'mobile/index.php',
                 ] as $file) {
            if (is_file($root.$name.'/'.$file)) {
                if ($file == 'admin.php') {
                    $dst = $path.(SELF == 'index.php' ? 'admin.php' : SELF);
                } else {
                    $dst = $path.$file;
                }
                $fix_web_dir = isset($value['FIX_WEB_DIR']) && $value['FIX_WEB_DIR'] ? $value['FIX_WEB_DIR'] : '';
                if (isset($value['SITE_ID']) && $value['SITE_ID'] > 1) {
                    if (strpos($file, 'mobile') !== false
                        && isset($value['MOBILE_DIR']) && $value['MOBILE_DIR']) {
                        $fix_web_dir.= '/'.$value['MOBILE_DIR'];
                    } elseif ($fix_web_dir) {
                        // 移动端加二级
                        if (strpos($file, 'mobile/') !== false) {
                            $fix_web_dir.= '/mobile';
                        }
                        // 终端加二级
                        if ( $name == 'Client') {
                            $fix_web_dir= (isset($value['SITE_FIX_WEB_DIR']) && $value['SITE_FIX_WEB_DIR'] ? $value['SITE_FIX_WEB_DIR'].'/' : '').$fix_web_dir;
                        }
                    }
                }
                dr_mkdirs(dirname($dst));
                $size = file_put_contents($dst, str_replace([
                    '{CLIENT}',
                    '{ROOTPATH}',
                    '{MOD_DIR}',
                    '{SITE_ID}',
                    '{FIX_WEB_DIR}'
                ], [
                    $value['CLIENT'],
                    ROOTPATH,
                    $value['MOD_DIR'],
                    $value['SITE_ID'],
                    trim($fix_web_dir, '/')
                ], file_get_contents($root.$name.'/'.$file)));
                if (!$size) {
                    return '文件['.$dst.']无法写入';
                }
            }
        }

        // 复制百度编辑器到当前目录
        //$this->cp_ueditor_file($path);

        // 复制百度编辑器到移动端网站
        //if (is_dir($path.'mobile')) {
        //$this->cp_ueditor_file($path.'mobile/');
        //}

        return '';
    }

    // 错误输出
    public function _error_msg($msg) {
        echo dr_array2string(dr_return_data(0, $msg));exit;
    }
}