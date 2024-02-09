<?php namespace Phpcmf\Controllers\Admin;

class Api extends \Phpcmf\Common
{
    public $cat_config;

    /**
     * 初始化
     */
    public function __construct($object = NULL)
    {
        parent::__construct();
        if ($object) {
            foreach ($object as $var => $value) {
                $this->$var = $value;
            }
        }
    }


    // 统计
    public function mtotal() {

        $t1 = $t2 = $t3 = $t4 = $t5 = 0;
        $dir = dr_safe_filename(\Phpcmf\Service::L('input')->get('dir'));
        $prefix = dr_module_table_prefix($dir);
        if (is_dir(dr_get_app_dir($dir))) {
            $this->_module_init($dir);
            $t1 = \Phpcmf\Service::M()->table($prefix)->where($this->content_model->get_admin_list_where($prefix))->where('DATEDIFF(from_unixtime(inputtime),now())=0')->counts();
            $t2 = \Phpcmf\Service::M()->table($prefix)->where($this->content_model->get_admin_list_where($prefix))->counts();
            $t3 = \Phpcmf\Service::M()->table($prefix.'_verify')->where($this->content_model->get_admin_list_verify_where($this->content_model->get_admin_list_where($prefix.'_verify')))->counts();
            $t4 = \Phpcmf\Service::M()->table($prefix.'_recycle')->where($this->content_model->get_admin_list_where($prefix.'_recycle'))->counts();
            $t5 = \Phpcmf\Service::M()->table($prefix.'_time')->where($this->content_model->get_admin_list_where($prefix.'_time'))->counts();
        }
        echo '$("#'.$dir.'_today").html('.$t1.');';
        echo '$("#'.$dir.'_all").html('.$t2.');';
        echo '$("#'.$dir.'_verify").html('.$t3.');';
        echo '$("#'.$dir.'_recycle").html('.$t4.');';
        echo '$("#'.$dir.'_timing").html('.$t5.');';
        exit;
    }

    // 更新url
    public function update_url() {

        $mid = dr_safe_filename(\Phpcmf\Service::L('input')->get('mid'));
        if (!$mid) {
            $this->_html_msg(0, dr_lang('mid参数不能为空'));
        }

        $this->_module_init($mid);

        $page = (int)\Phpcmf\Service::L('input')->get('page');
        $psize = 300; // 每页处理的数量
        $total = (int)\Phpcmf\Service::L('input')->get('total');

        // 栏目分表id
        $cats = [];
        $ctid = (int)\Phpcmf\Service::L('input')->get('ctid');
        if ($this->module['is_ctable']) {
            // 存在栏目分表
            $cats = \Phpcmf\Service::M()->table($this->module['share'] ? SITE_ID.'_share_category' : SITE_ID.'_'.$mid.'_category')->where('is_ctable=1 and pid=0')->getAll();
        }

        if (!$page) {
            // 计算数量
            $total = \Phpcmf\Service::M()->db->table($this->content_model->mytable)->countAllResults();
            if (!$total) {
                if ($cats && isset($cats[$ctid-1])) {
                    $ctid++;
                } else {
                    $this->_html_msg(0, dr_lang('无可用内容更新'));
                }
            }
            $url = dr_url('module/api/'.\Phpcmf\Service::L('Router')->method, ['mid' => $mid, 'ctid' => $ctid]);
            $this->_html_msg(1, dr_lang('正在执行中...'), $url.'&total='.$total.'&page='.($page+1));
        }

        $tpage = ceil($total / $psize); // 总页数

        // 更新完成
        if ($page > $tpage) {
            if ($cats && isset($cats[$ctid])) {
                $url = dr_url('module/api/'.\Phpcmf\Service::L('Router')->method, ['mid' => $mid, 'ctid' => $ctid+1]);
                $this->_html_msg(1, dr_lang('正在执行中...'), $url.'&total=0&page=0');
            }
            \Phpcmf\Service::M('cache')->update_data_cache();
            $this->_html_msg(1, dr_lang('更新完成'));
        }

        $cname = '';
        $table = $this->content_model->mytable;
        if ($ctid && isset($cats[$ctid-1])) {
            $table = dr_module_ctable($table, $cats[$ctid-1]);
            $cname = dr_lang('栏目[%s]分表', $cats[$ctid-1]['name']);
        }

        $data = \Phpcmf\Service::M()->db->table($table)->limit($psize, $psize * ($page - 1))->orderBy('id DESC')->get()->getResultArray();
        $update = [];
        foreach ($data as $t) {
            if ($t['link_id'] && $t['link_id'] >= 0) {
                // 同步栏目的数据
                $i = $t['id'];
                $t = \Phpcmf\Service::M()->db->table($table)->where('id', (int)$t['link_id'])->get()->getRowArray();
                if (!$t) {
                    continue;
                }
                $url = dr_module_show_url($this->module, $t);
                $t['id'] = $i; // 替换成当前id
            } else {
                $url = dr_module_show_url($this->module, $t);
            }
            $t['url'] != $url && $update[] = [
                'id' => (int)$t['id'],
                'url'=> $url,
            ];
        }
        $update && \Phpcmf\Service::M()->table($table)->update_batch($update);

        $this->_html_msg( 1, dr_lang('%s正在执行中【%s】...', $cname, "$tpage/$page"),
            dr_url('module/api/'.\Phpcmf\Service::L('Router')->method, ['mid' => $mid, 'total' => $total ,'ctid' => $ctid, 'page' => $page + 1])
        );
    }

