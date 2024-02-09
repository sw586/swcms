<?php namespace Phpcmf\Model\Module;

class Module extends \Phpcmf\Model {

    protected $cat_share;
    protected $cat_share_lock;

    // 全部安装的模块
    public function All($is_file_name = 0) {
        $list = $this->table('module')->order_by('displayorder ASC,id ASC')->getAll();
        if ($list && $is_file_name) {
            foreach ($list as $i => $t) {
                if (is_file(dr_get_app_dir($t['dirname']).'Config/App.php')) {
                    $cfg = require dr_get_app_dir($t['dirname']).'Config/App.php';
                    if ($cfg['type'] == 'module' || $cfg['ftype'] == 'module') {
                        $list[$i]['name'] = $cfg['name'];
                    }
                }
            }
        }
        return $list;
    }

    // 权限划分操作权限
    public function module_auth() {

        $module = \Phpcmf\Service::M()->db->table('module')->get()->getResultArray();
        $module_auth = [];
        if ($module) {
            foreach ($module as $t) {
                $mdir = $t['dirname'];
                $path = dr_get_app_dir($mdir);
                if (!is_file($path.'Config/App.php')) {
                    continue;
                }
                $config = require $path.'Config/App.php';
                $module_auth[$mdir] = [
                    'name' => dr_lang($config['name']),
                    'auth' => [],
                ];
                if (!$t['share']) {
                    $module_auth[$mdir]['auth'][$mdir.'/category/'] = dr_lang('栏目');
                }
                if ($config['system']) {
                    // 内容模块
                    $module_auth[$mdir]['auth'][$mdir.'/home/'] = dr_lang('内容');
                    $module_auth[$mdir]['auth'][$mdir.'/draft/'] = dr_lang('草稿箱');
                    $module_auth[$mdir]['auth'][$mdir.'/recycle/'] = dr_lang('回收站');
                    $module_auth[$mdir]['auth'][$mdir.'/time/'] = dr_lang('定时发布');
                    $module_auth[$mdir]['auth'][$mdir.'/verify/'] = dr_lang('待审核');
                } else {
                    // 自定义模块
                }
                if (dr_is_app('comment')) {
                    $module_auth[$mdir]['auth'][$mdir.'/comment/'] = dr_lang('内容评论');
                }
                $mform = \Phpcmf\Service::M()->is_table_exists('module_form') ? \Phpcmf\Service::M()->db->table('module_form')->where('module', $mdir)->get()->getResultArray() : [];
                if ($mform) {
                    foreach ($mform as $c) {
                        $module_auth[$mdir]['auth'][$mdir.'/'.$c['table'].'/'] = dr_lang($c['name']);
                    }
                }
            }
        }

        return $module_auth;
    }

    // 存储配置信息
    public function config($data, $post, $value = []) {

        $update = $post ? [ 'setting' => $post['setting'] ] : [ 'setting' => $data['setting'] ];

        // 推荐位更新
        if ($value['flag']) {
            $update['setting']['flag'] = [];
            foreach ($value['flag'] as $i => $t) {
                $t['name'] && $update['setting']['flag'][$i] = $t;
            }
        } else {
            $update['setting']['flag'] = $data['setting']['flag'];
        }

        // 这里不更新站点
        $update['setting']['param'] = $data['setting']['param'];
        $update['setting']['search'] = $data['setting']['search'];

        // 站点更新
        if ($value['site']) {
            $site = $data['site'];
            $value['site']['use'] = 1;
            $site[SITE_ID] = $value['site'];
            $update['site'] = dr_array2string($site);
        }

        // 排列table字段顺序
        $update['setting']['list_field'] = dr_list_field_order($update['setting']['list_field']);

        $update['setting'] = dr_array2string($update['setting']);

        // 更新表
        return $this->table('module')->update(intval($data['id']), $update);
    }

    // 获取归属于本模块的栏目关系
    protected function _get_my_category($mdir, $CAT) {

        foreach ($CAT as $i => $t) {
            if (!$t['child'] && $t['tid'] == 1 && $t['mid'] != $mdir) {
                unset($CAT[$i]);
            }
            // 验证mid
            if ($CAT[$t['id']]['childids']) {
                $mid = [];
                $catids = explode(',', $CAT[$t['id']]['childids']);
                foreach ($catids as $c_catid) {
                    if ($CAT[$c_catid]['mid']) {
                        $mid[$CAT[$c_catid]['mid']] = 1;
                    }
                }
                if (!$mid) {
                    unset($CAT[$t['id']]);
                } elseif (!isset($mid[$mdir])) {
                    unset($CAT[$t['id']]);
                }
            }
        }

        return $CAT;
    }

