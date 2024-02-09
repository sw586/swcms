<?php namespace Phpcmf\Model\Module;
/**
 * {{www.xunruicms.com}}
 * {{迅睿内容管理框架系统}}
 * 本文件是框架系统文件，二次开发时不可以修改本文件，可以通过继承类方法来重写此文件
 **/

// 模型类
class Repair extends \Phpcmf\Model {

    private $modules = [];
    private $cat_dir = [];
    private $cat_cache = [];
    private $cat_main = [];
    private $categorys = [];
    private $category_field = [];

    // 获取栏目的自定义字段
    public function get_category_field($cdir) {

        if (isset($this->category_field[$cdir]) && $this->category_field[$cdir]) {
            return $this->category_field[$cdir];
        }

        $category_field = [];

        $field = $this->db->table('field')
            ->where('disabled', 0)
            ->where('relatedname', 'category-'.$cdir)
            ->orderBy('displayorder ASC, id ASC')->get()->getResultArray();
        if ($field) {
            foreach ($field as $f) {
                $f['setting'] = dr_string2array($f['setting']);
                $category_field[$f['fieldname']] = $f;
            }
        }
        if (!isset($category_field['thumb'])) {
            \Phpcmf\Service::M('module', 'module')->_add_field([
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
        if (!APP_DIR && !isset($category_field['content'])) {
            \Phpcmf\Service::M('module', 'module')->_add_field([
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

        $this->category_field[$cdir] = $category_field;

        return $category_field;
    }

    public function init($cat) {
        $this->cat_dir = $cat['cat_dir'] ? $cat['cat_dir'] : [];
        $this->cat_main = $cat['cat_main'] ? $cat['cat_main'] : [];
        $this->cat_cache = $cat['cat_cache'] ? $cat['cat_cache'] : [];
        $this->categorys = $cat['categorys'];
        return $this;
    }

    public function get_cat_dir() {
        return $this->cat_dir;
    }

    public function get_cat_cache() {
        return $this->cat_cache;
    }

    public function get_cat_main() {
        return $this->cat_main;
    }

    // 单个栏目缓存文件
    public function cache($catid, $dir) {

        $cat = $this->categorys[$catid];
        if ($dir && $dir != 'share') {
            // 独立模块
            $cache_dir = $cdir = $cat['mid'] = $dir;
        } else {
            // 共享模块
            $cdir = $cat['mid'] = isset($cat['mid']) ? $cat['mid'] : '';
            !$cdir && $cdir = 'share';
            $cache_dir = 'share';
        }

        if (!$this->modules) {
            $this->modules = \Phpcmf\Service::M('module', 'module')->get_module_info();
            $this->modules['share'] = [
                'share' => 1,
                'dirname' => 'share',
                'category' => $this->categorys,
            ];
        }

        $pid = explode(',', (string)$cat['pids']);
        $cat['topid'] = isset($pid[1]) ? $pid[1] : $cat['id'];
        $cat['pcatpost'] = 0;
        $cat['catids'] = explode(',', $cat['childids']);
        if (isset($this->modules[$cat['mid']]) && $this->modules[$cat['mid']]) {
            // 是内容模块
            if (isset($this->modules[$cat['mid']]['setting']['pcatpost']) && $this->modules[$cat['mid']]['setting']['pcatpost']) {
                // 允许父栏目发布
                $cat['pcatpost'] = 1;
            }
        } else {

        }
        $cat['is_post'] = $cat['pcatpost'] ? 1 : ($cat['child'] ? 0 : 1); // 是否允许发布内容
        if ($dir && $dir != 'share') {
            // 独立模块
            //以站点为准
            if (!isset($cat['tid'])) {
                $cat['tid'] = $cat['setting']['linkurl'] ? 2 : 1; // 判断栏目类型 2表示外链
            }
            $cat['setting']['html'] = $cat['setting']['chtml'] = intval($this->modules[$cat['mid']]['site'][SITE_ID]['html']);
            $cat['setting']['urlrule'] = intval($this->modules[$cat['mid']]['site'][SITE_ID]['urlrule']);
        } else {
            // 共享模块
            // 共享栏目时
            //以本栏目为准
            $cat['setting']['html'] = intval($cat['setting']['html']);
            $cat['setting']['chtml'] = intval($cat['setting']['chtml']);
            $cat['setting']['urlrule'] = intval($cat['setting']['urlrule']);
        }
        // 获取栏目url
        if ($cat['tid'] == 2 && $cat['setting']['linkurl']) {
            $cat['url'] = dr_url_prefix($cat['setting']['linkurl'], $cat['mid'], SITE_ID, 0);
        } else {
            $cat['url'] = dr_module_category_url($this->modules[$cdir], $cat);
        }

        // 统计栏目文章数量
        $cat['total'] = '-';
        // 格式化栏目
        $cat = \Phpcmf\Service::L('Field')->format_value($this->get_category_field($cache_dir), $cat, 1);
        // 归类栏目模型字段
        $cat['field'] = [];
        if ($cat['ismain'] && $cat['setting']['module_field']) {
            foreach ($cat['setting']['module_field'] as $_fname => $o) {
                $cat['field'][] = $_fname;
            }
        }
        if ($cat['pid']) {
            $this->cat_dir[$cat['pdirname'].$cat['dirname']] = $cat['id'];
        }
        $this->cat_dir[$cat['dirname']] = $cat['id'];
        // 缓存数据
        $this->cat_cache[$cat['id']] = [
            'id' => $cat['id'],
            'url' => $cat['url'],
            'tid' => $cat['tid'],
            'pid' => $cat['pid'],
            'mid' => $cat['mid'],
            'name' => $cat['name'],
            'thumb' => $cat['thumb'],
            'pids' => $cat['pids'],
            'child' => $cat['child'],
            'catids' => $cat['catids'],
            'dirname' => $cat['dirname'],
            'pdirname' => $cat['pdirname'],
            'ismain' => $cat['ismain'],
            'field' => $cat['field'],
            'is_post' => $cat['is_post'],
            'pcatpost' => $cat['pcatpost'],
            'show' => $cat['show'],
            'topid' => $cat['topid'],
        ];
        if ($cat['ismain']) {
            $this->cat_main[$cat['id']] = $this->cat_cache[$cat['id']];
        }
        \Phpcmf\Service::L('cache')->set_file($cat['id'], $cat, 'module/category-'.SITE_ID.'-'.$cache_dir.'-data');
        \Phpcmf\Service::L('cache')->set_file($cat['id'], $this->cat_cache[$cat['id']], 'module/category-'.SITE_ID.'-'.$cache_dir.'-min');
    }

    // 获取子栏目json
    public function get_child_row($pid) {
        $newArr = [];
        foreach ($this->categorys as $cat) {
            $item = [
                'value' => $cat['id'],
                'label' => $cat['name'],
                'children' => [],
            ];
            if ($pid == $cat['pid']) {
                $item['children'] = $this->get_child_row($cat['id']);
                $newArr[] = $item;
            }

        }
        return $newArr;
    }

    // 修改后修复栏目
    public function repair_cat($catid, $mid = '') {

        $dir = $mid ? $mid : (APP_DIR ? APP_DIR : 'share');

        // 存储单个数据
        $cat = $this->table(SITE_ID.'_'.$dir.'_category')->get($catid);
        $cat['setting'] = dr_string2array($cat['setting']);
        $this->categorys = [
            $catid => $cat,
        ] ;
        $this->cache($catid, $dir);
    }

    // 同步修复栏目
    public function repair_sync($cat, $mid = '') {

        $dir = $mid ? $mid : (APP_DIR ? APP_DIR : 'share');
        if (!$this->categorys) {
            $_data = $this->table(SITE_ID.'_'.$dir.'_category')->where('disabled', 0)->order_by('displayorder ASC,id ASC')->getAll();
            if (!$_data) {
                return;
            }
            // 全部栏目数据
            $this->categorys = [];
            foreach ($_data as $t) {
                $this->categorys[$t['id']] = [
                    'id' => $t['id'],
                    'pid' => (int)$t['pid'],
                    'pids' => $t['pids'],
                    'child' => $t['child'],
                    'childids' => $t['childids'],
                    'dirname' => $t['dirname'],
                    'pdirname' => $t['pdirname'],
                ];
                if (isset($t['is_ctable'])) {
                    $this->categorys[$t['id']]['is_ctable'] = $t['is_ctable'];
                }
            }
        }

        $this->categorys[$cat['id']]['pids'] = $this->_get_pids($cat['id']);
        $this->categorys[$cat['id']]['childids'] = $this->_get_childids($dir, $cat['id']);
        $this->categorys[$cat['id']]['child'] = is_numeric($this->categorys[$cat['id']]['childids']) ? 0 : 1;
        $this->categorys[$cat['id']]['pdirname'] = $this->_get_pdirname($cat['id']);

        $update = [
            'pid' => (int)$this->categorys[$cat['id']]['pid'],
            'pids' => $this->categorys[$cat['id']]['pids'],
            'child' => $this->categorys[$cat['id']]['child'],
            'childids' => $this->categorys[$cat['id']]['childids'],
            'pdirname' => $this->categorys[$cat['id']]['pdirname']
        ];
        if (isset($cat['is_ctable']) && $cat['pid'] > 0) {
            $pid = explode(',', $cat['pids']);
            $tid = isset($pid[1]) ? $pid[1] : $cat['id'];
            $update['is_ctable'] = $this->categorys[$tid]['is_ctable'];
        }

        $this->table(SITE_ID.'_'.$dir.'_category')->update($cat['id'], $update);

        // 单独存储栏目关系
        if ($this->categorys[$cat['id']]['child']) {
            \Phpcmf\Service::L('cache')->set_file($cat['id'], $this->_get_nextids($dir, $cat['id']), 'module/category-'.SITE_ID.'-'.$dir.'-child');
        }
    }

    //生成顶级栏目下级
    public function repair_top_nextids($dir) {
        \Phpcmf\Service::L('cache')->set_file(0, $this->_get_nextids($dir, 0), 'module/category-'.SITE_ID.'-'.$dir.'-child');
    }

    // 修复栏目数据格式分组存储
    public function repair_data($dirname, $psize) {

        $_data = $this->table(SITE_ID.'_'.$dirname.'_category')->where('disabled', 0)->order_by('displayorder ASC,id ASC')->getAll();
        if (!$_data) {
            return 0;
        }

        // 全部栏目数据
        $data = $dir = $rt = [];
        foreach ($_data as $t) {
            $t['setting'] = dr_string2array($t['setting']);
            $dir[$t['dirname']] = $t['id'];
            $rt[$t['id']] = [
                'id' => $t['id'],
                'pid' => $t['pid'],
                'pids' => $t['pids'],
                'child' => $t['child'],
                'childids' => $t['childids'],
                'dirname' => $t['dirname'],
                'pdirname' => $t['pdirname'],
            ];
            if (isset($t['is_ctable'])) {
                $rt[$t['id']]['is_ctable'] = (int)$t['is_ctable'];
            }
            $data[$t['id']] = $t;
        }

        \Phpcmf\Service::L('cache')->set_auth_data('category-page-'.$dirname, array_chunk($rt, $psize), SITE_ID);
        \Phpcmf\Service::L('cache')->set_auth_data('category-data-'.$dirname, $data, SITE_ID);
        \Phpcmf\Service::L('cache')->set_auth_data('category-dir-'.$dirname, $dir, SITE_ID);

        return count($rt);
    }

    /**
     * 获取栏目的下级子栏目
     */
    protected function _get_nextids($dirname, $catid) {

        $rt = [];
        $data = $this->table(SITE_ID.'_'.$dirname.'_category')->where('pid='.$catid)->order_by('displayorder ASC,id ASC')->getAll();
        if ($data) {
            foreach($data as $cat) {
                $rt[] = $cat['id'];
            }
        }

        return $rt;
    }

    /**
     * 获取栏目的上级父栏目
     */
    public function get_pdirname($dirname, $catid) {

        $rt = '';
        $data = $this->table(SITE_ID.'_'.$dirname.'_category')->get($catid);
        if ($data) {
            if ($data['pid']) {
                $rs = $this->get_pdirname($dirname, $data['pid']);
                if ($rs) {
                    $rt.= trim($rs, '/').'/';
                }
            }
            $rt.= $data['dirname'].'/';
        }

        return $rt;
    }

    /**
     * 获取栏目的全部子栏目
     */
    private function _get_childids($dirname, $catid, $n = 1) {

        $childids = $catid;

        if ($n > 100 || !is_array($this->categorys) || !isset($this->categorys[$catid])) {
            return $childids;
        }

        $data = $this->table(SITE_ID.'_'.$dirname.'_category')->where('pid>0 and id<>'.$catid.' and pid='.$catid)->getAll();
        if ($data) {
            foreach($data as $cat) {
                $childids.= ','.$this->_get_childids($dirname, $cat['id'], ++$n);
            }
        }

        return $childids;
    }

    /**
     * 获取父栏目ID列表
     */
    private function _get_pids($catid, $pids = '', $n = 1) {

        if ($n > 100 || !is_array($this->categorys)
            || !isset($this->categorys[$catid])) {
            return FALSE;
        }

        $pid = $this->categorys[$catid]['pid'];
        $pids = $pids ? $pid.','.$pids : $pid;
        if ($pid) {
            $pids = $this->_get_pids($pid, $pids, ++$n);
        }

        return $pids;
    }

    /**
     * 所有父目录
     */
    private function _get_pdirname($catid) {

        if ($this->categorys[$catid]['pid'] == 0) {
            return '';
        }

        $t = $this->categorys[$catid];
        $pids = $t['pids'];
        $pids = explode(',', $pids);
        $catdirs = [];

        krsort($pids);

        foreach ($pids as $id) {
            if ($id == 0) {
                continue;
            }
            $catdirs[] = $this->categorys[$id]['dirname'];
            if ($this->categorys[$id]['pdirname'] == '') {
                break;
            }
        }

        krsort($catdirs);

        return implode('/', $catdirs).'/';
    }


    //生成常用栏目选择框
    public function cat_select($mid) {

        $data = \Phpcmf\Service::L('cache')->get_file(
            'cache',
            'module/category-'.SITE_ID.'-'.$mid.'-data'
        );
        $str = \Phpcmf\Service::L('tree', 'module')->select_category($data);
        \Phpcmf\Service::L('cache')->set_file(
            'cat',
            $str,
            'module/category-'.SITE_ID.'-'.$mid.'-select'
        );
    }

    // 生成主栏目选择框
    public function cat_ismain_select($mid) {

        $data = \Phpcmf\Service::L('cache')->get_file(
            'cache',
            'module/category-'.SITE_ID.'-'.$mid.'-data'
        );
        $str = \Phpcmf\Service::L('tree', 'module')->ismain(true)->select_category($data);
        \Phpcmf\Service::L('cache')->set_file(
            'cat_ismain',
            $str,
            'module/category-'.SITE_ID.'-'.$mid.'-select'
        );
    }

    // 生成后台发布选择框
    public function cat_admin_post_select($mid) {

        $data = \Phpcmf\Service::L('cache')->get_file(
            'cache',
            'module/category-'.SITE_ID.'-'.$mid.'-data'
        );
        $str = \Phpcmf\Service::L('tree', 'module')->select_category($data, 1, 1);
        \Phpcmf\Service::L('cache')->set_file(
            'admin_post',
            $str,
            'module/category-'.SITE_ID.'-'.$mid.'-select'
        );

    }

}