    // 统计栏目
    public function ctotal() {

        $rt = '';

        if (IS_POST) {
            $zt = \Phpcmf\Service::L('input')->get('zt');
            $ids = dr_string2array(\Phpcmf\Service::L('input')->post('cid'));
            if ($ids) {
                foreach ($ids as $t) {
                    list($id, $mids) = explode('-', $t);
                    if ($id && $mids) {
                        $num = 0;
                        $arr = explode(',', $mids);
                        foreach ($arr as $mid) {
                            $dir = \Phpcmf\Service::L('category', 'module')->get_mid($mid);
                            $cat = \Phpcmf\Service::M()->table(SITE_ID.'_'.$dir.'_category')->get($id);
                            $db = \Phpcmf\Service::M()->table(dr_module_table_prefix($mid).'_index');
                            if ($cat && $cat['child']) {
                                $db->where('catid in ('.$cat['childids'].')');
                            } else {
                                $db->where('catid', $id);
                            }
                            $num+= $db->where('status=9')->counts();
                        }
                        if ($zt == 1) {
                            $rt.= '$(".cat-total-'.$id.'").html("'.$num.'");';
                        } else {
                            $rt.= '$(".cat-total-'.$id.'").html("'.dr_lang('（约%s）', $num).'");';
                        }
                    }
                }
            }
        }

        $this->_json(1, $rt);
    }

    // 批量更新栏目关系
    public function update_category_repair() {

        $mid = dr_safe_filename(\Phpcmf\Service::L('input')->get('mid'));
        if (!$mid) {
            $cdir = 'share';
        } else {
            $cdir = $mid;
        }

        $all = intval(\Phpcmf\Service::L('input')->get('all'));
        $url = dr_url('module/api/update_category_repair', ['mid' => $mid, 'all' => $all]);
        $page = (int)\Phpcmf\Service::L('input')->get('page');
        $psize = MAX_CATEGORY; // 每页处理的数量
        $total = (int)\Phpcmf\Service::L('input')->get('total');

        $file = WRITEPATH.'config/category.php';
        if (is_file($file)) {
            $this->cat_config = require $file;
            if (isset($this->cat_config[$cdir]) && isset($this->cat_config[$cdir]['MAX_CATEGORY']) && $this->cat_config[$cdir]['MAX_CATEGORY']) {
                $psize = intval($this->cat_config[$cdir]['MAX_CATEGORY']);
            }
        }

        if (!$page) {
            // 修复栏目数据
            $total = \Phpcmf\Service::M('repair', 'module')->repair_data($cdir, $psize);
            if (!$total) {
                $this->_html_msg(1, dr_lang('更新完成'));
            }
            if ($all) {
                dr_dir_delete(WRITEPATH.'module/category-'.SITE_ID.'-'.$cdir.'-child');
                dr_dir_delete(WRITEPATH.'module/category-'.SITE_ID.'-'.$cdir.'-data');
                dr_dir_delete(WRITEPATH.'module/category-'.SITE_ID.'-'.$cdir.'-min');
                dr_dir_delete(WRITEPATH.'module/category-'.SITE_ID.'-'.$cdir.'-main');
                dr_dir_delete(WRITEPATH.'module/category-'.SITE_ID.'-'.$cdir.'-select');
                dr_dir_delete(WRITEPATH.'module/category-'.SITE_ID.'-share-select');
            }
            $this->_html_msg(1, dr_lang('正在执行中...'), $url.'&total='.$total.'&page='.($page+1));
        }

        $tpage = ceil($total / $psize); // 总页数
        $cats = \Phpcmf\Service::L('cache')->get_auth_data('category-page-'.$cdir, SITE_ID);
        if (!$cats) {
            $this->_html_msg(0, dr_lang('临时数据读取失败'));
        } elseif (!isset($cats[$page-1]) || $page > $tpage) {
            // 更新完成,新生成顶级分类的关系
            \Phpcmf\Service::M('repair', 'module')->repair_top_nextids($cdir);
            if ($all) {
                $this->_html_msg( 1, dr_lang('正在执行中...', ""),
                    dr_url('module/api/update_category_cache', ['mid' => $mid])
                );
            } else {
                $this->_html_msg(1, dr_lang('更新完成'));
            }
        }

        foreach ($cats[$page-1] as $cat) {
            \Phpcmf\Service::M('repair', 'module')->repair_sync(
                $cat, $cdir
            );
        }

        $this->_html_msg( 1, dr_lang('正在更新栏目关系【%s】...', "$tpage/$page"),
            $url.'&total='.$total.'&page='.($page+1)
        );
    }