    // 安装模块
    public function install($dir, $config = [], $is_app = 0, $is_share = 0) {

        $dir = strtolower($dir);
        $mpath = dr_get_app_dir($dir);
        $spath = dr_get_app_dir('module');
        if (!$config) {
            if (!is_file($mpath.'Config/App.php')) {
                return dr_return_data(0, dr_lang('模块配置文件不存在'));
            }
            $config = require $mpath.'Config/App.php';
        }

        // 安装前的判断
        if (is_file($mpath.'Config/Before.php')) {
            $rt = require $mpath.'Config/Before.php';
            if (!$rt['code']) {
                return dr_return_data(0, $rt['msg']);
            }
        }

        $table = $this->dbprefix(dr_module_table_prefix($dir)); // 当前表前缀
        $system_table = require $spath.'Config/SysTable.php';
        if (!$system_table ) {
            return dr_return_data(0, dr_lang('系统表配置文件不存在'));
        }

        // 判断是否强制独立模块或共享模块
        if ($is_share) {
            $config['share'] = $is_share == 1 ? 1 : 0; // 强制共享安装
        } elseif (isset($config['mtype']) && $config['mtype']) {
            $config['share'] = $config['mtype'] == 2 ? 0 : 1;
        }

        // 模块内容表结构和字段结构
        if (is_file($mpath.'Config/Content.php')) {
            $content_table = require $mpath.'Config/Content.php';
        } else {
            $content_table = require $spath.'Config/Content.php';
        }

        $module = $this->db->table('module')->where('dirname', $dir)->get()->getRowArray();
        if (!$module) {
            if (isset($config['ftype']) && $config['ftype'] == 'module' && $is_app == 0) {
                // 首次安装模块时，验证应用模块
                return dr_return_data(0, dr_lang('此模块属于应用类型，请到[本地应用]中去安装'));
            }
            $setting = '';
            if (is_file($mpath.'Config/Setting.php')) {
                $setting = require $mpath.'Config/Setting.php';
                if (is_array($setting)) {
                    $setting = dr_array2string($setting);
                }
            }
            if (!$setting) {
                $setting = '{"order":"updatetime DESC","verify_msg":"","delete_msg":"","list_field":{"title":{"use":"1","order":"1","name":"主题","width":"","func":"title"},"catid":{"use":"1","order":"2","name":"栏目","width":"130","func":"catid"},"author":{"use":"1","order":"3","name":"笔名","width":"120","func":"author"},"updatetime":{"use":"1","order":"4","name":"更新时间","width":"160","func":"datetime"}},"comment_list_field":{"content":{"use":"1","order":"1","name":"评论","width":"","func":"comment"},"author":{"use":"1","order":"3","name":"作者","width":"100","func":"author"},"inputtime":{"use":"1","order":"4","name":"评论时间","width":"160","func":"datetime"}},"flag":null,"param":null,"search":{"use":"1","field":"title,keywords","total":"500","length":"4","param_join":"-","param_rule":"0","param_field":"","param_join_field":["","","","","","",""],"param_join_default_value":"0"}}';
            }
            $module = [
                'site' => dr_array2string([
                    SITE_ID => [
                        'html' => 0,
                        'theme' => 'default',
                        'domain' => '',
                        'template' => 'default',
                    ]
                ]),
                'share' => intval($config['share']),
                'dirname' => $dir,
                'setting' => $setting,
                'comment' => '',
                'disabled' => 0,
                'displayorder' => 0,
            ];
            $rt = $this->table('module')->insert($module);
            if (!$rt['code']) {
                return dr_return_data(0, $rt['msg']);
            }
            $module['id'] = $rt['code'];
            $module['site'] = dr_string2array($module['site']);
        } else {
            $module['site'] = dr_string2array($module['site']);
            $module['site'][SITE_ID] = [
                'html' => 0,
                'theme' => 'default',
                'domain' => '',
                'template' => 'default',
            ];
            $this->table('module')->update($module['id'], [
                'site' => dr_array2string($module['site'])
            ]);
        }

        $siteid = 1;
        if (dr_count($module['site']) > 1) {
            // 多站点的情况下作为站点安装
            foreach ($module['site'] as $sid => $t) {
                if ($sid != SITE_ID) {
                    $siteid = $sid;
                    break;
                }
            }
            // 多站点时的复制站点表$siteid
        } else {
            // 创建内容表字段
            foreach ([1, 0] as $is_main) {
                $t = $content_table['field'][$is_main];
                if ($t) {
                    foreach ($t as $field) {
                        $this->_add_field($field, $is_main, $module['id'], 'module');
                    }
                }
            }
        }

        $system_table[''] = $content_table['table'][1];
        $system_table['_data_0'] = $content_table['table'][0];
        // 创建系统表
        foreach ($system_table as $name => $sql) {
            if (dr_count($module['site']) > 1) {
                // 表示存在多个站
                foreach ($module['site'] as $sid => $t) {
                    $root = dr_module_table_prefix($dir, $sid).$name;
                    if ($this->is_table_exists($root)) {
                        // 表示已经在其他站创建过了,我们就复制它以前创建的表结构
                        $sql = $this->db->query("SHOW CREATE TABLE `".$this->dbprefix($root)."`")->getRowArray();
                        $sql = str_replace(
                            array($sql['Table'], 'CREATE TABLE'),
                            array('{tablename}', 'CREATE TABLE IF NOT EXISTS'),
                            $sql['Create Table']
                        );
                        break;
                    }
                }
            }
            $this->db->simpleQuery(str_replace('{tablename}', $table.$name, dr_format_create_sql($sql)));
        }

        // 创建相关栏目表字段
        if (isset($config['scategory']) && $config['scategory']) {
            if (!\Phpcmf\Service::M()->db->fieldExists('tid', $table.'_category')) {
                \Phpcmf\Service::M()->query('ALTER TABLE `'.$table.'_category` ADD `tid` tinyint(1) NOT NULL COMMENT \'栏目类型，0单页，1模块，2外链\'');
            }
            if (!\Phpcmf\Service::M()->db->fieldExists('content', $table.'_category')) {
                \Phpcmf\Service::M()->query('ALTER TABLE `'.$table.'_category` ADD `content` mediumtext NOT NULL COMMENT \'单页内容\'');
            }
        }
        // 第一次安装模块
        // system = 2 2菜单不出现在内容下，由开发者自定义
        if ($config['system'] == 2 && dr_count($module['site']) == 1 && $is_app == 0 && is_file($mpath.'Config/Menu.php')) {
            \Phpcmf\Service::M('Menu')->add_app($dir);
        }

        // =========== 这里是公共部分 ===========

        if (dr_is_app('mform')) {
            \Phpcmf\Service::M('mform', 'mform')->link_install($module, $mpath, $dir, $table);
        }

        // 模块安装时执行的联动
        \Phpcmf\Hooks::trigger('module_install_data', [$module, $mpath, $dir, $table]);

        if (is_file($mpath.'Config/Install.php')) {
            require $mpath.'Config/Install.php';
        }

        // 首次安装模块执行它
        if (dr_count($module['site']) == 1) {
            // 执行自定义sql
            if (is_file($mpath.'Config/Install.sql')) {
                $sql = file_get_contents($mpath.'Config/Install.sql');
                $sql && \Phpcmf\Service::M('table')->_query(
                    $sql,
                    [
                        [
                            '{moduleid}',
                            '{dbprefix}',
                            '{tablename}',
                            '{dirname}',
                            '{siteid}'
                        ],
                        [
                            $module['id'],
                            $this->dbprefix(),
                            $table,
                            $dir,
                            $siteid
                        ],
                    ]
                );
            }
        }

        // 执行站点sql语句
        if (is_file($mpath.'Config/Install_site.sql')) {
            $sql = file_get_contents($mpath.'Config/Install_site.sql');
            $rt = $this->query_all(str_replace('{dbprefix}',  $this->dbprefix(dr_site_table_prefix('')), $sql));
            if ($rt) {
                return dr_return_data(0, $rt);
            }
        }

        $module['url'] = dr_url('module/module/index');
        return dr_return_data(1, dr_lang('操作成功，请刷新后台页面'), $module);
    }

