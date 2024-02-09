<?php namespace Phpcmf\Admin;
/**
 * {{www.xunruicms.com}}
 * {{迅睿内容管理框架系统}}
 * 本文件是框架系统文件，二次开发时不可以修改本文件，可以通过继承类方法来重写此文件
 **/

// 内容模块操作类 基于 Table
class Module extends \Phpcmf\Table {

    protected $post_time; // 定时发布时间
    protected $module_menu; // 是否显示模块菜单
    protected $is_post_user; // 投稿者权限
    protected $where_list_sql;

    // 上级公共类
    public function __construct() {
        parent::__construct();
        $this->fix_admin_tpl_path = dr_get_app_dir('module').'Views/';
        \Phpcmf\Service::V()->admin($this->admin_tpl_path, $this->fix_admin_tpl_path);
        $this->_Extend_Init();
    }

    // 继承类初始化
    protected function _Extend_Init() {
        // 初始化模块
        $this->_module_init(APP_DIR);
        // 支持附表存储
        $this->is_data = 1;
        // 是否支持模块索引表
        $this->is_module_index = 1;
        // 是否支持
        $this->is_category_data_field = $this->module['category_data_field'] ? 1 : 0;
        // 模板前缀(避免混淆)
        $this->tpl_prefix = 'share_';
        // 单独模板命名
        $this->tpl_name = APP_DIR;
        // 模块显示名称
        $this->name = dr_lang('内容模块【%s（%s）】', $this->module['cname'], APP_DIR);
        $this->is_post_user = \Phpcmf\Service::M('auth')->is_post_user();
        $this->where_list_sql = $this->content_model->get_admin_list_where();
        // 初始化数据表
        $this->_init([
            'table' => dr_module_table_prefix(APP_DIR),
            'field' => $this->module['field'],
            'sys_field' => ['inputtime', 'updatetime', 'inputip', 'displayorder', 'hits', 'uid'],
            'date_field' => $this->module['setting']['search_time'] ? $this->module['setting']['search_time'] : 'updatetime',
            'show_field' => 'title',
            'where_list' => $this->where_list_sql,
            'order_by' => dr_safe_replace($this->module['setting']['order']),
            'list_field' => $this->module['setting']['list_field'],
            'search_first_field' => $this->module['setting']['search_first_field'] ? $this->module['setting']['search_first_field'] : 'title',
        ]);
        $this->content_model->init($this->init); // 初始化内容模型
        // 子管理员推荐位权限
        if (!dr_in_array(1, $this->admin['roleid']) && $this->module['setting']['flag']) {
            foreach ($this->module['setting']['flag'] as $i => $t) {
                if (!$t['role']) {
                    unset($this->module['setting']['flag'][$i]);
                    continue;
                } elseif (dr_array_intersect_key($this->admin['roleid'], $t['role'])) {
                    continue;
                } else {
                    unset($this->module['setting']['flag'][$i]);
                }
            }
        }
        $is_right_field = 1;
        if (isset($this->module['setting']['right_field']) && $this->module['setting']['right_field']) {
            if ($this->module['setting']['right_field'] == 2) {
                $is_right_field = 0;
            } elseif (!dr_in_array(1, $this->admin['roleid'])) {
                $is_right_field = 0;
            }
        }
        // 写入模板
        \Phpcmf\Service::V()->assign([
            'field' => $this->init['field'],
            'module' => $this->module,
            'post_url' => \Phpcmf\Service::L('Router')->url(APP_DIR.'/home/add', ['catid' => intval($_GET['catid'])]),
            'is_post_user' => $this->is_post_user,
            'is_hcategory' => $this->is_hcategory,
            'is_right_field' => $is_right_field,
            'is_category_show' => $this->is_hcategory ? 0 : 1,
        ]);

        if ($this->module['setting']['is_hide_search_bar']) {
            $this->is_show_search_bar = 0;
        }
    }

    // ========================

    // 生成内容静态
    public function scjt_edit() {

        if (!dr_is_app('chtml')) {
            $this->_json(0, '没有安装官方版【静态生成】插件');
        }

        $ids = \Phpcmf\Service::L('input')->get_post_ids();
        if (!$ids) {
            $this->_json(0, dr_lang('没有选择任何栏目'));
        }

        $this->_json(1, dr_url('chtml/html/show_index', ['app' => APP_DIR, 'ids' => implode(',', $ids)]));
    }