    // 更新栏目缓存配置
    public function update_category_cache() {

        $mid = dr_safe_filename(\Phpcmf\Service::L('input')->get('mid'));
        if (!$mid) {
            $cdir = 'share';
        } else {
            $cdir = $mid;
        }

        $all = intval(\Phpcmf\Service::L('input')->get('all'));
        $url = dr_url('module/api/update_category_cache', ['mid' => $mid, 'all' => $all]);
        $page = (int)\Phpcmf\Service::L('input')->get('page');
        $psize = MAX_CATEGORY; // 每页处理的数量
        $total = (int)\Phpcmf\Service::L('input')->get('total');

        $file = WRITEPATH.'config/category.php';
        if (is_file($file)) {
            $this->cat_config = require $file;
            if (isset($this->cat_config[$cdir]) && isset($this->cat_config[$cdir]['MAX_CATEGORY']) && $this->cat_config[$cdir]['MAX_CATEGORY']) {
                $psize = intval($this->cat_config[$cdir]['MAX_CATEGORY']);
            }
        }

        if (!$page) {
            // 修复栏目数据
            $total = \Phpcmf\Service::M('repair', 'module')->repair_data($cdir, $psize);
            dr_dir_delete(WRITEPATH.'module/category-'.SITE_ID.'-'.$cdir.'-data/');
            if (!$total) {
                $this->_html_msg(1, dr_lang('更新完成'));
            }
            \Phpcmf\Service::L('cache')->set_auth_data(
                'category-cache-'.$cdir,
                [],
                SITE_ID
            );
            \Phpcmf\Service::L('cache')->set_auth_data(
                'category-dir-'.$cdir,
                [],
                SITE_ID
            );
            \Phpcmf\Service::L('cache')->set_auth_data(
                'category-main-'.$cdir,
                [],
                SITE_ID
            );
            $this->_html_msg(1, dr_lang('正在执行中...'), $url.'&total='.$total.'&page='.($page+1));
        }

        $tpage = ceil($total / $psize); // 总页数

        $cats = \Phpcmf\Service::L('cache')->get_auth_data('category-page-'.$cdir, SITE_ID);
        if (!$cats) {
            $this->_html_msg(0, dr_lang('临时数据读取失败'));
        } elseif (!isset($cats[$page-1]) || $page > $tpage) {
            \Phpcmf\Service::L('cache')->set_file(
                'cache',
                \Phpcmf\Service::L('cache')->get_auth_data('category-cache-'.$cdir, SITE_ID),
                'module/category-'.SITE_ID.'-'.$cdir.'-data'
            );
            \Phpcmf\Service::L('cache')->set_file(
                'main',
                \Phpcmf\Service::L('cache')->get_auth_data('category-main-'.$cdir, SITE_ID),
                'module/category-'.SITE_ID.'-'.$cdir.'-data'
            );
            \Phpcmf\Service::L('cache')->set_file(
                'dir',
                \Phpcmf\Service::L('cache')->get_auth_data('category-dir-'.$cdir, SITE_ID),
                'module/category-'.SITE_ID.'-'.$cdir.'-data'
            );
            if ($cdir == 'share') {
                $this->_html_msg( 1, dr_lang('正在执行中...', ""),
                    dr_url('module/api/update_category_select')
                );
            } else {
                \Phpcmf\Service::L('cache')->set_file(
                    'mid',
                    $cdir,
                    'module/category-'.SITE_ID.'-'.$cdir.'-data'
                );
                $this->_html_msg(1, dr_lang('更新完成'));
            }
        }

        $cat = \Phpcmf\Service::L('cache')->get_auth_data('category-data-'.$cdir, SITE_ID);
        \Phpcmf\Service::M('repair', 'module')->init([
            'cat_dir' => \Phpcmf\Service::L('cache')->get_auth_data('category-dir-'.$cdir, SITE_ID),
            'cat_cache' => \Phpcmf\Service::L('cache')->get_auth_data('category-cache-'.$cdir, SITE_ID),
            'cat_main' => \Phpcmf\Service::L('cache')->get_auth_data('category-main-'.$cdir, SITE_ID),
            'categorys' => $cat,
        ]);
        foreach ($cats[$page-1] as $v) {
            $cat[$v['id']] && \Phpcmf\Service::M('repair', 'module')->cache($v['id'], $mid);
        }

        \Phpcmf\Service::L('cache')->set_auth_data(
            'category-main-'.$cdir,
            \Phpcmf\Service::M('repair', 'module')->get_cat_main(),
            SITE_ID
        );
        \Phpcmf\Service::L('cache')->set_auth_data(
            'category-cache-'.$cdir,
            \Phpcmf\Service::M('repair', 'module')->get_cat_cache(),
            SITE_ID
        );
        \Phpcmf\Service::L('cache')->set_auth_data(
            'category-dir-'.$cdir,
            \Phpcmf\Service::M('repair', 'module')->get_cat_dir(),
            SITE_ID
        );

        $this->_html_msg( 1, dr_lang('正在更新栏目配置【%s】...', "$tpage/$page"),
            $url.'&total='.$total.'&page='.($page+1)
        );
    }