    // 卸载模块
    public function uninstall($dir, $config = [], $is_app = 0) {

        $module = $this->db->table('module')->where('dirname', $dir)->get()->getRowArray();
        if (!$module) {
            return dr_return_data(0, dr_lang('模块尚未安装'));
        }

        $mpath = dr_get_app_dir($dir);
        $table = $this->dbprefix(dr_module_table_prefix($dir)); // 当前表前缀
        $system_table = require IS_USE_MODULE.'Config/SysTable.php';

        $site = dr_string2array($module['site']);
        if (count($site) == 1 && isset($config['ftype']) && $config['ftype'] == 'module' && $is_app == 0) {
            // 只有一个站点时，卸载需要 验证应用模块
            return dr_return_data(0, dr_lang('此模块属于应用类型，请到[本地应用]中去卸载'));
        }

        if (isset($site[SITE_ID]) && $site[SITE_ID]) {
            // 删除当前站点中的全部模块表
            if (IS_USE_MEMBER) {
                $row = $this->db->table('member_setting')->where('name', 'auth_module')->get()->getRowArray();
                $auth_module = dr_string2array($row['value']);
                if (isset($auth_module[SITE_ID][$dir])) {
                    unset($auth_module[SITE_ID][$dir]);
                    $this->db->table('member_setting')->replace([
                        'name' => 'auth_module',
                        'value' => dr_array2string($auth_module)
                    ]);
                }
            }
            // 系统模块
            // 删除系统表
            foreach ($system_table as $name => $sql) {
                $this->db->simpleQuery('DROP TABLE IF EXISTS `'.$table.$name.'`');
            }
            // 删除主表
            $this->db->simpleQuery('DROP TABLE IF EXISTS `'.$table.'`');
            // 删除全部子表
            for ($i = 0; $i < 200; $i ++) {
                if (!$this->db->query("SHOW TABLES LIKE '".$table.'_data_'.$i."'")->getRowArray()) {
                    break;
                }
                $this->db->query('DROP TABLE IF EXISTS '.$table.'_data_'.$i);
            }

            if (dr_is_app('mform')) {
                \Phpcmf\Service::M('mform', 'mform')->link_uninstall($table, $dir);
            }

            // 模块卸载时执行的联动
            \Phpcmf\Hooks::trigger('module_uninstall_data', [$module, $mpath, $dir, $table]);

            // 执行模块自己的卸载程序
            if (is_file($mpath.'Config/Uninstall.php')) {
                require $mpath.'Config/Uninstall.php';
            }

            // 执行自定义sql
            if (is_file($mpath.'Config/Uninstall.sql')) {
                $sql = file_get_contents($mpath.'Config/Uninstall.sql');
                $sql && \Phpcmf\Service::M('table')->_query(
                    $sql,
                    [
                        [
                            '{moduleid}',
                            '{dbprefix}',
                            '{tablename}',
                            '{dirname}',
                            '{siteid}'
                        ],
                        [
                            $module['id'],
                            $this->dbprefix(),
                            $table,
                            $dir,
                            SITE_ID
                        ],
                    ]
                );
            }

            // 执行站点sql语句
            if (is_file($mpath.'Config/Uninstall_site.sql')) {
                $sql = file_get_contents($mpath.'Config/Uninstall_site.sql');
                foreach ($this->site as $siteid) {
                    $rt = $this->query_all(str_replace('{dbprefix}',  $this->dbprefix($siteid.'_'), $sql));
                    if ($rt) {
                        return dr_return_data(0, $rt);
                    }
                }
            }

            // 删除栏目模型字段
            $this->db->table('field')->where('relatedname', $dir.'-'.SITE_ID)->delete();
            // 删除缓存
            \Phpcmf\Service::L('cache')->clear('module-'.$siteid.'-'.$dir);
            unset($site[SITE_ID]);
        }

        if (empty($site)) {
            // 没有站点了就删除这条模块记录
            $this->table('module')->delete($module['id']);
            // 删除字段
            $this->db->table('field')->where('relatedname', $dir.'-1')->delete();
            $this->db->table('field')->where('relatedname', 'category-'.$dir)->delete();
            $this->db->table('field')->where('relatedid', $module['id'])->where('relatedname', 'module')->delete();
            // 删除菜单
            $this->db->table('admin_menu')->like('mark', 'module-'.$dir)->delete();
            IS_USE_MEMBER && $this->db->table('member_menu')->like('mark', 'module-'.$dir)->delete();
            // 删除自定义菜单
            \Phpcmf\Service::M('Menu')->delete_app($dir);
            // 删除自定义表单
            if (dr_is_app('mform')) {
                \Phpcmf\Service::M('mform', 'mform')->link_delete($module, $dir);
            }
        } else {
            // 删除当前站点配置
            $this->table('module')->update($module['id'], ['site' => dr_array2string($site)]);
        }

        return dr_return_data(1, dr_lang('卸载成功'));
    }

    /**
     * 字段入库
     * @return	bool
     */
    public function _add_field($field, $ismain, $rid, $rname) {

        if ($this->db->table('field')
            ->where('fieldname', $field['fieldname'])
            ->where('relatedid', $rid)
            ->where('relatedname', $rname)->countAllResults()) {
            return;
        }

        $this->db->table('field')->insert(array(
            'name' => (string)($field['name'] ? $field['name'] : $field['textname']),
            'ismain' => $ismain,
            'setting' => dr_array2string($field['setting']),
            'issystem' => isset($field['issystem']) ? (int)$field['issystem'] : 1,
            'ismember' => isset($field['ismember']) ? (int)$field['ismember'] : 1,
            'disabled' => isset($field['disabled']) ? (int)$field['disabled'] : 0,
            'fieldname' => $field['fieldname'],
            'fieldtype' => $field['fieldtype'],
            'relatedid' => $rid,
            'relatedname' => $rname,
            'displayorder' => (int)$field['displayorder'],
        ));
    }

