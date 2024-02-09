<?php namespace Phpcmf\Model\Module;
/**
 * {{www.xunruicms.com}}
 * {{迅睿内容管理框架系统}}
 * 本文件是框架系统文件，二次开发时不可以修改本文件，可以通过继承类方法来重写此文件
 **/

// 审核类
class Verify extends \Phpcmf\Model {

    // 验证是否具有审核状态
    public function _get_verify_status_edit($vid, $status) {

        if (dr_in_array(1, $this->admin['roleid'])) {
            return 1; // 超管用户
        } elseif ($status == 0) {
            return 1; // 退稿的可以看到
        } elseif (\Phpcmf\Service::M('auth')->is_post_user()) {
            return 0; // 投稿者不允许编辑审核
        }

        if (!IS_USE_MEMBER) {
            return 1;
        }

        $verify = \Phpcmf\Service::C()->get_cache('verify');
        if (!$verify) {
            return 0; // 没有审核流程时
        }

        $my = [];
        foreach ($verify as $t) {
            if ($t['value']['role']) {
                $rid = [];
                foreach ($t['value']['role'] as $c) {
                    if (is_array($c)) {
                        $rid = array_merge($rid, $c);
                    } elseif (is_numeric($c)) {
                        $rid[] = $c;
                    }
                }
                if (dr_array_intersect($rid, $this->admin['roleid'])) {
                    $my[] = $t['id'];
                }
            }
        }

        if (!$my) {
            // 此管理员没有管理权限
            return 0;
        }

        // 有权限了
        if (dr_in_array($vid, $my)) {
            return 1;
        }

        return 0;
    }

    // 获取当前栏目的时候流程
    public function _get_verify($vid) {

        $rt = [];
        $cache = \Phpcmf\Service::C()->get_cache('verify');
        if ($cache && $vid && $cache[$vid]) {
            $verify = $cache[$vid];
            if ($verify['value']['role']) {
                $role = \Phpcmf\Service::C()->get_cache('auth');
                foreach ($verify['value']['role'] as $id => $rid) {
                    if (!is_array($rid)) {
                        $rt[$id] = [
                            'rid' => $rid,
                            'name' => dr_lang($role[$rid]['name'] ? $role[$rid]['name'] : '管理员'),
                        ];
                    } else {
                        $ns = [];
                        foreach ($rid as $r) {
                            $ns[] = dr_lang($role[$r]['name'] ? $role[$r]['name'] : '管理员');
                        }
                        $ns = array_unique($ns);
                        $rt[$id] = [
                            'rid' => $r,
                            'name' => implode('、', $ns),
                        ];
                    }
                }
            }
        }

        $rt[9] = [
            'name' => dr_lang('完成'),
        ];

        return $rt;
    }

    // 审核时候的权限组,返回可用权限组的id
    // array(
    //     *      to_uid 指定人
    //     *      to_rid 指定角色组
    //     * )
    public function _get_verify_roleid($catid, $status, $member) {

        $verify = \Phpcmf\Service::C()->get_cache('verify');
        if (!$verify) {
            return ['to_uid' => 0, 'to_rid' => 0, 'verify_id' => 0];
        }

        if (IS_MEMBER) {
            // 前端找用户设置
            $auth = \Phpcmf\Service::L('member_auth', 'member')->category_auth(\Phpcmf\Service::C()->module, $catid, 'verify', $member);
        } else {
            // 后台投稿者
            $auth = \Phpcmf\Service::M('auth')->is_post_user_status();
        }

        if ($auth && isset($verify[$auth]) && $verify[$auth]) {
            $v = $verify[$auth];
            $status = max(1, $status);
            if (isset($v['value']['role'][$status]) && $v['value']['role'][$status]) {
                if (is_array($v['value']['role'][$status])) {
                    return ['to_uid' => 0, 'to_rid' => implode(',', $v['value']['role'][$status]), 'verify_id' => $v['id']];
                } else {
                    return ['to_uid' => 0, 'to_rid' => $v['value']['role'][$status], 'verify_id' => $v['id']];
                }
            }
        }

        return ['to_uid' => 0, 'to_rid' => 0, 'verify_id' => 0];
    }

    // 后台内容审核列表的权限的sql语句
    public function get_admin_verify_status_list() {

        if (dr_in_array(1, \Phpcmf\Service::C()->admin['roleid'])) {
            return '`status`>=0'; // 超管用户
        } elseif (!IS_USE_MEMBER && !\Phpcmf\Service::M('auth')->is_post_user()) {
            return '`status`>=0'; // 普通管理员
        }

        $verify = \Phpcmf\Service::C()->get_cache('verify');
        if (!$verify) {
            return '`status`=0'; // 没有审核流程时
        }

        $where = [];
        foreach ($verify as $t) {
            if ($t['value']['role']) {
                foreach ($t['value']['role'] as $status => $rid) {
                    if (is_array($rid)) {
                        if (dr_array_intersect($rid, \Phpcmf\Service::C()->admin['roleid'])) {
                            $where[] = '(`status`='.$status.' and `vid`='.$t['id'].')';
                        }
                    } else {
                        if (dr_in_array($rid, \Phpcmf\Service::C()->admin['roleid'])) {
                            $where[] = '(`status`='.$status.' and `vid`='.$t['id'].')';
                        }
                    }
                }
            }
        }

        // 此管理员没有管理权限
        if (!$where) {
            return 'status=0';
        }

        return '`status` = 0 OR '.implode(' OR ', $where);
    }

}