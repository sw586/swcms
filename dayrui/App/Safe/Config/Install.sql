DROP TABLE IF EXISTS `{dbprefix}app_login`;
CREATE TABLE IF NOT EXISTS `{dbprefix}app_login` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `uid` mediumint(8) unsigned DEFAULT NULL COMMENT '会员uid',
    `is_login` int(10) unsigned DEFAULT NULL COMMENT '是否首次登录',
    `is_repwd` int(10) unsigned DEFAULT NULL COMMENT '是否重置密码',
    `updatetime` int(10) unsigned NOT NULL COMMENT '修改密码时间',
    `logintime` int(10) unsigned NOT NULL COMMENT '最近登录时间',
    PRIMARY KEY (`id`),
    KEY `uid` (`uid`),
    KEY `logintime` (`logintime`),
    KEY `updatetime` (`updatetime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='账号记录';