    // 内容模块
    public function get_module_info($module = null) {

        $rt = [];
        !$module && $module = $this->table('module')->order_by('displayorder ASC,id ASC')->getAll();
        if ($module) {
            foreach ($module as $data) {
                $mdir = $data['dirname'];
                // 如果没有配置文件就不更新缓存
                if (!is_file(dr_get_app_dir($mdir).'Config/App.php') || $data['disabled']) {
                    continue;
                }
                $config = require dr_get_app_dir($mdir).'Config/App.php';
                $setting = dr_string2array($data['setting']);
                $data['site'] = dr_string2array($data['site']);
                $setting['list_field'] = dr_list_field_order($setting['list_field']);
                $rt[$mdir] = [
                    'id' => $data['id'],
                    'name' => $config['name'],
                    'icon' => $config['icon'],
                    'site' => $data['site'],
                    'config' => $config,
                    'share' => $data['share'],
                    'comment' => dr_string2array($data['comment']),
                    'setting' => $setting,
                    'dirname' => $mdir,
                    'domain' => $data['site'][SITE_ID]['domain'] ? dr_http_prefix($data['site'][SITE_ID]['domain'].'/') : '',
                    'mobile_domain' => $data['site'][SITE_ID]['mobile_domain'] ? dr_http_prefix($data['site'][SITE_ID]['mobile_domain'].'/') : '',
                ];
            }
        }

        return $rt;
    }

    // 模块的共享栏目数据
    protected function _get_share_category($siteid, $dir = '') {

        !$this->cat_share[$siteid] && $this->cat_share[$siteid] = $this->db->table($siteid.'_share_category')->orderBy('displayorder ASC, id ASC')->get()->getResultArray();

        if (!$this->cat_share[$siteid]) {
            return [];
        }

        if (!$dir) {
            return $this->cat_share[$siteid];
        }

        $category = [];
        foreach ($this->cat_share[$siteid] as $i => $t) {
            if (!$t['child'] && $t['tid'] == 1 && $t['mid'] != $dir) {
                continue;
            }
            $category[$i] = $t;
        }

        return $category;
    }

    public function update_category_cache($siteid, $cdir) {

        // 修复优化栏目
        $category = \Phpcmf\Service::M()->db->table($siteid.'_'.$cdir.'_category')
            ->where('disabled', 0)->orderBy('displayorder ASC, id ASC')->get()->getResultArray();

        $category = \Phpcmf\Service::M('category')->init(['table' => $siteid.'_'.$cdir.'_category'])->repair($category);
        //\Phpcmf\Service::L('Config')->file(WRITEPATH.'config/category_'.$siteid.'_'.$cdir.'.php', '站点栏目配置文件', 32)->to_require($category);

        return $category;
    }

    // 栏目缓存数据
    protected function _get_category_cache($siteid, $cache, $mdir, $is_seo = 0) {

        if ($cache['share']) {
            $cdir = 'share';
            if (!\Phpcmf\Service::M()->db->tableExists($this->dbprefix($siteid.'_share_category'))) {
                return $cache;
            }
            if ($this->cat_share[$siteid]) {
                $category = $this->cat_share[$siteid];
            } else {
                $category = $this->cat_share[$siteid] = $this->update_category_cache($siteid, $cdir);
            }
        } else {
            $cdir = $cache['dirname'];
            $category = $this->update_category_cache($siteid, $cdir);
        }

        // 栏目开始
		$CAT = $CAT_DIR = $level = [];

        // 栏目的定义字段
        $field = $this->db->table('field')
            ->where('disabled', 0)
            ->where('relatedname', 'category-'.$cdir)
            ->orderBy('displayorder ASC, id ASC')->get()->getResultArray();
        if ($field) {
            foreach ($field as $f) {
                $f['setting'] = dr_string2array($f['setting']);
                $cache['category_field'][$f['fieldname']] = $f;
            }
        }
        if (!isset($cache['category_field']['thumb'])) {
            $this->_add_field([
                'name' => dr_lang('缩略图'),
                'ismain' => 1,
                'ismember' => 1,
                'fieldtype' => 'File',
                'fieldname' => 'thumb',
                'setting' => array(
                    'option' => array(
                        'ext' => 'jpg,gif,png,jpeg',
                        'size' => 10,
                        'input' => 1,
                        'attachment' => 0,
                    )
                )
            ], 1, 0, 'category-'.$cdir);
        }
        if ($cache['share'] && !isset($cache['category_field']['content'])) {
            $this->_add_field([
                'name' => dr_lang('栏目内容'),
                'ismain' => 1,
                'fieldtype' => 'Ueditor',
                'fieldname' => 'content',
                'setting' => array(
                    'option' =>
                        array (
                            'mode' => 1,
                            'show_bottom_boot' => 1,
                            'div2p' => 1,
                            'width' => '100%',
                            'height' => 400,
                        ),
                    'validate' =>
                        array (
                            'xss' => 1,
                            'required' => 1,
                        ),
                ),
            ], 1, 0, 'category-'.$cdir);
        }
        // 栏目字段
        if ($category) {
            $cache['category'] = $category;
            foreach ($category as $i => $c) {
                $category[$i]['setting'] = $c['setting'] = dr_string2array($c['setting']);
                $pid = explode(',', $c['pids']);
                $level[] = substr_count((string)$c['pids'], ',');
                $c['mid'] = isset($c['mid']) ? $c['mid'] : $cache['dirname'];
                if (isset($cache['setting']['pcatpost']) && $cache['setting']['pcatpost']) {
                    // 允许父栏目发布
                    $c['pcatpost'] = 1;
                } else {
                    $c['pcatpost'] = 0;
                }
                $cache['category'][$i]['topid'] = $c['topid'] = isset($pid[1]) ? $pid[1] : $c['id'];
                $c['catids'] = explode(',', $c['childids']);
                $c['is_post'] = $c['pcatpost'] ? 1 : ($c['child'] ? 0 : 1); // 是否允许发布内容
                if ($cache['share']) {
                    // 共享栏目时
                    //以本栏目为准
                    $c['setting']['html'] = intval($c['setting']['html']);
                    $c['setting']['chtml'] = intval($c['setting']['chtml']);
                    $c['setting']['urlrule'] = intval($c['setting']['urlrule']);
                } else {
                    // 独立模块栏目
                    //以站点为准
                    if (!isset($c['tid'])) {
                        $c['tid'] = $c['setting']['linkurl'] ? 2 : 1; // 判断栏目类型 2表示外链
                    }
                    $c['setting']['html'] = intval($cache['html']);
                    $c['setting']['chtml'] = intval($cache['html']);
                    $c['setting']['urlrule'] = intval($cache['site'][$siteid]['urlrule']);
                }
                // 获取栏目url
                if ($c['tid'] == 2 && $c['setting']['linkurl']) {
                    $c['url'] = dr_url_prefix($c['setting']['linkurl'], '', $siteid, 0);
                } else {
                    $c['url'] = dr_module_category_url($cache, $c);
                }
                // 统计栏目文章数量
                $c['total'] = '-';
                if ($is_seo) {
                    $c['setting']['seo'] = [];
                }
                // 格式化栏目
                $c['field'] = [];
                $CAT[$c['id']] = \Phpcmf\Service::L('Field')->app($cdir)->format_value($cache['category_field'], $c, 1);
                if ($c['pid']) {
                    $CAT_DIR[$c['pdirname'].$c['dirname']] = $c['id'];
                }
                $CAT_DIR[$c['dirname']] = $c['id'];
                // 归类栏目模型字段
                if ($c['ismain'] && $c['setting']['module_field']) {
                    foreach ($c['setting']['module_field'] as $_fname => $o) {
                        $CAT[$c['id']]['field'][] = $_fname;
                    }
                }
            }
            $CAT_DIR = [];
            // 自定义栏目模型字段，把父级栏目的字段合并至当前栏目
            $like = ['catmodule-'.$mdir];
            if ($cache['share']) {
                $like[] = 'catmodule-share';
            }

            // 模型字段查询
            $cat_data_field = [];
            $field = $this->db->table('field')
                ->where('ismain', 1)
                ->where('disabled', 0)
                ->whereIn('relatedname', $like)
                ->orderBy('displayorder ASC, id ASC')
                ->get()->getResultArray();
            if ($field) {
                foreach ($field as $f) {
                    $f['setting'] = dr_string2array($f['setting']);
                    $cat_data_field[$f['fieldname']] = $f;
                }
            }
            $cache['category_data_field'] = $cat_data_field;

            // 栏目结束
            if (!$cache['share']) {
                // 此变量说明本模块存在栏目模型字段
                $cache['category'] = $CAT;
            } else {
                // 共享模块需要筛选出自己的模块的栏目
                $cache['category'] = $this->_get_my_category($cache['dirname'], $CAT);
            }
            $cache['category_dir'] = $CAT_DIR;
            $cache['category_level'] = $level ? max($level) : 0;
        }

        // 栏目分表
        $cat_table = $siteid.'_'.$cdir.'_category';
        if ($this->db->fieldExists('is_ctable', $this->dbprefix($cat_table)) && $this->table($cat_table)->where('is_ctable=1')->counts()) {
            // 存在栏目分表
            $cache['is_ctable'] = 1;
        }

        // 共享模块
        if ($cache['share'] && $this->cat_share_lock[$siteid]) {
            // 删除缓存
            \Phpcmf\Service::L('cache')->clear('module-'.$siteid.'-share');
            // 写入缓存
            \Phpcmf\Service::L('cache')->set_file('module-'.$siteid.'-share', [
                'id' => 0,
                'name' => '共享',
                'share' => 1,
                'dirname' => 'share',
                'category' => $CAT,
                'is_ctable' => $cache['is_ctable'],
                'category_dir' => $CAT_DIR,
                'category_level' => $cache['category_level'],
            ]);
            $this->cat_share_lock[$siteid] = 0;
        }

        return $cache;
    }

