<?php namespace Phpcmf\Controllers;

class Api extends \Phpcmf\App
{

    public function index() {


    }


    /**
     * 内容关联字段数据读取
     */
    public function related() {

        // 强制将模板设置为后台
        \Phpcmf\Service::V()->admin();

        // 登陆判断
        /*
        if (!$this->uid) {
            $this->_json(0, dr_lang('会话超时，请重新登录'));
        }*/

        // 参数判断
        $dirname = dr_safe_filename(\Phpcmf\Service::L('input')->get('module'));
        if (!$dirname) {
            $this->_json(0, dr_lang('module参数不存在'));
        }

        // 站点选择
        $site = max(1, (int)\Phpcmf\Service::L('input')->get('site'));
        $pagesize = (int)\Phpcmf\Service::L('input')->get('pagesize');
        if (!$pagesize) {
            $pagesize = 10;
        }

        // 模块缓存判断
        $module = $this->get_cache('module-'.$site.'-'.$dirname);
        if (!$module) {
            $this->_json(0, dr_lang('模块（%s）不存在', $dirname));
        }

        $module['field']['id'] = [
            'name' => 'Id',
            'ismain' => 1,
            'fieldtype' => 'Text',
            'fieldname' => 'id',
        ];

        $param = $data = \Phpcmf\Service::L('input')->get('', true);

        $diy = dr_safe_filename($data['diy']);

        if (IS_POST) {
            $ids = \Phpcmf\Service::L('input')->get_post_ids();
            if (!$ids) {
                $this->_json(0, dr_lang('没有选择项'));
            }
            $id = [];
            foreach ($ids as $i) {
                $id[] = (int)$i;
            }
            $builder = \Phpcmf\Service::M()->db->table($site.'_'.$dirname);
            $builder->whereIn('id', $id);
            $mylist = $builder->orderBy('updatetime DESC')->get()->getResultArray();
            if (!$mylist) {
                $this->_json(0, dr_lang('没有相关数据'));
            }
            $name = dr_safe_filename(\Phpcmf\Service::L('input')->get('name'));
            if (!$name) {
                $this->_json(0, dr_lang('name参数不能为空'));
            }

            $mid = $dirname;
            $ids = [];
            foreach ($mylist as $t) {
                $ids[] = $t['id'];
            }

            $file = \Phpcmf\Service::V()->code2php(
                file_get_contents(is_file(MYPATH.'View/api_related_field_'.$diy.'.html') ? MYPATH.'View/api_related_field_'.$diy.'.html' : COREPATH.'View/api_related_field.html')
            );
            ob_start();
            require $file;
            $code = ob_get_clean();
            $html = explode('<!--list-->', $code);

            $this->_json(1, dr_lang('操作成功'), ['ids' => $ids, 'html' => $html[1]]);
        }

        $my = intval($data['my']);
        $where = [];
        if ($my) {
            $where[] = 'uid = '.$this->uid;
        } elseif ($this->member && $this->member['adminid'] > 0) {
            $module['field']['uid'] = [
                'name' => dr_lang('账号'),
                'ismain' => 1,
                'fieldtype' => 'Text',
                'fieldname' => 'uid',
            ];
        }
        if ($data['search']) {
            $catid = (int)$data['catid'];
            if ($catid) {
                $cat = dr_cat_value($module['mid'], $catid);
                if ($cat['catids']) {
                    $where[] = '`catid` in('.implode(',', $cat['catids']).')';
                }
            }
            $data['keyword'] = dr_safe_replace(urldecode($data['keyword']));
            if (isset($data['keyword']) && $data['keyword'] && $data['field'] && isset($module['field'][$data['field']])) {
                $data['keyword'] = dr_safe_replace(urldecode($data['keyword']));
                if ($data['field'] == 'id') {
                    // id搜索
                    $id = [];
                    $ids = explode(',', $data['keyword']);
                    foreach ($ids as $i) {
                        $id[] = (int)$i;
                    }
                    $where[] = 'id in('.implode(',', $id).')';
                } else if ($data['field'] == 'uid') {
                    $uid = \Phpcmf\Service::M('member')->uid($data['keyword']);
                    $where[] = 'uid = '.intval($uid);
                } else {
                    // 其他模糊搜索
                    $where[] = $data['field'].' LIKE "%'.$data['keyword'].'%"';
                }
            }
        }

        sort($module['field']);
        $rules = $data;
        $rules['page'] = '{page}';

        \Phpcmf\Service::V()->assign([
            'diy' => $data['diy'],
            'mid' => $dirname,
            'mmid' => $module['mid'],
            'site' => $site,
            'param' => $data,
            'field' => $module['field'],
            'where' => $where ? urlencode(implode(' AND ', $where)) : '',
            'search' => dr_form_search_hidden(['search' => 1, 'is_iframe' => 1, 'module' => $dirname, 'site' => $site, 'diy' => $data['diy'], 'my' => $my, 'pagesize' => $pagesize]),
            'select' => \Phpcmf\Service::L('category', 'module')->select(
                $module['dirname'],
                $data['catid'],
                'name="catid"',
                '--',isset($this->module['setting']['pcatpost']) && $this->module['setting']['pcatpost'] ? 0 : 1
            ),
            'urlrule' => '/index.php?s=module&c=api&m=related&'.http_build_query($rules),
            'pagesize' => $pagesize,
        ]);
        if (is_file(MYPATH.'View/api_related_'.$diy.'.html')) {
            \Phpcmf\Service::V()->display('api_related_'.$diy.'.html');
        } else {
            \Phpcmf\Service::V()->display('api_related.html');
        }
    }



}