    // 后台查看列表
    protected function _Admin_List($rt = 0) {

        $this->is_ajax_list = true;
        $this->fix_table_list && $this->is_ajax_list = false;

        if (dr_is_app('fstatus') && $this->module['field']['fstatus'] && $this->init['list_field']) {
            $this->init['list_field']['fstatus'] = [
                'use' => 1,
                'order' => 1,
                'width' => 60,
                'func' => 'fstatus',
                'name' => dr_lang('状态'),
            ];
        }

        // 判断栏目分表
        if ($this->module['is_ctable']) {
            $catid = intval(\Phpcmf\Service::L('input')->get('catid'));
            if ($catid) {
                $this->init['table'] = dr_module_ctable(dr_module_table_prefix(APP_DIR), dr_cat_value($catid));
            }
        }

        list($tpl, $data) = $this->_List([]);

        $category_select = \Phpcmf\Service::L('category', 'module')->select(
            $this->module['dirname'],
            $data['param']['catid'],
            'name="catid"',
            dr_lang('全部'),
            0, 1
        );

        $category_move = \Phpcmf\Service::L('category', 'module')->clearbs(1)->select(
            $this->module['dirname'],
            $data['param']['catid'],
            'name="catid"',
            dr_lang('请选择'),
            isset($this->module['setting']['pcatpost']) && $this->module['setting']['pcatpost'] ? 0 : 1, 1
        );

        $clink = $cbottom = [];

        $this->mytable = [
            'foot_tpl' => '',
            'link_tpl' => '',
            'link_var' => 'html = html.replace(/\{id\}/g, row.id);
            html = html.replace(/\{cid\}/g, row.id);
            html = html.replace(/\{mid\}/g, "'.APP_DIR.'");',
        ];
        if ($this->_is_admin_auth('del') || $this->_is_admin_auth('edit')) {
            $this->mytable['foot_tpl'].= '<label class="table_select_all"><input onclick="dr_table_select_all(this)" type="checkbox"><span></span></label>';
        }
        if ($this->_is_admin_auth('del')) {
            $this->mytable['foot_tpl'].= '<label><button type="button" onclick="dr_module_delete()" class="btn red btn-sm"> <i class="fa fa-trash"></i> '.dr_lang('删除').'</button></label>';
        }
        if ($this->_is_admin_auth('edit')) {
            $this->mytable['link_tpl'].= '<label><a href="'.dr_url(APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/edit').'&id={id}" class="btn btn-xs red"> <i class="fa fa-edit"></i> '.dr_lang('修改').'</a></label>';
            if ($this->module['form']) {
                foreach ($this->module['form'] as $a) {
                    if ($this->_is_admin_auth(APP_DIR.'/'.$a['table'].'/index')) {
                        $this->mytable['link_tpl'].= '<label><a class="btn blue btn-xs" href="'.dr_url(APP_DIR.'/'.$a['table'].'/index').'&cid={cid}"><i class="'.dr_icon($a['setting']['icon']).'"></i> '.dr_lang($a['name']);
                        if (\Phpcmf\Service::M()->is_field_exists($this->init['table'], $a['table'].'_total')) {
                            $this->mytable['link_tpl'].= '（{'.$a['table'].'_total}）';
                            $this->mytable['link_var'].= 'html = html.replace(/\{'.$a['table'].'_total\}/g, row.'.$a['table'].'_total);';
                        }
                        $this->mytable['link_tpl'].= '</a></label>';
                    }
                }
            }
            $clink = $this->_app_clink();
            if ($clink) {
                if ($this->module['setting']['is_op_more']) {
                    $aa = $this->mytable['link_tpl'];
                    $this->mytable['link_tpl'] = '';
                }
                foreach ($clink as $a) {
                    if ($a['model'] && $a['check']
                        && method_exists($a['model'], $a['check'])
                        && call_user_func(array($a['model'], $a['check']), APP_DIR, []) == 0) {
                        continue;
                    }
                    $this->mytable['link_tpl'].= ' <label><a class="btn '.$a['color'].' btn-xs" href="'.$a['url'].'"><i class="'.$a['icon'].'"></i> '.dr_lang($a['name']);
                    if ($a['field'] && \Phpcmf\Service::M()->is_field_exists($this->init['table'], $a['field'])) {
                        $this->mytable['link_tpl'].= '（{'.$a['field'].'}）';
                        $this->mytable['link_var'].= 'html = html.replace(/\{'.$a['field'].'\}/g, row.'.$a['field'].');';
                    }
                    $this->mytable['link_tpl'].= '</a></label>';
                }

                if ($this->module['setting']['is_op_more']) {
                    $this->mytable['link_tpl'] = $aa . '<div class="btn-group">
    <button class="btn btn-xs blue dropdown-toggle" style="margin-top: 2px" type="button" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" aria-expanded="false"> 
        <i class="fa fa-cog"></i> 
        ' . dr_lang('更多') . '
        <i class="fa fa-angle-down"></i>
    </button>
    <ul class="dropdown-menu" role="menu"> ' . str_replace(['label', 'btn'], ['li', ''], $this->mytable['link_tpl']) . '   </ul>
</div>';
                }
            }
            if (\Phpcmf\Service::V()->get_value('is_category_show')) {
                $this->mytable['foot_tpl'].= '<label style="margin-right:5px">'.$category_move.'</label>
                <label><button type="button" onclick="dr_ajax_option(\''.dr_url(APP_DIR.'/home/move_edit').'\', \''.dr_lang('你确定要更改栏目吗？').'\', 1)" class="btn green btn-sm"> <i class="fa fa-edit"></i> '.dr_lang('更改').'</button></label>';
            }
            $data = [];
            $data[] = [
                'icon' => 'fa fa-flag',
                'name' => dr_lang('推送到推荐位'),
                'uri' => APP_DIR.'/home/edit',
                'url' => 'javascript:;" onclick="dr_module_send(\''.dr_lang("推荐位").'\', \''.dr_url(APP_DIR.'/home/tui_edit').'&page=0\')',
            ];
            $data[] = [
                'icon' => 'fa fa-clock-o',
                'name' => dr_lang('更新时间'),
                'uri' => APP_DIR.'/home/edit',
                'url' => 'javascript:;" onclick="dr_module_send_ajax(\''.dr_url(APP_DIR.'/home/tui_edit').'&page=4\')',
            ];
            if (!$this->is_post_user) {
                $data[] = [
                    'icon' => 'fa fa-sign-out',
                    'name' => dr_lang('批量退稿'),
                    'uri' => APP_DIR.'/home/tuigao_all_edit',
                    'url' => 'javascript:;" onclick="dr_module_tuigao()',
                ];
            }
            if ($this->module['setting']['sync_category']) {
                $data[] = [
                    'icon' => 'fa fa-refresh',
                    'name' => dr_lang('发布到其他栏目'),
                    'uri' => APP_DIR.'/home/edit',
                    'url' => 'javascript:;" onclick="dr_module_send(\''.dr_lang("发布到其他栏目").'\', \''.dr_url(APP_DIR.'/home/tui_edit').'&page=1\')',
                ];
            }
            if (version_compare(CMF_VERSION, '4.6.0') < 0) {
                $cbottom = $this->_app_cbottom_fix('', $data);
            } else {
                $cbottom = $this->_app_cbottom('', $data);
            }

            if ($cbottom) {
                $this->mytable['foot_tpl'].= '<label>
                    <div class="btn-group dropdown">
                        <a class="btn  blue btn-sm dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" aria-expanded="false" href="javascript:;"> '.dr_lang('批量').'
                            <i class="fa fa-angle-down"></i>
                        </a>
                        <ul class="dropdown-menu">';
                foreach ($cbottom as $a) {
                    $this->mytable['foot_tpl'].= '<li>
                                <a href="'.str_replace(['{mid}', '{catid}'], [APP_DIR, $data['param']['catid']], urldecode($a['url'])).'"> <i class="'.$a['icon'].'"></i> '.dr_lang($a['name']).' </a>
                            </li>';
                }
                $this->mytable['foot_tpl'].= '
                           
                        </ul>
                    </div>
                </label>';
            }
        }



        \Phpcmf\Service::V()->assign([
            'menu' => $this->_module_menu(
                ' <i class="'.dr_icon($this->module['icon']).'"></i>  '.dr_lang('%s管理', $this->module['cname']),
                \Phpcmf\Service::L('Router')->url(APP_DIR.'/home/index'),
                \Phpcmf\Service::L('Router')->url(APP_DIR.'/home/add', ['catid' => intval($_GET['catid'])])
            ),
            'mytable' => $this->mytable,
            'category_move' => $category_move,
            'category_select' => $category_select,
            'clink' => $clink,
            'cbottom' => $cbottom,
        ]);
        if ($rt) {
            return $tpl;
        } else {
            return \Phpcmf\Service::V()->display($tpl);
        }
    }

    // 后台添加内容
    protected function _Admin_Add($rt = 0) {

        $id = 0;
        $did = intval(\Phpcmf\Service::L('input')->get('did'));
        $catid = intval(\Phpcmf\Service::L('input')->get('catid'));

        $did && $this->auto_save = 0; // 草稿数据时不加载
        $draft = $did ? $this->content_model->get_draft($did) : [];

        $catid = $draft['catid'] ? $draft['catid'] : $catid;

        // 栏目id不存在时就去第一个可用栏目为catid
        if (!$catid) {
            list($select, $catid) = \Phpcmf\Service::L('category', 'module')->select(
                $this->module['dirname'],
                $catid,
                'id=\'dr_catid\' name=\'catid\' onChange="show_category_field(this.value)"',
                '', isset($this->module['setting']['pcatpost']) && $this->module['setting']['pcatpost'] ? 0 : 1, 1, 1
            );;
        } else {
            $select = \Phpcmf\Service::L('category', 'module')->select(
                $this->module['dirname'],
                $catid,
                'id=\'dr_catid\' name=\'catid\' onChange="show_category_field(this.value)"',
                '', isset($this->module['setting']['pcatpost']) && $this->module['setting']['pcatpost'] ? 0 : 1, 1
            );
        }

        $this->is_get_catid = $catid;
        $draft && $draft['catid'] = $catid;

        list($tpl) = $this->_Post($id, $draft);

        \Phpcmf\Service::V()->assign([
            'did' => $did,
            'form' => dr_form_hidden(['is_draft' => 0, 'module' => MOD_DIR, 'id' => $id]),
            'menu' => $this->_module_menu(
                ' <i class="'.dr_icon($this->module['icon']).'"></i>  '.dr_lang('%s管理', $this->module['cname']),
                \Phpcmf\Service::L('Router')->url(APP_DIR.'/home/index'),
                \Phpcmf\Service::L('Router')->url(APP_DIR.'/home/add', ['catid' => $catid])
            ),
            'catid' => 0,
            'select' => $select,
            'is_flag' => $this->module['setting']['flag'],
            'draft_url' => \Phpcmf\Service::L('Router')->url(APP_DIR.'/home/add'),
            'draft_list' => $this->content_model->get_draft_list('cid='.$id),
            'category_field_url' => $this->module['category_data_field'] ? \Phpcmf\Service::L('Router')->url(APP_DIR.'/home/add') : '',
        ]);
        if ($rt) {
            return $tpl;
        } else {
            return \Phpcmf\Service::V()->display($tpl);
        }
    }

    // 后台修改内容
    protected function _Admin_Edit($rt = 0) {

        $id = intval(\Phpcmf\Service::L('input')->get('id'));
        $did = intval(\Phpcmf\Service::L('input')->get('did'));
        $did && $this->auto_save = 0; // 草稿数据时不加载
        $draft = $did ? $this->content_model->get_draft($did) : [];

        list($tpl, $data) = $this->_Post($id, $draft);
        if (!$data) {
            $this->_admin_msg(0, dr_lang('数据#%s不存在', $id));
        } elseif ($this->where_list_sql && $this->content_model->admin_is_edit($data)) {
            $this->_admin_msg(0, dr_lang('当前角色无权限管理此栏目'));
        }

        $clink_btn = '';
        if ($this->module['form']) {
            foreach ($this->module['form'] as $a) {
                if ($this->_is_admin_auth(APP_DIR.'/'.$a['table'].'/index')) {
                    $clink_btn.= '<label><a class="btn blue " href="'.dr_url(APP_DIR.'/'.$a['table'].'/index').'&cid={cid}"><i class="'.dr_icon($a['setting']['icon']).'"></i> '.dr_lang($a['name']);
                    if (\Phpcmf\Service::M()->is_field_exists($this->init['table'], $a['table'].'_total')) {
                        $clink_btn.= '（{'.$a['table'].'_total}）';
                    }
                    $clink_btn.= '</a></label>'.PHP_EOL;
                }
            }
        }

        $clink = $this->_app_clink();
        if ($clink) {
            foreach ($clink as $a) {
                if ($a['model'] && $a['check']
                    && method_exists($a['model'], $a['check']) && call_user_func(array($a['model'], $a['check']), APP_DIR, []) == 0) {
                    continue;
                }
                $clink_btn.= ' <label><a class="btn '.$a['color'].' btn" href="'.$a['url'].'"><i class="'.$a['icon'].'"></i> '.dr_lang($a['name']);
                if ($a['field'] && \Phpcmf\Service::M()->is_field_exists($this->init['table'], $a['field'])) {
                    $clink_btn.= '（'.$data[$a['field']].'）';
                }
                $clink_btn.= '</a></label>'.PHP_EOL;
            }
        }

        if ($clink_btn) {
            $data['cid'] = $id;
            $data['mid'] = MOD_DIR;
            foreach ($data as $var => $t) {
                if (!is_array($t)) {
                    $clink_btn = str_replace('{'.$var.'}', (string)$t, $clink_btn);
                }
            }
        }

        \Phpcmf\Service::V()->assign([
            'did' => $did,
            'form' => dr_form_hidden(['is_draft' => 0, 'module' => MOD_DIR, 'id' => $id]),
            'menu' => $this->_module_menu(
                ' <i class="'.dr_icon($this->module['icon']).'"></i>  '.dr_lang('%s管理', $this->module['cname']),
                \Phpcmf\Service::L('Router')->url(APP_DIR.'/home/index'),
                \Phpcmf\Service::L('Router')->url(APP_DIR.'/home/add', ['catid' => $data['catid']])
            ),
            'is_flag' => $this->module['setting']['flag'],
            'select' => \Phpcmf\Service::L('category', 'module')->select(
                $this->module['dirname'],
                $data['catid'],
                'id=\'dr_catid\' name=\'catid\' onChange="show_category_field(this.value)"',
                '', isset($this->module['setting']['pcatpost']) && $this->module['setting']['pcatpost'] ? 0 : 1, 1
            ),
            'clink_btn' => $clink_btn,
            'web_url' => $data['url'] ? dr_url_prefix($data['url'], APP_DIR) : '',
            'draft_url' => \Phpcmf\Service::L('Router')->url(APP_DIR.'/home/edit', ['id' => $id]),
            'draft_list' => $this->content_model->get_draft_list('cid='.$id),
            'category_field_url' => $this->module['category_data_field'] ?\Phpcmf\Service::L('Router')->url(APP_DIR.'/home/edit', ['id' => $id]) : ''
        ]);
        if ($rt) {
            return $tpl;
        } else {
            return \Phpcmf\Service::V()->display($tpl);
        }
    }

    // 后台删除内容
    protected function _Admin_Del() {

        if (IS_POST) {

            $ids = \Phpcmf\Service::L('input')->get_post_ids();
            if (!$ids) {
                $this->_json(0, dr_lang('没有选择内容'));
            }

            $rt = $this->content_model->delete_to_recycle($ids, \Phpcmf\Service::L('input')->post('note'));
            if ($rt['code']) {
                // 写入日志
                \Phpcmf\Service::M('cache')->update_data_cache();
                \Phpcmf\Service::L('input')->system_log($this->name.'：放入回收站id ('.implode(', ', $ids).')');
                $this->_json(1, dr_lang('所选内容已被放入回收站中'));
            } else {
                $this->_json(0, $rt['msg']);
            }
        } else {
            // 选择选项
            $ids = $_GET['ids'];
            if (!$ids) {
                $this->_json(0, dr_lang('没有选择内容'));
            }
            \Phpcmf\Service::V()->assign([
                'ids' => $ids,
                'delete_msg' => $this->module['setting']['delete_msg'] ? explode(PHP_EOL, (string)$this->module['setting']['delete_msg']) : [],
            ]);
            \Phpcmf\Service::V()->display('share_delete.html');
            exit;
        }
    }

    // 后台批量保存排序值
    protected function _Admin_Order() {
        $this->_json(0, '此功能已经失效，请在模块设置中配置后台显示本字段');
    }

    // 批量移动栏目
    protected function _Admin_Move() {

        $ids = \Phpcmf\Service::L('input')->get_post_ids();
        $catid = (int)\Phpcmf\Service::L('input')->post('catid');
        if (!$ids) {
            $this->_json(0, dr_lang('选择内容不存在'));
        } elseif (!$catid) {
            $this->_json(0, dr_lang('目标栏目未选择'));
        } elseif (!$this->content_model->admin_category_auth($catid, 'edit')) {
            $this->_json(0, dr_lang('无权限操作此栏目'));
        } elseif ($this->where_list_sql && $this->content_model->admin_is_edit(['catid' => $catid])) {
            $this->_json(0, dr_lang('当前角色无权限管理此栏目'));
        }

        $rt = $this->content_model->move_category($ids, $catid);

        // 写入日志
        if ($rt['code']) {
            \Phpcmf\Service::L('input')->system_log($this->name.'：批量修改栏目id ('.implode(', ', $ids).')');
            $this->_json(1, dr_lang('操作成功'));
        }
        $this->_json(0, $rt['msg']);
    }

    // 同步栏目选择器
    protected function _Admin_Syncat() {

        $sync = \Phpcmf\Service::L('input')->get('catid');

        if (IS_AJAX_POST) {

            $catid = \Phpcmf\Service::L('input')->post('catid');
            $catid = dr_string2array($catid);
            if (!$catid) {
                $this->_json(0, dr_lang('你没有选择同步的栏目'));
            }

            $syncat = [];
            foreach ($catid as $i) {
                if ($this->where_list_sql && $this->content_model->admin_is_edit(['catid' => $i])) {
                    $this->_json(0, dr_lang('当前角色无权限管理此栏目'));
                }
                $cat = dr_cat_value($this->module['mid'], $i);
                if (!$cat) {
                    continue;
                } elseif ($cat['tid'] != 1) {
                    continue;
                } elseif (!$cat['is_post']) {
                    continue;
                } else {
                    $syncat[] = $i;
                }
            }

            if (!$syncat) {
                $this->_json(0, dr_lang('所选栏目无效'));
            }

            $this->_json(1, dr_count($syncat), implode(',', $syncat));
        }

        \Phpcmf\Service::V()->assign([
            'form' =>  dr_form_hidden(),
            'select' => \Phpcmf\Service::L('category', 'module')->select(
                $this->module['dirname'],
                $sync ? explode(',', $sync) : 0,
                'id=\'dr_catid\' name=\'catid[]\' multiple="multiple"  data-actions-box="true"',
                '', isset($this->module['setting']['pcatpost']) && $this->module['setting']['pcatpost'] ? 0 : 1, 1
            ),
        ]);
        \Phpcmf\Service::V()->display('share_syncat.html');exit;
    }

    // 批量推送
    protected function _Admin_Send() {

        $page = (int)\Phpcmf\Service::L('input')->get('page');
        if ($page != 5) {
            $ids = \Phpcmf\Service::L('input')->get('ids');
            if (!$ids) {
                $this->_json(0, dr_lang('所选数据不存在'));
            }
        }

        if (IS_AJAX_POST) {

            $in = [];
            foreach ($ids as $i) {
                $i && $in[] = intval($i);
            }

            if (!$in) {
                $this->_json(0, dr_lang('所选数据不存在'));
            }

            switch ($page) {

                case 1: // 推送到其他栏目

                    $catids = \Phpcmf\Service::L('input')->post('catid');
                    $catids = dr_string2array($catids);
                    if (!$catids) {
                        $this->_json(0, dr_lang('你还没有选择同步的栏目'));
                    }

                    $data = \Phpcmf\Service::M()->db->table($this->init['table'])->whereIn('id', $in)->where('link_id<=0')->get()->getResultArray();
                    if (!$data) {
                        $this->_json(0, dr_lang('没有可用数据'));
                    }

                    $c = 0;
                    foreach ($data as $t) {
                        $u = 0;
                        foreach ($catids as $catid) {
                            if ($catid && $catid != $t['catid']) {
                                if ($this->where_list_sql && $this->content_model->admin_is_edit(['catid' => $catid])) {
                                    $this->_json(0, dr_lang('当前角色无权限管理此栏目'));
                                }
                                // 插入到同步栏目中
                                $new[1] = $t;
                                $new[1]['catid'] = $catid;
                                $new[1]['link_id'] = $t['id'];
                                $new[1]['tableid'] = 0;
                                $new[1]['id'] = $this->content_model->index(0, $new);
                                if ($new[1]['id']) {
                                    $this->content_model->table($this->init['table'])->replace($new[1]);
                                    $c ++;
                                    $u = 1;
                                }
                            }
                        }
                        $u && $this->content_model->table($this->init['table'])->update($t['id'], ['link_id' => -1]);
                    }

                    $this->_json(1, dr_lang('批量执行%s条', $c));

                    break;

                case 0: // 推荐位

                    $flag = \Phpcmf\Service::L('input')->post('flag');
                    $clear = \Phpcmf\Service::L('input')->post('clear');
                    if (!$clear && !$flag) {
                        $this->_json(0, dr_lang('你还没有选择推荐位'));
                    }

                    if ($clear) {
                        \Phpcmf\Service::M()->db->table($this->init['table'].'_flag')->whereIn('id', $in)->delete();
                        $this->_json(1, dr_lang('推荐位清除成功'));
                    }

                    $data = \Phpcmf\Service::M()->db->table($this->init['table'].'_index')->whereIn('id', $in)->get()->getResultArray();
                    if (!$data) {
                        $this->_json(0, dr_lang('所选数据不存在'));
                    }

                    $c = 0;
                    foreach ($data as $t) {
                        foreach ($flag as $fid) {
                            $this->content_model->insert_flag((int)$fid, (int)$t['id'], (int)$t['uid'], (int)$t['catid']);
                            $c ++;
                        }
                    }

                    $this->_json(1, dr_lang('批量执行%s条', $c));
                    break;

                case 6:
                    // 退稿
                    $note = \Phpcmf\Service::L('input')->post('note');
                    if (!$note) {
                        $this->_json(0, dr_lang('退稿理由未填写'));
                    }

                    $clear = \Phpcmf\Service::L('input')->post('clear');

                    // 批量操作
                    if (!$in) {
                        $this->_json(0, dr_lang('没有选中内容'));
                    } elseif ($this->is_post_user) {
                        $this->_json(0, dr_lang('投稿者身份不允许退稿操作'));
                    }
                    $num = 10;
                    if (isset($this->module['setting']['verify_num']) && $this->module['setting']['verify_num'])  {
                        $num = max(1, intval($this->module['setting']['verify_num']));
                    }
                    if (dr_count($in) > $num) {
                        $this->_json(0, dr_lang('批量选择数量不能超过%s个', $num));
                    }

                    $p = [
                        'clear' => $clear,
                        'note' => $note,
                        'page' => 7,
                    ];

                    $url = dr_url(MOD_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/tui_edit', $p);
                    foreach ($in as $i) {
                        $url.='&ids[]='. $i;
                    }

                    $this->_json(1, $url);

                    break;
            }
            exit;
        } elseif ($page == 7) {

            $note = \Phpcmf\Service::L('input')->get('note');
            if (!$note) {
                $this->_json(0, dr_lang('退稿理由未填写'));
            }

            $clear = \Phpcmf\Service::L('input')->get('clear');

            // 批量操作
            if (!$ids) {
                $this->_json(0, dr_lang('没有选中内容'));
            } elseif ($this->is_post_user) {
                $this->_json(0, dr_lang('投稿者身份不允许退稿操作'));
            }
            $num = 10;
            if (isset($this->module['setting']['verify_num']) && $this->module['setting']['verify_num'])  {
                $num = max(1, intval($this->module['setting']['verify_num']));
            }
            if (dr_count($ids) > $num) {
                $this->_json(0, dr_lang('批量选择数量不能超过%s个', $num));
            }

            $list = [];
            foreach ($ids as $id) {
                $id = intval($id);
                if (!$id) {
                    continue;
                }
                $row = \Phpcmf\Service::M()->table(dr_module_table_prefix(APP_DIR))->get($id);
                if (!$row) {
                    $this->_json(0, dr_lang('选中内容[#%s]不存在', $id));
                }
                $url = dr_url(MOD_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/tuigao_edit').'&is_verify_iframe=1&clear='.$clear.'&note='.$note.'&id='.$id;
                $t = dr_string2array($row);
                $list[$id] = [
                    'url' => $url,
                    'title' => $t['title'],
                    'preview' => dr_url_prefix('/index.php?s='.MOD_DIR.'&c=show&id='.$row['id']),
                ];
            }

            \Phpcmf\Service::V()->assign([
                'list' => $list,
                'back_url' => dr_url(MOD_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/index').'&rand='.SYS_TIME,
            ]);
            \Phpcmf\Service::V()->display('share_list_verify_all.html');exit;
        } elseif ($page == 4) {
            $this->content_model->update_time($ids);
            $this->_json(1, dr_lang('操作成功'));
            exit;
        }

        \Phpcmf\Service::V()->assign([
            'ids' => $ids,
            'page' => $page,
            'form' => dr_form_hidden(),
            'select' => \Phpcmf\Service::L('category', 'module')->select(
                $this->module['dirname'],
                0,
                'id=\'dr_catid\' name=\'catid[]\' multiple="multiple" style="height:200px"',
                '', isset($this->module['setting']['pcatpost']) && $this->module['setting']['pcatpost'] ? 0 : 1, 1
            ),
        ]);
        \Phpcmf\Service::V()->display('share_send.html');exit;
    }

    // ===========================

    // 后台查看草稿列表
    protected function _Admin_Draft_List($rt = 0) {

        if (isset($this->field['content'])) {
            $this->field['content']['fieldtype'] = 'Text'; // 防止识别为编辑器字段被转义
        }

        $this->_init([
            'table' => dr_module_table_prefix(APP_DIR).'_draft',
            'date_field' => 'inputtime',
            'order_by' => 'inputtime desc',
            'where_list' => 'uid='.$this->uid,
        ]);

        list($tpl, $data) = $this->_List();

        \Phpcmf\Service::V()->assign([
            'menu' => $this->_module_menu(
                ' <i class="'.dr_icon('fa fa-pencil').'"></i>  '.dr_lang('草稿箱管理'),
                \Phpcmf\Service::L('Router')->url(APP_DIR.'/draft/index'),
                \Phpcmf\Service::L('Router')->url(APP_DIR.'/home/add')
            ),
            'category_select' => \Phpcmf\Service::L('category', 'module')->select(
                $this->module['dirname'],
                $data['param']['catid'],
                'name="catid"',
                '--',isset($this->module['setting']['pcatpost']) && $this->module['setting']['pcatpost'] ? 0 : 1
            ),
        ]);

        $tpl = $this->_tpl_filename('list_draft');
        if ($rt) {
            return $tpl;
        } else {
            return \Phpcmf\Service::V()->display($tpl);
        }
    }

    // 后台删除草稿内容
    protected function _Admin_Draft_Del() {

        // 支持附表存储
        $this->is_data = 0;
        $this->name.= dr_lang('草稿');
        $this->_init([
            'table' => dr_module_table_prefix(APP_DIR).'_draft',
        ]);

        $this->_Del(\Phpcmf\Service::L('input')->get_post_ids());
    }

    // ===========================

    // 后台查看审核列表
    protected function _Admin_Verify_List($rt = 0) {

        // 说明来自审核页面
        define('IS_MODULE_VERIFY', 1);

        $is_post_user = \Phpcmf\Service::M('auth')->is_post_user();

        if (isset($_GET['is_all']) && intval($_GET['is_all']) == 1) {

            if (IS_POST) {
                $this->_json(1, dr_lang('操作成功'));
            }

            // 批量操作
            $ids = \Phpcmf\Service::L('input')->get('ids');
            if (!$ids) {
                $this->_json(0, dr_lang('没有选中内容'));
            } elseif ($is_post_user) {
                $this->_json(0, dr_lang('投稿者身份不允许审核操作'));
            }
            $num = 10;
            if (isset($this->module['setting']['verify_num']) && $this->module['setting']['verify_num'])  {
                $num = max(1, intval($this->module['setting']['verify_num']));
            }
            if (dr_count($ids) > $num) {
                $this->_json(0, dr_lang('批量选择数量不能超过%s个', $num));
            }

            $list = [];
            $note = \Phpcmf\Service::L('input')->get('note');
            foreach ($ids as $id) {
                $row = \Phpcmf\Service::M()->table(dr_module_table_prefix(APP_DIR).'_verify')->get($id);
                if (!$row) {
                    $this->_json(0, dr_lang('选中内容[#%s]不存在', $id));
                }
                if (intval($_GET['at']) == 1) {
                    $url = dr_url(MOD_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/edit').'&is_verify_iframe=1&id='.$id;
                } else {
                    if (!$note) {
                        $this->_json(0, dr_lang('没有填写审核说明'));
                    }
                    $url = dr_url(MOD_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/edit').'&is_verify_iframe=1&note='.$note.'&id='.$id;
                }
                $t = dr_string2array($row['content']);
                $list[$id] = [
                    'url' => $url,
                    'title' => $t['title'],
                    'preview' => dr_url_prefix('/index.php?s='.MOD_DIR.'&c=show&id='.$row['id']),
                ];
            }

            \Phpcmf\Service::V()->assign([
                'list' => $list,
                'back_url' => dr_url(MOD_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/index').'&rand='.SYS_TIME,
            ]);
            \Phpcmf\Service::V()->display('share_list_verify_all.html');
            exit;
        }

        if (isset($this->field['content'])) {
            $this->field['content']['fieldtype'] = 'Text'; // 防止识别为编辑器字段被转义
        }
        $this->_init([
            'table' => dr_module_table_prefix(APP_DIR).'_verify',
            'date_field' => 'inputtime',
            'order_by' => 'inputtime desc',
            'where_list' => $this->content_model->get_admin_list_verify_where($this->where_list_sql),
        ]);

        list($tpl, $data) = $this->_List();

        $verify_msg = [
            dr_lang('词文不对'),
        ];
        if ($this->module['setting']['verify_msg']) {
            $msg = explode(PHP_EOL, $this->module['setting']['verify_msg']);
            $msg && $verify_msg = $msg;
        }

        \Phpcmf\Service::V()->assign([
            'menu' => $this->_module_menu(
                ' <i class="'.dr_icon('fa fa-edit').'"></i>  '.dr_lang('待审核管理'),
                \Phpcmf\Service::L('Router')->url(APP_DIR.'/verify/index'),
                \Phpcmf\Service::L('Router')->url(APP_DIR.'/home/add')
            ),
            'clink' => $this->_app_clink(),
            'verify_msg' => $verify_msg,
            'is_post_user' => $is_post_user,
            'category_select' => \Phpcmf\Service::L('category', 'module')->select(
                $this->module['dirname'],
                $data['param']['catid'],
                'name="catid"',
                '--',isset($this->module['setting']['pcatpost']) && $this->module['setting']['pcatpost'] ? 0 : 1
            ),
        ]);

        $tpl = $this->_tpl_filename('list_verify');
        if ($rt) {
            return $tpl;
        } else {
            return \Phpcmf\Service::V()->display($tpl);
        }
    }

    // 后台修改审核内容
    protected function _Admin_Verify_Edit($rt = 0) {

        // 说明来自审核页面
        define('IS_MODULE_VERIFY', 1);

        $id = intval(\Phpcmf\Service::L('input')->get('id'));
        list($tpl, $data) = $this->_Post($id);
        if (!$data['id']) {
            // 删除审核提醒
            \Phpcmf\Service::M('member')->delete_admin_notice(APP_DIR.'/verify/edit:id/'.$id, SITE_ID);
            $this->_admin_msg(0, dr_lang('审核内容不存在'));
        } elseif ($this->where_list_sql && $this->content_model->admin_is_edit($data)) {
            $this->_admin_msg(0, dr_lang('当前角色无权限管理此栏目'));
        } elseif (!$this->_get_verify_status_edit($data['verify']['vid'], $data['status'])) {
            $this->_admin_msg(0, dr_lang('当前角色无权限审核此内容'));
        }

        $step = $this->_get_verify($data['verify']['vid']);
        $verify_msg = [
            dr_lang('词文不对'),
        ];
        if ($this->module['setting']['verify_msg']) {
            $msg = explode(PHP_EOL, $this->module['setting']['verify_msg']);
            $msg && $verify_msg = $msg;
        }

        $next = dr_count($step) - 1 <= $data['status'] ? 9 : $data['status'] + 1;

        \Phpcmf\Service::V()->assign([
            'menu' => \Phpcmf\Service::M('auth')->_admin_menu(
                [
                    '审核管理' => [MOD_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/index', 'fa fa-edit'],
                    '审核处理' => ['hide:'.MOD_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/edit', 'fa fa-edit'],
                ]
            ),
            'form' =>  dr_form_hidden(['is_draft' => 0, 'module' => MOD_DIR, 'id' => $id]),
            'select' => \Phpcmf\Service::L('category', 'module')->select(
                $this->module['dirname'],
                $data['catid'],
                'id=\'dr_catid\' name=\'catid\' onChange="show_category_field(this.value)"',
                '', isset($this->module['setting']['pcatpost']) && $this->module['setting']['pcatpost'] ? 0 : 1, 1
            ),
            'is_flag' => $next == 9 ? $this->module['setting']['flag'] : [],
            'web_url' => dr_url_prefix('index.php?s='.APP_DIR.'&c=show&m=verify&id='.$id, APP_DIR),
            'is_verify' => 1,
            'back_note' => \Phpcmf\Service::L('input')->get('note'),
            'verify_msg' => $verify_msg,
            'verify_step' => $step,
            'is_sync_cat' => $data['sync_cat'],
            'verify_next' => $next,
        ]);

        $tpl = $this->_tpl_filename('post');
        if ($rt) {
            return $tpl;
        } else {
            return \Phpcmf\Service::V()->display($tpl);
        }
    }

    // 后台删除审核内容
    protected function _Admin_Verify_Del() {

        // 支持附表存储
        $this->is_data = 0;
        $this->name.= dr_lang('审核');
        $this->_init([
            'table' => dr_module_table_prefix(APP_DIR).'_verify',
        ]);
        $this->_Del(\Phpcmf\Service::L('input')->get_post_ids(), function ($rows) {
            foreach ($rows as $t) {
                if ($this->where_list_sql && $this->content_model->admin_is_edit($t)) {
                    return dr_return_data(0, dr_lang('当前角色无权限管理此栏目'));
                }
            }
            return dr_return_data(1, 'ok');
        }, function($rows) {
            foreach ($rows as $t) {
                // 删除索引
                $t['isnew'] && \Phpcmf\Service::M()->table(dr_module_table_prefix(APP_DIR).'_index')->delete($t['id']);
                // 删除审核提醒
                \Phpcmf\Service::M('member')->delete_admin_notice(APP_DIR.'/verify/edit:id/'.$t['id'], SITE_ID);
            }
            return dr_return_data(1, 'ok');
        });
    }

    // ===========================

    // 后台定时发布列表
    protected function _Admin_Time_List($rt = 0) {

        if (isset($this->field['content'])) {
            $this->field['content']['fieldtype'] = 'Text'; // 防止识别为编辑器字段被转义
        }

        $this->_init([
            'table' => dr_module_table_prefix(APP_DIR).'_time',
            'order_by' => 'inputtime desc',
            'date_field' => 'inputtime',
            'where_list' => $this->where_list_sql,
        ]);

        list($tpl, $data) = $this->_List();

        \Phpcmf\Service::V()->assign([
            'menu' => $this->_module_menu(
                ' <i class="'.dr_icon('fa fa-clock-o').'"></i>  '.dr_lang('待发布管理'),
                \Phpcmf\Service::L('Router')->url(APP_DIR.'/time/index'),
                \Phpcmf\Service::L('Router')->url(APP_DIR.'/home/add')
            ),
            'category_select' => \Phpcmf\Service::L('category', 'module')->select(APP_DIR, $data['param']['catid']),
        ]);

        $tpl = $this->_tpl_filename('list_time');
        if ($rt) {
            return $tpl;
        } else {
            return \Phpcmf\Service::V()->display($tpl);
        }
    }

    // 后台退稿
    public function tuigao_edit() {
        // 说明来退稿页面
        define('IS_MODULE_TG', 1);
        $id = intval(\Phpcmf\Service::L('input')->get('id'));
        list($tpl, $data) = $this->_Post($id);
        if (isset($_GET['is_verify_iframe'])) {
            \Phpcmf\Service::V()->assign([
                'form' =>  dr_form_hidden(['is_draft' => 0, 'module' => MOD_DIR, 'id' => $id]),
                'select' => \Phpcmf\Service::L('category', 'module')->select(
                    $this->module['dirname'],
                    $data['catid'],
                    'id=\'dr_catid\' name=\'catid\' onChange="show_category_field(this.value)"',
                    '', isset($this->module['setting']['pcatpost']) && $this->module['setting']['pcatpost'] ? 0 : 1, 1
                ),
                'web_url' => dr_url_prefix('index.php?s='.APP_DIR.'&c=show&m=verify&id='.$id, APP_DIR),
            ]);
            \Phpcmf\Service::V()->display($this->_tpl_filename('post'));
        } else {
            $this->_json(1, dr_lang('操作异常'));
            exit;
        }

    }

    // 后台定时发布
    protected function _Admin_Time_Add($rt = 0) {

        $at = \Phpcmf\Service::L('input')->get('at');
        if ($at == 'post') {
            // 批量发布
            $ids = \Phpcmf\Service::L('input')->get_post_ids();
            if (!$ids) {
                $this->_json(0, dr_lang('还没有选择呢'));
            }
            $html = [];
            foreach ($ids as $id) {
                $rt = $this->content_model->post_time(\Phpcmf\Service::M()->table(dr_module_table_prefix(APP_DIR).'_time')->get($id));
                $rt['data'] && $html[] = $rt['data'];
                if (!$rt['code']) {
                    $this->_json(0, $rt['msg'], ['htmlfile' => $html]);
                }
            }
            $this->_json(1, dr_lang('操作成功'), ['htmlfile' => $html]);
            exit;
        }

        // 说明来自定时页面
        define('IS_MODULE_TIME', 1);

        $this->_Post();

        $tpl = 'share_time.html';
        if ($rt) {
            return $tpl;
        } else {
            return \Phpcmf\Service::V()->display($tpl);
        }
    }

    // 后台修改定时时间
    public function time_edit() {

        // 说明来自定时页面
        define('IS_MODULE_TIME', 1);
        $id = intval(\Phpcmf\Service::L('input')->get('id'));
        $data = \Phpcmf\Service::M()->table(dr_module_table_prefix(APP_DIR).'_time')->get($id);
        if (!$data) {
            $this->_admin_msg(0, dr_lang('内容不存在'));
        }

        if (IS_POST) {
            $time = strtotime(\Phpcmf\Service::L('input')->post('posttime'));
            if ($time < SYS_TIME) {
                $this->_json(0, dr_lang('定时时间不能晚于当前时间'));
            }

            \Phpcmf\Service::M()->table(dr_module_table_prefix(APP_DIR).'_time')->update($id, [
                'posttime' => $time
            ]);
            $this->_json(1, dr_lang('操作成功'));
        }

        \Phpcmf\Service::V()->assign('form', dr_form_hidden());
        \Phpcmf\Service::V()->assign('posttime', dr_date($data['posttime'], 'Y-m-d H:i:s'));
        \Phpcmf\Service::V()->display('share_time.html');exit;
    }

    // 后台修改定时内容
    protected function _Admin_Time_Edit($rt = 0) {

        // 说明来自定时页面
        define('IS_MODULE_TIME', 1);
        $id = intval(\Phpcmf\Service::L('input')->get('id'));
        list($tpl, $data) = $this->_Post($id);
        if (!$data) {
            $this->_admin_msg(0, dr_lang('内容不存在'));
        }

        \Phpcmf\Service::V()->assign([
            'menu' => \Phpcmf\Service::M('auth')->_admin_menu(
                [
                    '定时发布' => [MOD_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/index', 'fa fa-clock-o"'],
                    '修改' => ['hide:'.MOD_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/edit', 'fa fa-edit'],
                ]
            ),
            'form' => dr_form_hidden(['is_draft' => 0, 'module' => MOD_DIR, 'id' => $id]),
            'select' => \Phpcmf\Service::L('category', 'module')->select(
                $this->module['dirname'],
                $data['catid'],
                'id=\'dr_catid\' name=\'catid\' onChange="show_category_field(this.value)"',
                '', isset($this->module['setting']['pcatpost']) && $this->module['setting']['pcatpost'] ? 0 : 1, 1
            ),
            'is_flag' => $this->module['setting']['flag'],
            'web_url' => dr_url_prefix('index.php?s='.APP_DIR.'&c=show&m=time&id='.$id, APP_DIR),
            'is_post_time' => 1,
        ]);

        $tpl = $this->_tpl_filename('post');
        if ($rt) {
            return $tpl;
        } else {
            return \Phpcmf\Service::V()->display($tpl);
        }
    }

    // 后台删除定时内容
    protected function _Admin_Time_Del() {

        // 支持附表存储
        $this->is_data = 0;
        $this->name.= dr_lang('定时');
        $this->_init([
            'table' => dr_module_table_prefix(APP_DIR).'_time',
        ]);

        $this->_Del(\Phpcmf\Service::L('input')->get_post_ids());
    }

    // ===========================

    // 后台查看回收站列表
    protected function _Admin_Recycle_List($rt = 0) {

        if (isset($this->field['content'])) {
            $this->field['content']['fieldtype'] = 'Text'; // 防止识别为编辑器字段被转义
        }

        $this->_init([
            'table' => dr_module_table_prefix(APP_DIR).'_recycle',
            'date_field' => 'inputtime',
            'order_by' => 'inputtime desc',
            'where_list' => $this->where_list_sql,
        ]);

        list($tpl, $data) = $this->_List();

        \Phpcmf\Service::V()->assign([
            'menu' => $this->_module_menu(
                ' <i class="'.dr_icon('fa fa-trash-o').'"></i>  '.dr_lang('回收站管理'),
                \Phpcmf\Service::L('Router')->url(APP_DIR.'/recycle/index'),
                \Phpcmf\Service::L('Router')->url(APP_DIR.'/home/add')
            ),
            'category_select' => \Phpcmf\Service::L('category', 'module')->select(
                $this->module['dirname'],
                $data['param']['catid'],
                'name="catid"',
                '--',isset($this->module['setting']['pcatpost']) && $this->module['setting']['pcatpost'] ? 0 : 1
            ),
        ]);

        $tpl = $this->_tpl_filename('list_recycle');
        if ($rt) {
            return $tpl;
        } else {
            return \Phpcmf\Service::V()->display($tpl);
        }
    }

    // 后台回收站清空
    public function recycle_del() {

        $page = (int)\Phpcmf\Service::L('input')->get('page');
        $psize = 20;
        if (!$page) {
            $nums = \Phpcmf\Service::M()->table(dr_module_table_prefix(APP_DIR).'_recycle')->where($this->where_list_sql)->counts();
            if (!$nums) {
                $this->_json(0, dr_lang('数据为空'));
            }
            $tpage = ceil($nums / $psize); // 总页数
            $this->_json(1, dr_lang('即将执行清空回收站命令'), [
                'jscode' => 'dr_iframe_show(\''.dr_lang('清空回收站').'\', \''.dr_url(APP_DIR.'/recycle/recycle_del').'&page=1&total='.$nums.'&tpage='.$tpage.'\', \'500px\', \'300px\', \'load\')'
            ]);
        }

        $tpage = (int)\Phpcmf\Service::L('input')->get('tpage');
        $total = (int)\Phpcmf\Service::L('input')->get('total');

        $db = \Phpcmf\Service::M()->db->table(dr_module_table_prefix(APP_DIR).'_recycle');
        $this->where_list_sql && $db->where($this->where_list_sql);
        $data = $db->limit($psize)->orderBy('id DESC')->get()->getResultArray();
        if (!$data) {
            // 写入日志
            \Phpcmf\Service::L('input')->system_log($this->name.'：清空回收站');
            $this->_html_msg(1, dr_lang('共删除%s条数据', $total));
        }

        $ids = [];
        foreach ($data as $t) {
            $ids[] = $t['id'];
        }

        $this->content_model->delete_for_recycle($ids);

        $this->_html_msg( 1, dr_lang('正在执行中【%s】...', $tpage.'/'.($page+1)),
            dr_url(APP_DIR.'/recycle/recycle_del', ['total' => $total, 'tpage' => $tpage, 'page' => $page + 1])
        );
    }

    // 后台回收站删除内容
    protected function _Admin_Recycle_Del() {

        $ids = \Phpcmf\Service::L('input')->get_post_ids();
        if (!$ids) {
            $this->_json(0, dr_lang('参数不存在'));
        }

        $rt = $this->content_model->delete_for_recycle($ids);

        // 写入日志
        \Phpcmf\Service::L('input')->system_log($this->name.'：删除回收站id ('.implode(', ', $ids).')');

        if ($rt['code']) {
            $this->_json(1, dr_lang('操作成功'));
        } else {
            $this->_json(0, $rt['msg']);
        }
    }

    // 后台回收站恢复查看
    protected function _Admin_Recycle_Show() {

        $id = intval(\Phpcmf\Service::L('input')->get('id'));

        // 说明来自页面
        define('IS_MODULE_RECYCLE', $id);

        list($tpl, $data) = $this->_Show($id);
        if (!$data) {
            $this->_admin_msg(0, dr_lang('内容不存在'));
        }

        \Phpcmf\Service::V()->assign([
            'menu' => $this->_module_menu(
                ' <i class="'.dr_icon('fa fa-trash-o').'"></i>  '.dr_lang('回收站管理'),
                \Phpcmf\Service::L('Router')->url(APP_DIR.'/recycle/index'),
                \Phpcmf\Service::L('Router')->url(APP_DIR.'/home/add')
            ),
            'catid' => $data['catid'],
            'web_url' => dr_url_prefix('index.php?s='.APP_DIR.'&c=show&m=recycle&id='.$id, APP_DIR),
        ]);
        \Phpcmf\Service::V()->display($this->_tpl_filename('post'));

    }

    // 后台回收站恢复内容
    protected function _Admin_Recovery() {

        $ids = \Phpcmf\Service::L('input')->get_post_ids();
        if (!$ids) {
            $this->_json(0, dr_lang('参数不存在'));
        }

        $rt = $this->content_model->recovery($ids);

        if ($rt['code']) {
            // 写入日志
            \Phpcmf\Service::L('input')->system_log($this->name.'：恢复回收站id ('.implode(', ', $rt['data']).')');
            $this->_json(1, dr_lang('操作成功'));
        } else {
            $this->_json(0, $rt['msg']);
        }
    }

    // 后台回收站编辑内容
    protected function _Admin_Recycle_Edit() {

        // 说明来自定时页面
        $id = intval(\Phpcmf\Service::L('input')->get('id'));
        define('IS_MODULE_RECYCLE', $id);
        $this->init['show_field'] = 'cid';
        $this->name.= dr_lang('恢复回收站');
        list($tpl, $data) = $this->_Post($id);
        if (!$data) {
            $this->_admin_msg(0, dr_lang('内容不存在'));
        }

        \Phpcmf\Service::V()->assign([
            'menu' => \Phpcmf\Service::M('auth')->_admin_menu(
                [
                    '回收站' => [MOD_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/index', 'fa fa-trash-o"'],
                    '修改' => ['hide:'.MOD_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/edit', 'fa fa-edit'],
                ]
            ),
            'form' =>  dr_form_hidden(['is_draft' => 0, 'module' => MOD_DIR, 'id' => $id]),
            'select' => \Phpcmf\Service::L('category', 'module')->select(
                $this->module['dirname'],
                $data['catid'],
                'id=\'dr_catid\' name=\'catid\' onChange="show_category_field(this.value)"',
                '', isset($this->module['setting']['pcatpost']) && $this->module['setting']['pcatpost'] ? 0 : 1, 1
            ),
            'web_url' => dr_url_prefix('index.php?s='.APP_DIR.'&c=show&m=recycle&id='.$id, APP_DIR),
            'is_recycle' => 1,
        ]);
        \Phpcmf\Service::V()->display($this->_tpl_filename('post'));
    }

    // ===========================

    // 推荐位管理
    protected function _Admin_Flag_List($rt = 0) {

        $flag = intval(\Phpcmf\Service::L('input')->get('flag'));
        if (!$this->module['setting']['flag'][$flag]) {
            $this->_admin_msg(0, dr_lang('推荐位（%s）不存在', $flag));
        }

        if (IS_POST) {
            $ids = \Phpcmf\Service::L('input')->get_post_ids();
            if (!$ids) {
                $this->_json(0, dr_lang('所选内容不存在'));
            }
            foreach ($ids as $id) {
                $this->content_model->delete_flag($id, $flag);
            }
            $this->_json(1, dr_lang('操作成功'));
        }

        $this->_init([
            'table' => dr_module_table_prefix(APP_DIR),
            'field' => $this->module['field'],
            'sys_field' => ['inputtime', 'updatetime', 'inputip', 'displayorder', 'hits', 'uid'],
            'date_field' => 'inputtime',
            'order_by' => 'inputtime desc',
            'show_field' => 'title',
            'list_field' => $this->module['setting']['list_field'],
            'where_list' => 'id IN (select id from `'.\Phpcmf\Service::M()->dbprefix(dr_module_table_prefix(APP_DIR).'_flag').'` where flag='.$flag.')'.($this->where_list_sql ? ' AND '.$this->where_list_sql : ''),
        ]);
        // 判断栏目分表
        if ($this->module['is_ctable']) {
            $catid = intval(\Phpcmf\Service::L('input')->get('catid'));
            if ($catid) {
                $this->init['table'] = dr_module_ctable(dr_module_table_prefix(APP_DIR), dr_cat_value($catid));
            }
        }

        $this->is_ajax_list = true;
        $this->fix_table_list && $this->is_ajax_list = false;

        list($tpl, $data) = $this->_List();

        $this->mytable = [
            'foot_tpl' => '',
            'link_tpl' => '',
            'link_var' => 'html = html.replace(/\{id\}/g, row.id);',
        ];
        if ($this->_is_admin_auth('del')) {
            $this->mytable['foot_tpl'].= '<label class="table_select_all"><input onclick="dr_table_select_all(this)" type="checkbox"><span></span></label>';
            $this->mytable['foot_tpl'].= '<label><button type="button" onclick="dr_table_option(\''.dr_now_url().'\', \''.dr_lang('你确定要移除推荐位吗？').'\')" class="btn red btn-sm"> <i class="fa fa-close"></i> '.dr_lang('移除').'</button></label>';
        }
        if ($this->_is_admin_auth('edit')) {
            $this->mytable['link_tpl'] .= '<label><a href="' . dr_url(APP_DIR . '/' . \Phpcmf\Service::L('Router')->class . '/edit') . '&id={id}" class="btn btn-xs red"> <i class="fa fa-edit"></i> ' . dr_lang('修改') . '</a></label>';
        }

        \Phpcmf\Service::V()->assign([
            'p' => ['flag' => $flag],
            'menu' => $this->_module_menu(
                ' <i class="'.dr_icon($this->module['setting']['flag'][$flag]['icon']).'"></i>  '.dr_lang($this->module['setting']['flag'][$flag]['name']),
                \Phpcmf\Service::L('Router')->url(APP_DIR.'/flag/index', ['flag' => $flag]),
                \Phpcmf\Service::L('Router')->url(APP_DIR.'/home/add')
            ),
            'category_select' => \Phpcmf\Service::L('category', 'module')->select(
                $this->module['dirname'],
                $data['param']['catid'],
                'name="catid"',
                '--', 0
            ),
            'mytable' => $this->mytable,
        ]);

        $tpl = $this->_tpl_filename('list');
        if ($rt) {
            return $tpl;
        } else {
            return \Phpcmf\Service::V()->display($tpl);
        }
    }

    // ===========================

    /**
     * 获取内容
     * $id      内容id,新增为0
     * */
    protected function _Data($id = 0) {

        if (!$id) {
            $data = [];
            if (isset($this->module['setting']['hits_min']) && isset($this->module['setting']['hits_max'])
                && $this->module['setting']['hits_min'] && $this->module['setting']['hits_max'])  {
                $data['hits'] = (int)rand((int)$this->module['setting']['hits_min'], (int)$this->module['setting']['hits_max']);
            }
            return $data;
        }

        $catid = intval(\Phpcmf\Service::L('input')->get('catid'));

        if (defined('IS_MODULE_VERIFY')) {
            // 判断是否来至审核
            $row = \Phpcmf\Service::M()->table(dr_module_table_prefix(APP_DIR).'_verify')->get($id);
            if ($row) {
                // 调用当前表数据
                $now = $this->content_model->get_data($id);
                if ($now) {
                    $data = dr_string2array($row['content']) + $now;
                } else {
                    $data = dr_string2array($row['content']);
                }
                $data['verify'] = [
                    'vid' => $row['vid'],
                    'uid' => $row['backuid'],
                    'isnew' => $row['isnew'],
                    'backinfo' => dr_string2array($row['backinfo']),
                ];
                $data['my_flag'] = isset($data['verify']['backinfo']['flag']) ? $data['verify']['backinfo']['flag'] : [];
                $this->is_get_catid = $catid ? $catid : $data['catid'];
            } else {
                $data = [
                    'id' => 0,
                ];
                $this->is_get_catid = $catid;
            }
        } elseif (defined('IS_MODULE_TIME')) {
            // 判断是否来至定时发布
            $row = \Phpcmf\Service::M()->table(dr_module_table_prefix(APP_DIR).'_time')->get($id);
            $data = dr_string2array($row['content']);
            $data['my_flag'] = $data['flag'];
            $data['posttime'] = $row['posttime'];
            $this->is_get_catid = $catid ? $catid : $data['catid'];
        } elseif (defined('IS_MODULE_RECYCLE')) {
            // 判断是否来至回收站
            $row = \Phpcmf\Service::M()->table(dr_module_table_prefix(APP_DIR).'_recycle')->get($id);
            $c = dr_string2array($row['content']);
            $data = [];
            if ($c) {
                foreach ($c as $t) {
                    $t && $data = dr_array22array($data, $t);
                }
            }
            $this->is_get_catid = $data['catid'];
        } else {
            // 正常内容
            $data = $this->content_model->get_data($id);
            if (!$data) {
                return [];
            }

            if ($this->module['is_ctable']) {
                $this->init['table'] = dr_module_ctable($this->init['table'], dr_cat_value($data['catid']));
            }

            // 判断是同步栏目数据
            if ($data['link_id'] > 0) {
                $data = $this->content_model->get_data($data['link_id']);
                if (!$data) {
                    return [];
                }
                $this->replace_id = $id = $data['id'];
            }

            $this->is_get_catid = $catid ? $catid : $data['catid'];
        }

        // 推荐位
        $data['my_flag'] = isset($data['my_flag']) ? $data['my_flag'] : ($id ? $this->content_model->get_flag($id) : []);

        if ($this->is_get_catid) {
            // 为什么要赋值
            $this->module['category'][$catid] = dr_cat_value($this->module['mid'], $catid);
            \Phpcmf\Service::V()->assign('module', $this->module);
        }

        return $data;
    }

    // 格式化保存数据
    protected function _Format_Data($id, $data, $old) {

        // 验证栏目
        $catid = (int)\Phpcmf\Service::L('input')->post('catid');
        $cat = dr_cat_value($this->module['mid'], $catid);
        if (!$cat && !$this->is_hcategory) {
            $this->_json(0, dr_lang('栏目[%s]不存在', $catid));
        }

        // 判断栏目分表
        $this->module['is_ctable'] && $this->init['table'] = dr_module_ctable(dr_module_table_prefix(APP_DIR), dr_cat_value($catid));

        // 验证后台权限
        $data[1]['catid'] = $data[0]['catid'] = $catid;

        // 验证状态
        if ($this->is_post_user) {
            // 投稿者
            $data[1]['status'] = \Phpcmf\Service::M('auth')->is_post_user_status() ? 1 : 9;
        } else {
            $data[1]['status'] = 9;
        }

        $data[1]['uid'] = (int)$data[1]['uid'];

        // 默认数据
        $data = $this->content_model->format_data($data);

        // 不更新时间
        if (!$old['updatetime']) {
            !$data[1]['updatetime'] && $data[1]['updatetime'] = SYS_TIME;
        } elseif ($id && isset($_POST['no_time'])
            && $_POST['no_time']) {
            $data[1]['updatetime'] = $old['updatetime'];
        }

        return $data;
    }

    /**
     * 保存内容
     * $id      内容id,新增为0
     * $data    提交内容数组,留空为自动获取
     * $func    格式化提交的数据
     * */
    protected function _Save($id = 0, $data = [], $old = [], $func = null, $func2 = null) {

        $did = intval(\Phpcmf\Service::L('input')->get('did'));
        $is_draft = intval(\Phpcmf\Service::L('input')->post('is_draft'));

        if ($this->is_post_user) {
            if ($old && $old['uid'] && $old['uid'] != $data[1]['uid']) {
                return dr_return_data(0, dr_lang('归属账号无法更改'), ['field' => 'uid']);
            } elseif ($data[1]['uid'] != $this->uid) {
                return dr_return_data(0, dr_lang('归属账号无法更改'), ['field' => 'uid']);
            }
        }

        // 判断定时发布时间
        if (defined('IS_MODULE_TIME')) {
            if (isset($_POST['posttime']) && $_POST['posttime']) {
                $this->post_time = (int)strtotime(\Phpcmf\Service::L('input')->post('posttime'));
            } else {
                $this->post_time = (int)strtotime(\Phpcmf\Service::L('input')->get('posttime'));
            }
            if (SYS_TIME > $this->post_time) {
                return dr_return_data(0, dr_lang('定时发布时间[%s]不正确', dr_date($this->post_time)), $data);
            }
            // 保存定时发布数据
            $this->init['table'] = dr_module_table_prefix(APP_DIR).'_time';
            $rt = $this->content_model->save_post_time($id, $data, $this->post_time);
            $this->_json($rt['code'], $rt['msg']);
        } elseif ($is_draft) {
            // 草稿箱存储
            $data[1]['id'] = $id;
            $this->name.= dr_lang('草稿箱');
            $this->init['table'] = dr_module_table_prefix(APP_DIR).'_draft';
            return $this->content_model->insert_draft($did, $data);
        } else {
            // 删除草稿
            $did && $this->content_model->delete_draft($did);
            if (defined('IS_MODULE_RECYCLE')) {
                // 是否回收站恢复，id恢复以前的
                $id = $data[1]['id'] = $data[0]['id'] = intval($old['id']);
            }
            // 正常存储
            return parent::_Save($id, $data, $old,
                function ($id, $data, $old) {
                    // 禁止修改栏目
                    if ($old['catid']) {
                        $cat = dr_cat_value($this->module['mid'], $old['catid']);
                        if ($cat['setting']['notedit']) {
                            $data[1]['catid'] = $old['catid'];
                        }
                    }
                    // 发布之前判断
                    if ($old && defined('IS_MODULE_VERIFY')) {
                        // 是否来自审核
                        if ($this->is_post_user && IS_USE_MEMBER) {
                            // 投稿者编辑
                            $data[1]['status'] = $this->is_hcategory ? $this->content_model->_hcategory_member_post_status($this->member) : $this->content_model->get_verify_status(
                                $data[1]['id'],
                                $this->member,
                                \Phpcmf\Service::L('member_auth', 'member')->category_auth($this->module, $data[1]['catid'], 'verify', $this->member)
                            );
                        } else {
                            if ($_POST['verify']['status']) {
                                // 通过
                                $step = $this->_get_verify($old['verify']['vid']);
                                $status = intval($old['status']);
                                $data[1]['status'] = dr_count($step) - 1 <= $status ? 9 : $status + 1;
                                // 再次验证审核级别
                                if (!$this->_get_verify_status_edit($old['verify']['vid'], $data[1]['status'])) {
                                    $this->_json(0, dr_lang('当前角色无权限审核此内容'));
                                }
                                // 任务执行成功
                                \Phpcmf\Service::M('member')->todo_admin_notice( MOD_DIR.'/verify/edit:id/'.$id, SITE_ID);
                                if ($data[1]['status'] == 9 && $old) {
                                    // 审核通过时读取最新数据
                                    $new = \Phpcmf\Service::M()->table_site(MOD_DIR)->get($old['id']);
                                    if ($new) {
                                        $data[1]['hits'] = $new['hits'];
                                    }
                                }
                            } else {
                                // 拒绝
                                $data[1]['status'] = 0;
                                // 通知
                                $old['note'] = (string)$_POST['verify']['msg'];
                                \Phpcmf\Service::L('Notice')->send_notice('module_content_verify_0', $old);
                            }
                        }
                        // 审核时赋予当前角色账号
                        $this->member = dr_member_info($data[1]['uid']);
                    } elseif (defined('IS_MODULE_RECYCLE')) {
                        // 是否回收站恢复
                        $this->content_model->recovery([IS_MODULE_RECYCLE]);
                    } elseif (defined('IS_MODULE_TG')) {
                        // 是否退稿
                        $data[1]['status'] = 0;
                        // 通知
                        $_POST['verify']['msg'] = $old['note'] = \Phpcmf\Service::L('input')->get('note');
                        \Phpcmf\Service::L('Notice')->send_notice('module_content_verify_0', $old);
                        $this->content_model->table($this->content_model->mytable.'_verify')->delete($id);
                        $this->name.= dr_lang('退稿审核');
                    }

                    return dr_return_data(1, 'ok', $data);
                },
                function ($id, $data, $old) {

                    // 审核通过后
                    if ($data[1]['status'] == 9) {

                        // 同步发送到其他栏目
                        $this->content_model->sync_cat(\Phpcmf\Service::L('input')->post('sync_cat'), $data);
                        // 处理推荐位
                        if ($old['my_flag']) {
                            $this->content_model->delete_flag($id, 'all');
                        }
                        $myflag = \Phpcmf\Service::L('input')->post('flag');
                        if ($myflag) {
                            foreach ($myflag as $i) {
                                if (isset($this->module['setting']['flag'][$i])) {
                                    $this->content_model->insert_flag((int)$i, $id, $data[1]['uid'], $data[1]['catid']);
                                }
                            }
                        }
                    }

                    $data[1]['id'] = $id;

                    return $data;
                }
            );
        }
    }

    /**
     * 回调处理结果
     * $data
     * */
    protected function _Call_Post($data) {

        if ($data[1]['status'] == 9) {
            $html = $list = '';
            $cat = dr_cat_value($this->module['mid'], $data[1]['catid']);
            if ($cat['setting']['html']) {
                // 生成权限文件
                if (!dr_html_auth(1)) {
                    $this->_json(0, dr_lang('/cache/html/ 无法写入文件'));
                }
                $list = dr_web_prefix('index.php?s='.MOD_DIR.'&c=html&m=categoryfile&id='.$data[1]['catid']);
            }
            if ($cat['setting']['chtml']) {
                // 生成权限文件
                if (!dr_html_auth(1)) {
                    $this->_json(0, dr_lang('/cache/html/ 无法写入文件'));
                }
                $html = dr_web_prefix('index.php?s='.MOD_DIR.'&c=html&m=showfile&id='.$data[1]['id']);
            }
            $this->_json(1, dr_lang('操作成功'), [
                'id' => $data[1]['id'],
                'url' => isset($_GET['is_self']) ? dr_url(MOD_DIR.'/home/edit', ['id' => $data[1]['id']]) : '',
                'catid' => $data[1]['catid'],
                'htmlfile' => $html,
                'htmllist' => $list
            ]);
        } else {
            if (intval(\Phpcmf\Service::L('input')->post('is_draft'))) {
                // 草稿
                $this->_json(1, dr_lang('保存草稿成功'));
            } elseif ($this->is_post_user) {
                // 投稿者
                $this->_json(1, dr_lang('操作成功，等待管理员审核'), [
                    'url' => dr_url($this->_is_admin_auth(MOD_DIR.'/verify/index') ? MOD_DIR.'/verify/index' : MOD_DIR.'/home/index'),
                    'id' => $data[1]['id'],
                    'catid' => $data[1]['catid'],
                ]);
            }
            $this->_json(1, dr_lang('操作成功'), [
                'id' => $data[1]['id'],
                'catid' => $data[1]['catid']
            ]);
        }
    }

    // 验证是否具有审核状态
    protected function _get_verify_status_edit($vid, $status) {
        return \Phpcmf\Service::M('verify', 'module')->_get_verify_status_edit($vid, $status);
    }

    // 获取当前栏目的时候流程
    protected function _get_verify($vid) {
        return \Phpcmf\Service::M('verify', 'module')->_get_verify($vid);
    }

    // 模块后台菜单
    protected function _module_menu($list_name, $list_url, $post_url) {

        // <a class="btn green-haze btn-outline btn-circle btn-sm" href="javascript:;" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" aria-expanded="false">
        $module_menu = '<a class="dropdown-toggle {ON}" '.(\Phpcmf\Service::IS_MOBILE_USER() ? ' data-toggle="dropdown"' : '').' data-hover="dropdown" data-close-others="true" aria-expanded="true"><i class="fa fa-angle-double-down"></i></a>';
        $module_menu.= '<ul class="dropdown-menu">';
        $this->_is_admin_auth($this->module['dirname'].'/home/index') && $module_menu.= '<li><a href="'.\Phpcmf\Service::L('router')->url($this->module['dirname'].'/home/index').'"> <i class="'.dr_icon($this->module['icon']).'"></i> '.dr_lang('%s管理', $this->module['cname']).' </a></li>';

        if ($this->module['setting']['flag']) {
            $module_menu.= '<li class="divider"> </li>';
            foreach ($this->module['setting']['flag'] as $i => $t) {
                $module_menu.= '<li><a href="'.\Phpcmf\Service::L('router')->url($this->module['dirname'].'/flag/index', array('flag'=>$i)).'"> <i class="'.dr_icon($t['icon']).'"></i> '.dr_lang($t['name']).' </a></li>';
            }
        }

        if ($this->module['form']) {
            $module_menu.= '<li class="divider"> </li>';
            foreach ($this->module['form'] as $i => $t) {
                $this->_is_admin_auth($this->module['dirname'].'/'.$i.'/index') && $module_menu.= '<li><a href="'.\Phpcmf\Service::L('router')->url($this->module['dirname'].'/'.$i.'/index').'"> <i class="'.dr_icon($t['setting']['icon']).'"></i> '.dr_lang('%s管理', $t['name']).' </a></li>';
            }
        }

        $module_menu.= '<li class="divider"> </li>';
        $this->_is_admin_auth($this->module['dirname'].'/draft/index') && $module_menu.= '<li><a href="'.\Phpcmf\Service::L('router')->url($this->module['dirname'].'/draft/index').'"> <i class="fa fa-pencil"></i> '.dr_lang('草稿箱管理').' </a></li>';
        $this->_is_admin_auth($this->module['dirname'].'/recycle/index') && $module_menu.= '<li><a href="'.\Phpcmf\Service::L('router')->url($this->module['dirname'].'/recycle/index').'"> <i class="fa fa-trash-o"></i> '.dr_lang('回收站管理').' </a></li>';
        $this->_is_admin_auth($this->module['dirname'].'/time/index') && $module_menu.= '<li><a href="'.\Phpcmf\Service::L('router')->url($this->module['dirname'].'/time/index').'"> <i class="fa fa-clock-o"></i> '.dr_lang('待发布管理').' </a></li>';
        $this->_is_admin_auth($this->module['dirname'].'/verify/index') && $module_menu.= '<li><a href="'.\Phpcmf\Service::L('router')->url($this->module['dirname'].'/verify/index').'"> <i class="fa fa-edit"></i> '.dr_lang('待审核管理').' </a></li>';

        if ($this->_is_admin_auth()) {
            $module_menu.= '<li class="divider"> </li>';
            $module_menu.= '<li><a href="javascript:dr_iframe_show(\''.dr_lang('模块内容字段').'\', \''.dr_url('field/index', ['rname'=>'module', 'rid'=> $this->module['id'], 'is_menu'=>1]).'\', \'80%\', \'80%\')""> <i class="fa fa-code"></i> '.dr_lang('模块内容字段').'</a> </li>';
            $module_menu.= '<li><a href="javascript:dr_iframe_show(\''.dr_lang('栏目模型字段').'\', \''.dr_url('field/index', ['rname'=>'catmodule-'.$this->module['dirname'], 'rid'=> 0, 'is_menu'=>1]).'\', \'80%\', \'80%\')""> <i class="fa fa-code"></i> '.dr_lang('栏目模型字段').'</a> </li>';
            $module_menu.= '<li><a href="javascript:dr_iframe_show(\''.dr_lang('划分栏目模型字段').'\', \''.dr_url('module/module_category/field_index', ['dir'=>$this->module['dirname'], 'rid'=> 0, 'is_menu'=>1]).'\', \'80%\', \'80%\')""> <i class="fa fa-edit"></i> '.dr_lang('划分栏目模型字段').'</a> </li>';
        }

        $module_menu.= '</ul>';

        // 显示菜单
        $menu = '';
        $menu.= '<li class="dropdown"> <a href="'.$list_url.'" class="{ON}">'.$list_name.'</a> '.$module_menu.' <i class="fa fa-circle"></i> </li>';

        if (\Phpcmf\Service::IS_PC_USER()) {
            // 独立模块显示其栏目
            if (!$this->module['share'] && $this->_is_admin_auth($this->module['dirname'].'/category/index')) {
                $menu.= '<li><a href="'.\Phpcmf\Service::L('router')->url($this->module['dirname'].'/category/index').'"> <i class="fa fa-reorder"></i> '.dr_lang('栏目管理').'</a> <i class="fa fa-circle"></i> </li>';
            }

            $menu.= '<li><a href="javascript:dr_iframe_show(\''.dr_lang('批量更新内容URL').'\', \''.dr_url('api/update_url', ['mid' => $this->module['dirname']]).'\', \'500px\', \'300px\')""> <i class="fa fa-refresh"></i> '.dr_lang('更新URL').'</a> <i class="fa fa-circle"></i> </li>';
            if ($this->_is_admin_auth('module/module/edit')) {
                $menu.= '<li><a href="javascript:dr_iframe_show(\''.dr_lang('模块配置').'\', \''.dr_url('module/module/edit', ['id' => $this->module['id']]).'\', \'80%\', \'80%\')""> <i class="fa fa-cog"></i> '.dr_lang('模块配置').'</a> <i class="fa fa-circle"></i> </li>';
            }
        }

        // 非内容页面就显示返回链接
        if (\Phpcmf\Service::L('router')->uri() != $this->module['dirname'].'/home/index'
            && $this->_is_admin_auth($this->module['dirname'].'/home/index') ) {
            $menu.= '<li> <a href="'.\Phpcmf\Service::L('Router')->get_back($this->module['dirname'].'/home/index').'" class=""> <i class="fa fa-reply"></i> '.dr_lang('返回').'</a> <i class="fa fa-circle"></i> </li>';
        }

        // 发布和编辑权限
        $this->_is_admin_auth($this->module['dirname'].'/home/add') && $post_url && $menu.= '<li> <a href="'.$post_url.'" class="'.(\Phpcmf\Service::L('router')->method == 'add' ? 'on' : '').'"> <i class="fa fa-plus"></i> '.(isset($this->module['post_name']) && $this->module['post_name'] ? dr_lang($this->module['post_name']) : dr_lang('发布')).'</a> <i class="fa fa-circle"></i> </li>';
        \Phpcmf\Service::L('router')->method == 'edit' && $menu.= '<li> <a href="'.dr_now_url().'" class="on"> <i class="fa fa-edit"></i> '.dr_lang('修改').'</a> <i class="fa fa-circle"></i> </li>';

        // 选中判断
        strpos($menu, 'class="on"') === false && $menu = str_replace('{ON}', 'on', $menu);

        return $menu;
    }

    /**
     * 兼容低版本
     */
    protected function _app_cbottom_fix($type = '', $data = [])
    {

        if (!$type) {
            // 表示模块部分
            $endfix = '';
        } else {
            $endfix = '_'.$type;
        }

        // 加载模块自身的
        if (APP_DIR && is_file(APPPATH.'Config/Cbottom'.$endfix.'.php')) {
            $_clink = require APPPATH.'Config/Cbottom'.$endfix.'.php';
            if ($_clink) {
                if (is_file(APPPATH.'Models/Auth'.$endfix.'.php')) {
                    $obj = \Phpcmf\Service::M('auth'.$endfix, APP_DIR);
                    if (method_exists($obj, 'is_bottom_auth') && $obj->is_bottom_auth(APP_DIR)) {
                        $data = array_merge($data , $_clink);
                    }
                } else {
                    $data = array_merge($data , $_clink);
                }
            }
        }

        // 加载全部插件的
        $local = \Phpcmf\Service::Apps();
        foreach ($local as $dir => $path) {
            // 排除模块自身
            if (strtolower($dir) == APP_DIR) {
                continue;
            }
            // 判断插件目录
            if (is_file($path.'install.lock') && is_file($path.'Config/Cbottom'.$endfix.'.php') && is_file($path.'Config/App.php')) {
                $cfg = require $path.'Config/App.php';
                if ($cfg['type'] == 'app' && !$cfg['ftype']) {
                    // 表示插件非模块
                    $_clink = require $path.'Config/Cbottom'.$endfix.'.php';
                    if ($_clink) {
                        if (is_file($path.'Models/Auth'.$endfix.'.php')) {
                            $obj = \Phpcmf\Service::M('auth'.$endfix, $dir);
                            if (method_exists($obj, 'is_bottom_auth') && $obj->is_bottom_auth(APP_DIR)) {
                                $data = array_merge($data , $_clink);
                            }
                        } else {
                            $data = array_merge($data , $_clink);
                        }
                    }
                }
            }
        }

        if ($data) {
            foreach ($data as $i => $t) {
                if (IS_ADMIN) {
                    if (!$t['url']) {
                        unset($data[$i]); // 没有url
                        CI_DEBUG && log_message('debug', 'Cbottom（'.$t['name'].'）没有设置url参数');
                        continue;
                    } elseif ($t['uri'] && !$this->_is_admin_auth($t['uri'])) {
                        unset($data[$i]); // 无权限的不要
                        continue;
                    }
                    $data[$i]['url'] = urldecode($data[$i]['url']);
                } else {
                    if (!$t['murl']) {
                        unset($data[$i]); // 非后台必须验证murl
                        CI_DEBUG && log_message('debug', 'Cbottom（'.$t['name'].'）没有设置murl参数');
                        continue;
                    }
                    $data[$i]['url'] = urldecode($data[$i]['murl']);
                }
            }
        }

        return $data;
    }
}