    public function sync_site_cache($module, $webpath, $site_domain, $app_domain, $sso_domain, $client_domain, $module_cache_file, $site = []) {

        // 循环模块域名
        !$module && $module = $this->table('module')->getAll();
        if ($module) {
            foreach ($module as $t) {
                if (!is_file(APPSPATH.ucfirst($t['dirname']).'/Config/App.php')) {
                    continue;
                }
                $t['site'] = dr_string2array($t['site']);
                if (!$t['site']) {
                    // 表示没有进行站点安装
                    continue;
                }
                // 循环站点信息
                foreach ($t['site'] as $sid => $v) {
                    if (!$t['share']) {
                        // 独立模块才有域名
                        $webpath[$sid][$t['dirname']] = $webpath[$sid]['site'];
                        if ($v['domain']) {
                            $site_domain[$v['domain']] = $sid;
                            $app_domain[$v['domain']] = $t['dirname'];
                            $sso_domain[] = $v['domain'];
                            // 网站路径
                            if ($v['webpath']) {
                                $webpath[$sid][$t['dirname']] = dr_get_dir_path($v['webpath']);
                            }
                        }
                        if ($v['mobile_domain']) {
                            $site_domain[$v['mobile_domain']] = $sid;
                            $app_domain[$v['mobile_domain']] = $t['dirname'];
                            $client_domain[$v['domain']] = $v['mobile_domain'];
                            $sso_domain[] = $v['mobile_domain'];
                        }
                    }
                }
            }
        }

        // 循环站点
        !$site && $site = $this->table('site')->where('disabled', 0)->getAll();
        foreach ($site as $t) {
            $domain = \Phpcmf\Service::L('Cache')->get_file('category_domain_'.$t['id'], 'module');
            if ($domain) {
                foreach ($domain as $v) {
                    if ($v['domain']) {
                        $site_domain[$v['domain']] = $t['id'];
                    }
                    if ($v['mobile_domain']) {
                        $site_domain[$v['mobile_domain']] = $t['id'];
                    }
                }
            }
        }

        return [$webpath, $site_domain, $app_domain, $sso_domain, $client_domain];
    }

