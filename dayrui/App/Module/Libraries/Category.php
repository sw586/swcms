<?php namespace Phpcmf\Library\Module;
/**
 * {{www.xunruicms.com}}
 * {{迅睿内容管理框架系统}}
 * 本文件是框架系统文件，二次开发时不可以修改本文件，可以通过继承类方法来重写此文件
 **/

class Category {

    protected $ismain = 0;
    protected $clearbs = 0;

    public function ismain($v) {
        $this->ismain = $v;
        return $this;
    }

    public function clearbs($v) {
        $this->clearbs = $v;
        return $this;
    }

    public function get_mid($mid) {
        if (!$mid || $mid == 'share') {
            return 'share';
        }
        return \Phpcmf\Service::L('cache')->get_file(
            'mid',
            'module/category-'.SITE_ID.'-'.$mid.'-data'
        );
    }

    public function select($mid, $id = '', $str = '', $default = ' -- ', $onlysub = 0, $is_push = 0, $is_first = 0) {

        $catid = 0;

        $dir = 'module/category-'.SITE_ID.'-'.$mid.'-select/';
        $name = md5('v2'.$this->ismain.$mid.$str.$default.$onlysub.$is_push.$is_first.($this->ismain ? 1 : 0).($this->clearbs ? 1 : 0));
        if ($is_push) {
            $name.= \Phpcmf\Service::C()->uid;
            if (IS_ADMIN) {
                $name.= 'admin'.md5(\Phpcmf\Service::C()->admin ? dr_array2string(\Phpcmf\Service::C()->admin['roleid']) : '1');
            } else {
                $name.= md5(\Phpcmf\Service::C()->member ? dr_array2string(\Phpcmf\Service::C()->member['authid']) : '2');
            }
        }

        $html = CI_DEBUG ? [] : \Phpcmf\Service::L('cache')->get_file($name, $dir);
        if (!$html) {
            $file = WRITEPATH.$dir.'ld.lock';
            if (is_file($file)) {
                if ($this->ismain) {
                    $data = $this->get_category_main($mid);
                } else {
                    $data = $this->get_category($mid);
                }
                $res = \Phpcmf\Service::L('tree', 'module')->mid($this->get_mid($mid))
                    ->select_category_json($data, $id, $str, $default, $onlysub, $is_push, $is_first, $name);
                if ($is_first) {
                    list($html, $catid) = $res;
                    \Phpcmf\Service::L('cache')->set_file($name.'_first', $catid, $dir);
                    if (!$id) {
                        $id = $catid;
                    }
                } else {
                    $html = $res;
                }
            } else {
                $category = \Phpcmf\Service::L('category', 'module')->get_category($mid);
                if ($is_first) {
                    list($html, $catid) = \Phpcmf\Service::L('Tree')->select_category(
                        $category,
                        $id,
                        $str,
                        $default, $onlysub, $is_push, $is_first
                    );
                } else {
                    $html = \Phpcmf\Service::L('Tree')->select_category(
                        $category,
                        $id,
                        $str,
                        $default, $onlysub, $is_push, $is_first
                    );
                }
            }

            $data && \Phpcmf\Service::L('cache')->set_file($name, $html, $dir);
        }

        $value = '0';
        if ($id) {
            if (is_array($id)) {
                $new = [];
                foreach ($id as $t) {
                    $new[] = (string)$t;
                }
                $value = json_encode($new);
            } else {
                $value = '\''.(int)$id.'\'';
            }
        }

        $html = str_replace('{category_value}', $value, $html);


        $this->ismain = false;
        $this->clearbs = false;

        if ($is_first) {
            return [$html, intval($catid ? $catid : \Phpcmf\Service::L('cache')->get_file($name.'_first', $dir))];
        } else {
            return $html;
        }

    }

    // 获取主栏目
    public function get_category_main($mid, $siteid = SITE_ID) {
        return \Phpcmf\Service::L('cache')->get_file(
            'main',
            'module/category-'.$siteid.'-'.$mid.'-data'
        );
    }

    // 获取全部栏目
    public function get_category($mid, $siteid = SITE_ID) {
        return \Phpcmf\Service::L('cache')->get_file(
            'cache',
            'module/category-'.$siteid.'-'.$mid.'-data'
        );
    }

    // 获取下级子栏目
    public function get_child($mid, $catid, $siteid = SITE_ID) {
        return \Phpcmf\Service::L('cache')->get_file(
            $catid,
            'module/category-'.$siteid.'-'.$mid.'-child'
        );
    }

    // 通过目录找id
    public function get_catid($mid, $dir, $siteid = SITE_ID) {
        $cats = \Phpcmf\Service::L('cache')->get_file(
            'dir',
            'module/category-'.$siteid.'-'.$mid.'-data'
        );
        return isset($cats[$dir]) ? $cats[$dir] : 0;
    }

    // 查询所属主栏目
    public function get_ismain_id($mid, $cat) {

        if ($cat['ismain']) {
            return $cat['id'];
        }

        if ($cat['pids']) {
            $arr = array_reverse(explode(',', $cat['pids']));
            foreach ($arr as $t) {
                if ($t) {
                    $my = dr_cat_value($mid, $t);
                    if ($my['ismain']) {
                        return $t;
                    }
                }
            }
        }

        return 0;
    }

}