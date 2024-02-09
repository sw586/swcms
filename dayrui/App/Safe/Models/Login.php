<?php namespace Phpcmf\Model\Safe;

class Login extends \Phpcmf\Model {

    private $log;

    public function get_log($uid) {

        if (!isset($this->log[$uid]) || !$this->log[$uid]) {
            if (CI_DEBUG) {
                $table = $this->dbprefix('app_login');
                if (!$this->db->tableExists($table)) {
                    $this->query("
                    CREATE TABLE IF NOT EXISTS `".$table."` (
                        `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                        `uid` mediumint(8) unsigned DEFAULT NULL COMMENT '会员uid',
                        `is_login` int(10) unsigned DEFAULT NULL COMMENT '是否首次登录',
                        `is_repwd` int(10) unsigned DEFAULT NULL COMMENT '是否重置密码',
                        `updatetime` int(10) unsigned DEFAULT NULL COMMENT '修改密码时间',
                        `logintime` int(10) unsigned DEFAULT NULL COMMENT '最近登录时间',
                        PRIMARY KEY (`id`),
                        KEY `uid` (`uid`),
                        KEY `logintime` (`logintime`),
                        KEY `updatetime` (`updatetime`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='账号记录';
                    ");
                }
            }
            $row = $this->table('app_login')->where('uid', $uid)->getRow();
            if (!$row) {
                $row = [
                    'uid' => $uid,
                    'is_login' => 0,
                    'is_repwd' => 0,
                    'updatetime' => 0,
                    'logintime' => 0,
                ];
                $rt = $this->table('app_login')->insert($row);
                $row['id'] = $rt['code'];
            }
            $this->log[$uid] = $row;
        }

        return $this->log[$uid];
    }

    // 缓存
    public function cache($siteid = SITE_ID) {

        $table = $this->dbprefix('app_login');
        if (!\Phpcmf\Service::M()->db->tableExists($table)) {
            $sql = "CREATE TABLE IF NOT EXISTS `{dbprefix}app_login` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `uid` mediumint(8) unsigned DEFAULT NULL COMMENT '会员uid',
    `is_login` int(10) unsigned DEFAULT NULL COMMENT '是否首次登录',
    `is_repwd` int(10) unsigned DEFAULT NULL COMMENT '是否重置密码',
    `updatetime` int(10) unsigned DEFAULT NULL COMMENT '修改密码时间',
    `logintime` int(10) unsigned DEFAULT NULL COMMENT '最近登录时间',
    PRIMARY KEY (`id`),
    KEY `uid` (`uid`),
    KEY `logintime` (`logintime`),
    KEY `updatetime` (`updatetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='账号记录';";
            $this->query_all(str_replace('{dbprefix}',  $this->prefix, $sql));
        }

    }


}