    public function domian($value, $my, $data) {

        $module = $this->table('module')->getAll();
        foreach ($module as $t) {
            $path = dr_get_app_dir($t['dirname']);
            if (!is_file($path.'Config/App.php')) {
                continue;
            }
            $cfg = require $path.'Config/App.php';
            $t['site'] = dr_string2array($t['site']);
            $my[$t['dirname']] = [
                'share' => $t['share'],
                'name' => dr_lang($cfg['name']),
                'error' => '',
            ];
            if ($t['share']) {
                unset($my[$t['dirname']]);
                continue;
            }
            if ($t['site'][SITE_ID]) {
                if ($value) {
                    $t['site'][SITE_ID]['domain'] = strtolower((string)$value['module_'.$t['dirname']]);
                    $t['site'][SITE_ID]['mobile_domain'] = $value['module_mobile_'.$t['dirname']];
                    $t['site'][SITE_ID]['webpath'] = $value['webpath_'.$t['dirname']];
                    $this->db->table('module')->where('id', $t['id'])->update([
                        'site' => dr_array2string($t['site'])
                    ]);
                }
                $data['module_'.$t['dirname']] = strtolower((string)$t['site'][SITE_ID]['domain']);
                $data['module_mobile_'.$t['dirname']] = strtolower((string)$t['site'][SITE_ID]['mobile_domain']);
                $data['webpath_'.$t['dirname']] = $t['site'][SITE_ID]['webpath'];
            } else {
                $my[$t['dirname']]['error'] = dr_lang('当前站点未安装');
            }
        }

        return [$my, $data];
    }

    // 编辑器更新
    public function update_ueditor($site) {
        $module = $this->table('module')->getAll();
        foreach ($module as $t) {
            $path = dr_get_app_dir($t['dirname']);
            if (!is_file($path.'Config/App.php')) {
                continue;
            } elseif ($t['share']) {
                continue;
            }
            $t['site'] = dr_string2array($t['site']);
            foreach ($site as $r) {
                $siteid = $r['id'];
                if (isset($t['site'][$siteid]['domain']) && $t['site'][$siteid]['domain'] && $t['site'][$siteid] && $t['site'][$siteid]['webpath']) {
                    $path = rtrim($t['site'][$siteid]['webpath'], '/').'/';
                    // 复制百度编辑器到当前目录
                    \Phpcmf\Service::M('cache')->cp_ueditor_file($path);
                    // 复制百度编辑器到移动端项目
                    \Phpcmf\Service::M('cache')->cp_ueditor_file($path.'mobile/');
                }
            }
        }
    }

    // 重建子站配置文件
    public function update_site_config($t) {
        if (!is_array($t)) {
            return;
        }
        $path = dr_get_app_dir($t['dirname']);
        if (!is_file($path.'Config/App.php')) {
            return;
        } elseif ($t['share']) {
            return;
        }
        $t['site'] = dr_string2array($t['site']);
        foreach ($this->site as $siteid) {
            if (isset($t['site'][$siteid]['domain']) && $t['site'][$siteid]['domain'] && $t['site'][$siteid] && $t['site'][$siteid]['webpath']) {
                $rt = \Phpcmf\Service::M('cache')->update_webpath('Module_Domain', $t['site'][$siteid]['webpath'], [
                    'SITE_ID' => $siteid,
                    'MOD_DIR' => $t['dirname'],
                ], IS_USE_MODULE . 'Temps/');
                if ($rt) {
                    \Phpcmf\Service::M('cache')->_error_msg('模块[' . $t['site'][$siteid]['domain'] . ']: ' . $rt);
                }
            }
        }
    }

    // 付款表
    public function paytable($cache, $paytable, $module, $siteid) {

        !$module && $module = $this->table('module')->getAll();
        if ($module) {
            $this->db->resetDataCache();// 清除缓存，影响字段存在的重复
            $is_module_form = $this->is_table_exists('module_form');
            foreach ($module as $t) {
                // 模块主表
                $table = dr_module_table_prefix($t['dirname'], $siteid);
                $prefix = $this->dbprefix($table);
                // 判断是否存在表
                if (!$this->db->tableExists($prefix)) {
                    continue;
                }
                $main_field = $this->db->getFieldNames($prefix);
                if ($main_field) {
                    // 付款表
                    $paytable['module-'.$t['id']] = [
                        'table' => $table,
                        'name' => 'title',
                        'thumb' => 'thumb',
                        'url' => dr_web_prefix('index.php?s='.$t['dirname'].'&c=show&id='),
                        'username' => 'author',
                    ];
                    // 模块表
                    $cache[$prefix] = $main_field;
                    // 模块附表
                    $table = $prefix.'_data_0';
                    $this->db->tableExists($table) && $cache[$table] = $this->db->getFieldNames($table);
                    // 栏目模型主表
                    $table = $prefix.'_category_data';
                    $this->db->tableExists($table) && $cache[$table] = $this->db->getFieldNames($table);
                    // 模块点击量表
                    $table = $prefix.'_hits';
                    $this->db->tableExists($table) && $cache[$table] = $this->db->getFieldNames($table);
                    // 模块评论表
                    $table = $prefix.'_comment';
                    $this->db->tableExists($table) && $cache[$table] = $this->db->getFieldNames($table);
                    // 模块表单
                    if ($is_module_form) {
                        $form = $this->table('module_form')->where('module', $t['dirname'])->order_by('id ASC')->getAll();
                        if ($form) {
                            foreach ($form as $f) {
                                // 主表
                                $table = $prefix . '_form_' . $f['table'];
                                $this->db->tableExists($table) && $cache[$table] = $this->db->getFieldNames($table);
                                // 付款表
                                $paytable['mform-' . $t['dirname'] . '-' . $f['id']] = [
                                    'table' => $table,
                                    'name' => 'title',
                                    'thumb' => 'thumb',
                                    'url' => dr_web_prefix('index.php?s=' . $t['dirname'] . '&c=' . $f['table'] . '&m=show&id='),
                                    'username' => 'author',
                                ];
                            }
                        }
                    }
                }
            }
        }
        return [$cache, $paytable];
    }

