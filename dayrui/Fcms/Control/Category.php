<?php namespace Phpcmf\Control;
/**
 * www.xunruicms.com
 * 迅睿内容管理框架系统（简称：迅睿CMS）
 * 本文件是框架系统文件，二次开发时不可以修改本文件
 **/

class Category extends \Phpcmf\Home\Module {

	public function index() {

        if (IS_POST) {
            $this->_json(0, '禁止提交，请检查提交地址是否有误');
        }

		$id = (int)\Phpcmf\Service::L('input')->get('id');
		$dir = dr_safe_replace(\Phpcmf\Service::L('input')->get('dir'));
		$page = max(1, (int)\Phpcmf\Service::L('input')->get('page'));

		$module = \Phpcmf\Service::L('cache')->get('module-'.SITE_ID.'-share');
		if (!$module) {
            $module = \Phpcmf\Service::L('cache')->get_file('module-'.SITE_ID.'-share');
            if (!$module) {
                $this->_msg(0, dr_lang('共享栏目缓存不存在'));
                return;
            }
        }

        // 挂钩点
        $rt2 = \Phpcmf\Hooks::trigger_callback('module_category_share');
        if ($rt2 && isset($rt2['code']) && $rt2['code']) {
            $id = $rt2['data'];
        }

		if ($id) {
            $cat = dr_cat_value($module['mid'], $id);
			if (!$cat) {
			    $this->goto_404_page(dr_lang('栏目（%s）不存在', $id));
            }
		} elseif ($dir) {
			$id = \Phpcmf\Service::L('category', 'module')->get_catid($module['mid'], $dir);
            $cat = dr_cat_value($module['mid'], $id);
			if (!$cat) {
                if (isset($module['category_dir'][$dir])) {
                    $id = (int)$module['category_dir'][$dir];
                    $cat = dr_cat_value($module['mid'], $id);
                } else {
                    // 无法通过目录找到栏目时，尝试多及目录
                    $all = \Phpcmf\Service::L('category', 'module')->get_category($module['mid']);
                    foreach ($all as $t) {
                        if ($t['setting']['urlrule']) {
                            $rule = \Phpcmf\Service::L('cache')->get('urlrule', $t['setting']['urlrule']);
                            $rule['value']['catjoin'] = '/';
                            if ($rule['value']['catjoin'] && strpos($dir, $rule['value']['catjoin'])) {
                                $dir = trim(strchr($dir, $rule['value']['catjoin']), $rule['value']['catjoin']);
                                $sid = \Phpcmf\Service::L('category', 'module')->get_catid($module['mid'], $dir);
                                if ($sid) {
                                    $id = $sid;
                                    $cat = dr_cat_value($module['mid'], $id);
                                    break;
                                }
                            }
                        }
                    }
                    // 返回无法找到栏目
                    if (!$id) {
                        $this->goto_404_page(dr_lang('栏目（%s）不存在', $dir));
                    }
                }
			}
		} else {
            $this->goto_404_page(dr_lang('栏目参数不存在'));
		}

		// 初始化模块
        if ($cat['tid'] == 1) {
		    if ($cat['mid']) {
                $this->_module_init($cat['mid']);
            } else {
                $this->goto_404_page(dr_lang('栏目所属模块不存在'));
            }
        } else {
            $this->_module_init('share');
        }
		
		// 调用栏目方法
		$this->_Category($id, $dir, $page);
	}

}