    // 更新栏目选择器
    public function update_category_select() {

        $url = dr_url('module/api/update_category_select');
        $page = (int)\Phpcmf\Service::L('input')->get('page');

        $array = [];
        $module = \Phpcmf\Service::L('cache')->get('module-'.SITE_ID.'-content');
        foreach ($module as $mdir => $tt) {
            if ($tt['share']) {
                $array[] = $mdir;
            }
            //$array[] = $mdir;
        }

        if (!$page) {
            // 修复栏目数据
            dr_dir_delete(WRITEPATH.'module/category-'.SITE_ID.'-share-select/');
            foreach ($module as $mdir => $tt) {
                dr_dir_delete(WRITEPATH.'module/category-'.SITE_ID.'-'.$mdir.'-select/');
                \Phpcmf\Service::L('cache')->set_file(
                    'mid',
                    $tt['share'] ? 'share' : $mdir,
                    'module/category-'.SITE_ID.'-'.$mdir.'-data'
                );
            }
            $this->_html_msg(1, dr_lang('正在执行中...'), $url.'&page='.($page+1));
        }

        if (!isset($array[$page-1])) {
            \Phpcmf\Service::M('cache')->update_cache();
            $this->_html_msg(1, dr_lang('更新完成'));
        }

        $mdir = $array[$page-1];

        // 过滤共享栏目中的其他模块数据
        $cats = \Phpcmf\Service::L('category', 'module')->get_category('share');
        $CAT = $cats;
        $MAIN = [];
        foreach ($CAT as $i => $t) {
            if ($t['tid'] == 1) {
                // 模块栏目
                if (!$t['child'] && $t['mid'] != $mdir) {
                    unset($CAT[$i]);
                }
            } elseif ($t['tid'] == 0) {
                // 单页
                if (!$t['child']) {
                    unset($CAT[$i]);
                }
            } else {
                // 外链
                unset($CAT[$i]);
            }

            // 验证mid
            if ($CAT[$t['id']]['catids']) {
                $mid = [];
                $catids = $CAT[$t['id']]['catids'];
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
            if ($t['ismain']) {
                if ($t['mid'] != $mdir) {
                   // 排除主栏目
                } else {
                    $MAIN[$t['id']] = $t;
                }
            }
        }
        \Phpcmf\Service::L('cache')->set_file(
            'cache',
            $CAT,
            'module/category-' . SITE_ID . '-' . $mdir . '-data'
        );
        \Phpcmf\Service::L('cache')->set_file(
            'main',
            $MAIN,
            'module/category-' . SITE_ID . '-' . $mdir . '-data'
        );

        $this->_html_msg( 1, dr_lang('正在更新共享模块【%s】...', count($array)."/$page"),
            $url.'&&page='.($page+1)
        );
    }

    /**
     * 生成静态时的跳转提示
     */
    protected function _html_msg($code, $msg, $url = '', $note = '') {

        if (\Phpcmf\Service::L('input')->get('is_ajax')) {
            $this->_json($code, $msg, $url);
        }

        \Phpcmf\Service::V()->assign([
            'msg' => $msg,
            'url' => $url,
            'note' => $note,
            'mark' => $code
        ]);
        \Phpcmf\Service::V()->display('html_msg.html', 'admin');exit;
    }
}