    // 缓存
    public function cache($siteid = SITE_ID, $module = null) {

       if ($siteid == 1) {
           // 规则缓存
           $data = $this->table('urlrule')->getAll();
           $cache = [];
           if ($data) {
               foreach ($data as $t) {
                   $t['value'] = dr_string2array($t['value']);
                   $cache[$t['id']] = $t;
               }
           }

           \Phpcmf\Service::L('cache')->set_file('urlrule', $cache);

           $table = $this->dbprefix($siteid.'_share_category');
           if (!\Phpcmf\Service::M()->db->tableExists($table)) {
               $this->db->simpleQuery(dr_format_create_sql("
                CREATE TABLE IF NOT EXISTS `".$table."` (
                  `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
                  `tid` tinyint(1) NOT NULL COMMENT '栏目类型，0单页，1模块，2外链',
                  `pid` smallint(5) unsigned NOT NULL DEFAULT '0' COMMENT '上级id',
                  `mid` varchar(20) NOT NULL COMMENT '模块目录',
                  `pids` varchar(255) NOT NULL COMMENT '所有上级id',
                  `name` varchar(255) NOT NULL COMMENT '栏目名称',
                  `dirname` varchar(255) NOT NULL COMMENT '栏目目录',
                  `pdirname` varchar(255) NOT NULL COMMENT '上级目录',
                  `child` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否有下级',
                  `disabled` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否禁用',
                  `ismain` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否主栏目',
                  `childids` text NOT NULL COMMENT '下级所有id',
                  `thumb` varchar(255) NOT NULL COMMENT '栏目图片',
                  `show` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '是否显示',
                  `content` mediumtext NOT NULL COMMENT '单页内容',
                  `setting` mediumtext NOT NULL COMMENT '属性配置',
                  `displayorder` smallint(5) NOT NULL DEFAULT '0',
                  PRIMARY KEY (`id`),
                  KEY `mid` (`mid`),
                  KEY `tid` (`tid`),
                  KEY `show` (`show`),
                  KEY `disabled` (`disabled`),
                  KEY `ismain` (`ismain`),
                  KEY `dirname` (`dirname`),
                  KEY `module` (`pid`,`displayorder`,`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='共享模块栏目表';
                "));
           }
       } else {
           $table = $this->dbprefix($siteid.'_share_category');
           if (!\Phpcmf\Service::M()->db->tableExists($table)) {
               $sql = $this->db->query("SHOW CREATE TABLE `".$this->dbprefix('1_share_category')."`")->getRowArray();
               $sql = str_replace(
                   array($sql['Table'], 'CREATE TABLE'),
                   array('{tablename}', 'CREATE TABLE IF NOT EXISTS'),
                   $sql['Create Table']
               );
               $this->db->simpleQuery(str_replace('{tablename}', $table, dr_format_create_sql($sql)));
               $this->db->table($table)->truncate();
           }
       }

        $table = $this->dbprefix($siteid.'_share_index');
        if (!\Phpcmf\Service::M()->db->tableExists($table)) {
            $this->db->simpleQuery(dr_format_create_sql("
                CREATE TABLE IF NOT EXISTS `".$this->dbprefix($siteid.'_share_index')."` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `mid` varchar(20) NOT NULL COMMENT '模块目录',
                  PRIMARY KEY (`id`),
                  KEY `mid` (`mid`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='共享模块内容索引表';
                "));
        }

        // 重置缓存
        $this->cat_share_lock[$siteid] = 1;

        \Phpcmf\Service::L('cache')->set_file('module-'.$siteid, []);
        \Phpcmf\Service::L('cache')->set_file('module-'.$siteid.'-share', []);
        \Phpcmf\Service::L('cache')->set_file('module-'.$siteid.'-content', []);

        $all = $content = [];
        $module = $this->get_module_info($module);
        $menu_model = \Phpcmf\Service::M('menu');

        $ld = WRITEPATH.'module/category-'.SITE_ID.'-share-select/ld.lock';
        if (is_file($ld)) {
            unlink($ld);
        }
        $file = WRITEPATH.'config/category.php';
        if (is_file($file)) {
            $cat_config = require $file;
            if (isset($cat_config['share']['ld']) && $cat_config['share']['ld']) {
                file_put_contents($ld, 1);
            }
        } else {
            $cat_config = [];
        }

        if ($module) {
            foreach ($module as $mdir => $data) {
                //unset($cache['config']);
                $ld = WRITEPATH.'module/category-'.SITE_ID.'-'.$mdir.'-select/ld.lock';
                if (is_file($ld)) {
                    unlink($ld);
                }
                if ($data['share']) {
                    if (isset($cat_config['share']['ld']) && $cat_config['share']['ld']) {
                        file_put_contents($ld, 1);
                    }
                } else {
                    if (isset($cat_config[$mdir]['ld']) && $cat_config[$mdir]['ld']) {
                        file_put_contents($ld, 1);
                    }
                }
                // 当前站点安装过就缓存它
                if (isset($data['site'][$siteid]) && $data['site'][$siteid]) {
                    $cache = $data;
                    $config = $data['config'];
                    $cache['cname'] = isset($config['cname']) && $config['cname'] ? $config['cname'] : $cache['name'];
                    $cache['html'] = $data['site'][$siteid]['html'];
                    $cache['title'] = $data['site'][$siteid]['module_title'] ? $data['site'][$siteid]['module_title'] : $data['name'];
                    $cache['urlrule'] = $data['site'][$siteid]['urlrule'];
                    // 绑定的域名
                    $cache['domain'] = $data['site'][$siteid]['domain'] ? dr_http_prefix($data['site'][$siteid]['domain'].'/') : '';
                    $cache['mobile_domain'] = $data['site'][$siteid]['mobile_domain'] ? dr_http_prefix($data['site'][$siteid]['mobile_domain'].'/') : '';
                    // 补全url
                    $cache['url'] = \Phpcmf\Service::L('router')->module_url($cache, $siteid); // 模块的URL地址
                    $cache['murl'] = $data['site'][$siteid]['mobile_domain'] ? $cache['mobile_domain'] : dr_url_prefix($cache['url'], '', $siteid, 1); // 模块的URL地址
                    // 模块的自定义字段
                    $cache['field'] = [];
                    $field = $this->db->table('field')
                        ->where('disabled', 0)
                        ->where('relatedid', intval($data['id']))
                        ->where('relatedname', 'module')
                        ->orderBy('displayorder ASC, id ASC')->get()->getResultArray();
                    if ($field) {
                        foreach ($field as $f) {
                            $f['setting'] = dr_string2array($f['setting']);
                            $cache['field'][$f['fieldname']] = $f;
                        }
                    }

                    // 系统模块
                    $cache['system'] = $config['system'];

                    if (isset($config['hcategory']) && $config['hcategory']) {
                        // 不使用栏目功能
                    } else {
                        // 如果是共享共享栏目就查询share表
                        /*
                        if (!$data['share'] && (!isset($data['site'][$siteid]['is_cat']) || !$data['site'][$siteid]['is_cat'])) {
                            // 独立模块统一规则模式
                            // $cache = $this->_get_category_cache($siteid, $cache, $mdir, 1);
                            \Phpcmf\Service::M('repair', 'module')->_get_category_cache($siteid, $cache, $mdir, 1);
                        } else {
                            //$cache = $this->_get_category_cache($siteid, $cache, $mdir, 0);
                            \Phpcmf\Service::M('repair', 'module')->_get_category_cache($siteid, $cache, $mdir, 0);
                        }*/
                    }

                    // 栏目分表
                    $cat_table = $siteid.'_'.($data['share'] ? 'share' : $mdir).'_category';
                    if ($this->db->fieldExists('is_ctable', $this->dbprefix($cat_table)) && $this->table($cat_table)->where('is_ctable=1')->counts()) {
                        // 存在栏目分表
                        $cache['is_ctable'] = 1;
                    }

                    // 自定义栏目模型字段，把父级栏目的字段合并至当前栏目
                    $like = ['catmodule-'.$mdir];
                    if ($cache['share']) {
                        $like[] = 'catmodule-share';
                    }

                    // 模型字段查询
                    $cat_data_field = [];
                    $field = $this->db->table('field')
                        ->where('ismain', 1)
                        ->where('disabled', 0)
                        ->whereIn('relatedname', $like)
                        ->orderBy('displayorder ASC, id ASC')
                        ->get()->getResultArray();
                    if ($field) {
                        foreach ($field as $f) {
                            $f['setting'] = dr_string2array($f['setting']);
                            $cat_data_field[$f['fieldname']] = $f;
                        }
                    }
                    $cache['category_data_field'] = $cat_data_field;
                    $cache['category'] = [ 0 => $mdir];

                    // 模块表单
                    $cache['form'] = [];
                    if (dr_is_app('mform')) {
                        $cache = \Phpcmf\Service::M('mform', 'mform')->link_cache($mdir, $cache);
                    }

                    // 搜索验证
                    !$cache['setting']['search']['use'] && $cache['setting']['search'] = [];

                    // 评论缓存 转移评论
                    if ($cache['comment'] && dr_is_app('comment')) {
                        $ct = $this->table('app_comment')->where('name', 'module')->getRow();
                        // 转移评论
                        if ($ct) {
                            $ct_cfg = dr_string2array($ct['value']);
                            if (!$ct_cfg[$mdir]) {
                                $this->db->table('app_comment')->where('name', 'module')->update([
                                    'value' => dr_array2string([
                                        $mdir => $cache['comment'],
                                    ]),
                                ]);
                            }
                        } else {
                            $this->db->table('app_comment')->insert([
                                'name' => 'module',
                                'value' => dr_array2string([
                                    $mdir => $cache['comment'],
                                ]),
                            ]);
                        }
                        $this->db->table('module')->where('dirname', $mdir)->update(['comment' => '']);
                    }
                    unset($cache['comment']);

                    // 更新内容模块菜单
                    \Phpcmf\Service::M('menu', 'module')->update_module($mdir, $config, $cache['form']);
                    !$cache['title'] && $cache['title'] = $cache['name'];

                    // 执行模块自己的缓存程序
                    if (is_file(dr_get_app_dir($mdir).'Config/Mcache.php')) {
                        require dr_get_app_dir($mdir).'Config/Mcache.php';
                    }
                    $cache['mid'] = $cache['share'] ? 'share' : $mdir;
                    // 全部模块
                    $all[$mdir] = [
                        'mid' => $cache['mid'],
                        'url' => $cache['url'],
                        'murl' => $cache['murl'],
                        'name' => $cache['name'],
                        'icon' => $cache['icon'],
                        'title' => $cache['title'],
                        'share' => $cache['share'],
                        'system' => $config['system'],
                        'hlist' => (int)$config['hlist'],
                        'is_ctable' => $cache['is_ctable'],
                        'hcategory' => (int)$config['hcategory'],
                        'scategory' => (int)$config['scategory'],
                        'search' => $cache['setting']['search']['use'] ? 1 : 0,
                        'dirname' => $mdir,
                        'is_index_html' => $cache['setting']['module_index_html'] ? 1 : 0,
                        'pcatpost' => isset($cache['setting']['pcatpost']) && $cache['setting']['pcatpost'] ? 1 : 0,
                    ];

                    // 内容模块
                    if (in_array($config['system'], [1, 2])) {
                        $content[$mdir] = $all[$mdir];
                    }

                    // 删除缓存
                    //\Phpcmf\Service::L('cache')->clear('module-'.$siteid.'-'.$mdir);

                    // 共享模块
                    if ($cache['share'] && $this->cat_share_lock[$siteid]) {
                        // 删除缓存
                        \Phpcmf\Service::L('cache')->clear('module-'.$siteid.'-share');
                        // 写入缓存
                        \Phpcmf\Service::L('cache')->set_file('module-'.$siteid.'-share', [
                            'id' => 0,
                            'name' => '共享',
                            'share' => 1,
                            'mid' => 'share',
                            'dirname' => 'share',
                            'category' => [0 => 'share'],
                        ]);
                        $this->cat_share_lock[$siteid] = 0;
                    }

                    // 写入缓存
                    \Phpcmf\Service::L('cache')->set_file('module-'.$siteid.'-'.$mdir, $cache);
                } else {
                }
            }
        }

        // 写入缓存
        \Phpcmf\Service::L('cache')->set_file('module-'.$siteid, $all);
        \Phpcmf\Service::L('cache')->set_file('module-'.$siteid.'-content', $content);

        // vip标记
        file_put_contents(CMSPATH.'Config/vip.lock', 'ok');

        if ($this->table('admin_menu')->where('uri', 'site_param/index')->counts()
            && $this->table('admin_menu')->where('uri', 'module/site_param/index')->counts()) {
            $this->table('admin_menu')->where('uri', 'site_param/index')->delete();
        }
        $this->table('admin_menu')->update(0, ['uri' => 'module/site_config/index'], 'uri="site_config/index"');
        $this->table('admin_menu')->update(0, ['uri' => 'module/site_mobile/index'], 'uri="site_mobile/index"');
        $this->table('admin_menu')->update(0, ['uri' => 'module/site_domain/index'], 'uri="site_domain/index"');
        $this->table('admin_menu')->update(0, ['uri' => 'module/site_image/index'], 'uri="site_image/index"');
        $this->table('admin_menu')->update(0, ['name' => '网站设置'], 'mark="config-web"');

        return;
    }

}