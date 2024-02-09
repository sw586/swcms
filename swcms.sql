/*
 Navicat Premium Data Transfer

 Source Server         : localhost_3306
 Source Server Type    : MySQL
 Source Server Version : 80012
 Source Host           : localhost:3306
 Source Schema         : swcms

 Target Server Type    : MySQL
 Target Server Version : 80012
 File Encoding         : 65001

 Date: 09/02/2024 19:31:17
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for dr_1_news
-- ----------------------------
DROP TABLE IF EXISTS `dr_1_news`;
CREATE TABLE `dr_1_news`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `catid` smallint(5) UNSIGNED NOT NULL COMMENT '栏目id',
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '主题',
  `thumb` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '缩略图',
  `keywords` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '关键字',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '描述',
  `hits` int(10) UNSIGNED NULL DEFAULT NULL COMMENT '浏览数',
  `uid` int(10) UNSIGNED NOT NULL COMMENT '作者id',
  `author` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '笔名',
  `status` tinyint(2) NOT NULL COMMENT '状态(已废弃)',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '地址',
  `link_id` int(10) NOT NULL DEFAULT 0 COMMENT '同步id',
  `tableid` smallint(5) UNSIGNED NOT NULL COMMENT '附表id',
  `inputip` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '录入者ip',
  `inputtime` int(10) UNSIGNED NOT NULL COMMENT '录入时间',
  `updatetime` int(10) UNSIGNED NOT NULL COMMENT '更新时间',
  `displayorder` int(10) NULL DEFAULT 0 COMMENT '排序值',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`uid` ASC) USING BTREE,
  INDEX `catid`(`catid` ASC) USING BTREE,
  INDEX `link_id`(`link_id` ASC) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE,
  INDEX `updatetime`(`updatetime` ASC) USING BTREE,
  INDEX `hits`(`hits` ASC) USING BTREE,
  INDEX `category`(`catid` ASC, `status` ASC) USING BTREE,
  INDEX `displayorder`(`displayorder` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '内容主表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_1_news
-- ----------------------------
INSERT INTO `dr_1_news` VALUES (1, 1, '测试啊', '1', '', '阿斯顿', 3, 1, '创始人', 9, '/index.php?c=show&id=1', 0, 0, '127.0.0.1-56686', 1707476891, 1707476922, 0);

-- ----------------------------
-- Table structure for dr_1_news_category
-- ----------------------------
DROP TABLE IF EXISTS `dr_1_news_category`;
CREATE TABLE `dr_1_news_category`  (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` mediumint(8) UNSIGNED NOT NULL DEFAULT 0 COMMENT '上级id',
  `pids` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '所有上级id',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '栏目名称',
  `dirname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '栏目目录',
  `pdirname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '上级目录',
  `child` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否有下级',
  `disabled` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否禁用',
  `ismain` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否主栏目',
  `childids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '下级所有id',
  `thumb` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '栏目图片',
  `show` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否显示',
  `setting` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '属性配置',
  `displayorder` mediumint(8) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `show`(`show` ASC) USING BTREE,
  INDEX `disabled`(`disabled` ASC) USING BTREE,
  INDEX `ismain`(`ismain` ASC) USING BTREE,
  INDEX `module`(`pid` ASC, `displayorder` ASC, `id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '栏目表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_1_news_category
-- ----------------------------

-- ----------------------------
-- Table structure for dr_1_news_category_data
-- ----------------------------
DROP TABLE IF EXISTS `dr_1_news_category_data`;
CREATE TABLE `dr_1_news_category_data`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` mediumint(8) UNSIGNED NOT NULL COMMENT '作者uid',
  `catid` int(3) UNSIGNED NOT NULL COMMENT '栏目id',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`uid` ASC) USING BTREE,
  INDEX `catid`(`catid` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '栏目模型表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_1_news_category_data
-- ----------------------------

-- ----------------------------
-- Table structure for dr_1_news_data_0
-- ----------------------------
DROP TABLE IF EXISTS `dr_1_news_data_0`;
CREATE TABLE `dr_1_news_data_0`  (
  `id` int(10) UNSIGNED NOT NULL,
  `uid` mediumint(8) UNSIGNED NOT NULL COMMENT '作者uid',
  `catid` smallint(5) UNSIGNED NOT NULL COMMENT '栏目id',
  `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '内容',
  UNIQUE INDEX `id`(`id` ASC) USING BTREE,
  INDEX `uid`(`uid` ASC) USING BTREE,
  INDEX `catid`(`catid` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '内容附表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_1_news_data_0
-- ----------------------------
INSERT INTO `dr_1_news_data_0` VALUES (1, 1, 1, '&lt;p&gt;&lt;u&gt;阿斯顿&lt;/u&gt;&lt;img  title=&quot;微信图片_20240207123808&quot; alt=&quot;微信图片_20240207123808&quot; src=&quot;/uploadfile/202402/22fa1e97162946d.png&quot;&gt;&lt;/p&gt;');

-- ----------------------------
-- Table structure for dr_1_news_draft
-- ----------------------------
DROP TABLE IF EXISTS `dr_1_news_draft`;
CREATE TABLE `dr_1_news_draft`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cid` int(10) UNSIGNED NOT NULL COMMENT '内容id',
  `uid` mediumint(8) UNSIGNED NOT NULL COMMENT '作者uid',
  `catid` mediumint(8) UNSIGNED NOT NULL COMMENT '栏目id',
  `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '具体内容',
  `inputtime` int(10) UNSIGNED NOT NULL COMMENT '录入时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`uid` ASC) USING BTREE,
  INDEX `cid`(`cid` ASC) USING BTREE,
  INDEX `catid`(`catid` ASC) USING BTREE,
  INDEX `inputtime`(`inputtime` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '内容草稿表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_1_news_draft
-- ----------------------------

-- ----------------------------
-- Table structure for dr_1_news_flag
-- ----------------------------
DROP TABLE IF EXISTS `dr_1_news_flag`;
CREATE TABLE `dr_1_news_flag`  (
  `flag` tinyint(2) UNSIGNED NOT NULL DEFAULT 1 COMMENT '文档标记id',
  `id` int(10) UNSIGNED NOT NULL COMMENT '文档内容id',
  `uid` mediumint(8) UNSIGNED NOT NULL COMMENT '作者uid',
  `catid` mediumint(8) UNSIGNED NOT NULL COMMENT '栏目id',
  INDEX `flag`(`flag` ASC, `id` ASC, `uid` ASC) USING BTREE,
  INDEX `catid`(`catid` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '标记表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_1_news_flag
-- ----------------------------

-- ----------------------------
-- Table structure for dr_1_news_hits
-- ----------------------------
DROP TABLE IF EXISTS `dr_1_news_hits`;
CREATE TABLE `dr_1_news_hits`  (
  `id` int(10) UNSIGNED NOT NULL COMMENT '文章id',
  `hits` int(10) UNSIGNED NOT NULL COMMENT '总点击数',
  `day_hits` int(10) UNSIGNED NOT NULL COMMENT '本日点击',
  `week_hits` int(10) UNSIGNED NOT NULL COMMENT '本周点击',
  `month_hits` int(10) UNSIGNED NOT NULL COMMENT '本月点击',
  `year_hits` int(10) UNSIGNED NOT NULL COMMENT '年点击量',
  `day_time` int(10) UNSIGNED NOT NULL COMMENT '本日',
  `week_time` int(10) UNSIGNED NOT NULL COMMENT '本周',
  `month_time` int(10) UNSIGNED NOT NULL COMMENT '本月',
  `year_time` int(10) UNSIGNED NOT NULL COMMENT '年',
  UNIQUE INDEX `id`(`id` ASC) USING BTREE,
  INDEX `day_hits`(`day_hits` ASC) USING BTREE,
  INDEX `week_hits`(`week_hits` ASC) USING BTREE,
  INDEX `month_hits`(`month_hits` ASC) USING BTREE,
  INDEX `year_hits`(`year_hits` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '时段点击量统计' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_1_news_hits
-- ----------------------------
INSERT INTO `dr_1_news_hits` VALUES (1, 3, 2, 2, 2, 2, 1707476916, 1707476916, 1707476916, 1707476916);

-- ----------------------------
-- Table structure for dr_1_news_index
-- ----------------------------
DROP TABLE IF EXISTS `dr_1_news_index`;
CREATE TABLE `dr_1_news_index`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` mediumint(8) UNSIGNED NOT NULL COMMENT '作者uid',
  `catid` mediumint(8) UNSIGNED NOT NULL COMMENT '栏目id',
  `status` tinyint(2) NOT NULL COMMENT '审核状态',
  `inputtime` int(10) UNSIGNED NOT NULL COMMENT '录入时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`uid` ASC) USING BTREE,
  INDEX `catid`(`catid` ASC) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE,
  INDEX `inputtime`(`inputtime` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '内容索引表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_1_news_index
-- ----------------------------
INSERT INTO `dr_1_news_index` VALUES (1, 1, 1, 9, 1707476922);

-- ----------------------------
-- Table structure for dr_1_news_recycle
-- ----------------------------
DROP TABLE IF EXISTS `dr_1_news_recycle`;
CREATE TABLE `dr_1_news_recycle`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `cid` int(10) UNSIGNED NOT NULL COMMENT '内容id',
  `uid` mediumint(8) UNSIGNED NOT NULL COMMENT '作者uid',
  `catid` tinyint(3) UNSIGNED NOT NULL COMMENT '栏目id',
  `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '具体内容',
  `result` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '删除理由',
  `inputtime` int(10) UNSIGNED NOT NULL COMMENT '录入时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`uid` ASC) USING BTREE,
  INDEX `cid`(`cid` ASC) USING BTREE,
  INDEX `catid`(`catid` ASC) USING BTREE,
  INDEX `inputtime`(`inputtime` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '内容回收站表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_1_news_recycle
-- ----------------------------

-- ----------------------------
-- Table structure for dr_1_news_search
-- ----------------------------
DROP TABLE IF EXISTS `dr_1_news_search`;
CREATE TABLE `dr_1_news_search`  (
  `id` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `catid` mediumint(8) UNSIGNED NOT NULL COMMENT '栏目id',
  `params` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '参数数组',
  `keyword` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '关键字',
  `contentid` int(10) UNSIGNED NOT NULL COMMENT '字段改成了结果数量值',
  `inputtime` int(10) UNSIGNED NOT NULL COMMENT '搜索时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `id`(`id` ASC) USING BTREE,
  INDEX `catid`(`catid` ASC) USING BTREE,
  INDEX `keyword`(`keyword` ASC) USING BTREE,
  INDEX `inputtime`(`inputtime` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '搜索表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_1_news_search
-- ----------------------------

-- ----------------------------
-- Table structure for dr_1_news_time
-- ----------------------------
DROP TABLE IF EXISTS `dr_1_news_time`;
CREATE TABLE `dr_1_news_time`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` mediumint(8) UNSIGNED NOT NULL COMMENT '作者uid',
  `catid` mediumint(8) UNSIGNED NOT NULL COMMENT '栏目id',
  `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '具体内容',
  `result` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '处理结果',
  `posttime` int(10) UNSIGNED NOT NULL COMMENT '定时发布时间',
  `inputtime` int(10) UNSIGNED NOT NULL COMMENT '录入时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`uid` ASC) USING BTREE,
  INDEX `catid`(`catid` ASC) USING BTREE,
  INDEX `posttime`(`posttime` ASC) USING BTREE,
  INDEX `inputtime`(`inputtime` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '内容定时发布表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_1_news_time
-- ----------------------------

-- ----------------------------
-- Table structure for dr_1_news_verify
-- ----------------------------
DROP TABLE IF EXISTS `dr_1_news_verify`;
CREATE TABLE `dr_1_news_verify`  (
  `id` int(10) UNSIGNED NOT NULL,
  `uid` mediumint(8) UNSIGNED NOT NULL COMMENT '作者uid',
  `vid` tinyint(2) NOT NULL COMMENT '审核id号',
  `isnew` tinyint(1) UNSIGNED NOT NULL COMMENT '是否新增',
  `islock` tinyint(1) UNSIGNED NOT NULL COMMENT '是否锁定',
  `author` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '作者',
  `catid` mediumint(8) UNSIGNED NOT NULL COMMENT '栏目id',
  `status` tinyint(2) NOT NULL COMMENT '审核状态',
  `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '具体内容',
  `backuid` mediumint(8) UNSIGNED NOT NULL COMMENT '操作人uid',
  `backinfo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '操作退回信息',
  `inputtime` int(10) UNSIGNED NOT NULL COMMENT '录入时间',
  UNIQUE INDEX `id`(`id` ASC) USING BTREE,
  INDEX `uid`(`uid` ASC) USING BTREE,
  INDEX `vid`(`vid` ASC) USING BTREE,
  INDEX `catid`(`catid` ASC) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE,
  INDEX `inputtime`(`inputtime` ASC) USING BTREE,
  INDEX `backuid`(`backuid` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '内容审核表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_1_news_verify
-- ----------------------------

-- ----------------------------
-- Table structure for dr_1_share_category
-- ----------------------------
DROP TABLE IF EXISTS `dr_1_share_category`;
CREATE TABLE `dr_1_share_category`  (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tid` tinyint(1) NOT NULL COMMENT '栏目类型，0单页，1模块，2外链',
  `pid` smallint(5) UNSIGNED NOT NULL DEFAULT 0 COMMENT '上级id',
  `mid` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模块目录',
  `pids` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '所有上级id',
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '栏目名称',
  `dirname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '栏目目录',
  `pdirname` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '上级目录',
  `child` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否有下级',
  `disabled` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '是否禁用',
  `ismain` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否主栏目',
  `childids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '下级所有id',
  `thumb` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '栏目图片',
  `show` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否显示',
  `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '单页内容',
  `setting` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '属性配置',
  `displayorder` smallint(5) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `mid`(`mid` ASC) USING BTREE,
  INDEX `tid`(`tid` ASC) USING BTREE,
  INDEX `show`(`show` ASC) USING BTREE,
  INDEX `disabled`(`disabled` ASC) USING BTREE,
  INDEX `ismain`(`ismain` ASC) USING BTREE,
  INDEX `dirname`(`dirname` ASC) USING BTREE,
  INDEX `module`(`pid` ASC, `displayorder` ASC, `id` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '共享模块栏目表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_1_share_category
-- ----------------------------
INSERT INTO `dr_1_share_category` VALUES (1, 1, 0, 'news', '0', '测试', 'ceshi', '', 0, 0, 1, '1', '', 1, '', '{\"disabled\":\"0\",\"linkurl\":\"\",\"getchild\":\"0\",\"notedit\":\"0\",\"urlrule\":null,\"seo\":{\"list_title\":\"[第{page}页{join}]{catpname}{join}{SITE_NAME}\",\"list_keywords\":\"\",\"list_description\":\"\"},\"template\":{\"pagesize\":\"20\",\"mpagesize\":\"20\",\"page\":\"page.html\",\"list\":\"list.html\",\"category\":\"category.html\",\"search\":\"search.html\",\"show\":\"show.html\"},\"cat_field\":null,\"module_field\":null}', 0);

-- ----------------------------
-- Table structure for dr_1_share_index
-- ----------------------------
DROP TABLE IF EXISTS `dr_1_share_index`;
CREATE TABLE `dr_1_share_index`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `mid` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模块目录',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `mid`(`mid` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '共享模块内容索引表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_1_share_index
-- ----------------------------
INSERT INTO `dr_1_share_index` VALUES (1, 'news');

-- ----------------------------
-- Table structure for dr_admin
-- ----------------------------
DROP TABLE IF EXISTS `dr_admin`;
CREATE TABLE `dr_admin`  (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` int(10) UNSIGNED NOT NULL COMMENT '管理员uid',
  `setting` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '相关配置',
  `usermenu` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '自定义面板菜单，序列化数组格式',
  `history` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '历史菜单，序列化数组格式',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uid`(`uid` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '管理员表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_admin
-- ----------------------------
INSERT INTO `dr_admin` VALUES (1, 1, '{\"admin_min\":0}', '', NULL);

-- ----------------------------
-- Table structure for dr_admin_login
-- ----------------------------
DROP TABLE IF EXISTS `dr_admin_login`;
CREATE TABLE `dr_admin_login`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` mediumint(8) UNSIGNED NULL DEFAULT NULL COMMENT '会员uid',
  `loginip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '登录Ip',
  `logintime` int(10) UNSIGNED NOT NULL COMMENT '登录时间',
  `useragent` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '客户端信息',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`uid` ASC) USING BTREE,
  INDEX `loginip`(`loginip` ASC) USING BTREE,
  INDEX `logintime`(`logintime` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 6 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '登录日志记录' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_admin_login
-- ----------------------------
INSERT INTO `dr_admin_login` VALUES (1, 1, '127.0.0.1', 1707476713, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36');
INSERT INTO `dr_admin_login` VALUES (2, 1, '127.0.0.1', 1707476743, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36');
INSERT INTO `dr_admin_login` VALUES (3, 1, '127.0.0.1', 1707477165, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36');
INSERT INTO `dr_admin_login` VALUES (4, 1, '127.0.0.1', 1707477198, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36');
INSERT INTO `dr_admin_login` VALUES (5, 1, '127.0.0.1', 1707477225, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36');

-- ----------------------------
-- Table structure for dr_admin_menu
-- ----------------------------
DROP TABLE IF EXISTS `dr_admin_menu`;
CREATE TABLE `dr_admin_menu`  (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` smallint(5) UNSIGNED NOT NULL COMMENT '上级菜单id',
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '菜单语言名称',
  `site` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '站点归属',
  `uri` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'uri字符串',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '外链地址',
  `mark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '菜单标识',
  `hidden` tinyint(1) UNSIGNED NULL DEFAULT NULL COMMENT '是否隐藏',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '图标标示',
  `displayorder` int(5) NULL DEFAULT NULL COMMENT '排序值',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `list`(`pid` ASC) USING BTREE,
  INDEX `displayorder`(`displayorder` ASC) USING BTREE,
  INDEX `mark`(`mark` ASC) USING BTREE,
  INDEX `hidden`(`hidden` ASC) USING BTREE,
  INDEX `uri`(`uri` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 68 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '后台菜单表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_admin_menu
-- ----------------------------
INSERT INTO `dr_admin_menu` VALUES (1, 0, '首页', '', '', '', 'home', 0, 'fa fa-home', -2);
INSERT INTO `dr_admin_menu` VALUES (2, 1, '我的面板', '', '', '', 'home-my', 0, 'fa fa-home', 0);
INSERT INTO `dr_admin_menu` VALUES (3, 2, '后台首页', '', 'home/main', '', '', 0, 'fa fa-home', 0);
INSERT INTO `dr_admin_menu` VALUES (4, 2, '资料修改', '', 'api/my', '', '', 0, 'fa fa-user', 0);
INSERT INTO `dr_admin_menu` VALUES (5, 2, '系统更新', '', 'cache/index', '', '', 0, 'fa fa-refresh', 0);
INSERT INTO `dr_admin_menu` VALUES (6, 2, '应用市场', '', 'api/app', '', '', 0, 'fa fa-puzzle-piece', 0);
INSERT INTO `dr_admin_menu` VALUES (7, 0, '系统', '', '', '', 'system', 0, 'fa fa-globe', -2);
INSERT INTO `dr_admin_menu` VALUES (8, 7, '系统维护', '', '', '', 'system-wh', 0, 'fa fa-cog', 0);
INSERT INTO `dr_admin_menu` VALUES (9, 8, '系统参数', '', 'system/index', '', '', 0, 'fa fa-cog', 0);
INSERT INTO `dr_admin_menu` VALUES (10, 8, '系统缓存', '', 'system_cache/index', '', '', 0, 'fa fa-clock-o', 0);
INSERT INTO `dr_admin_menu` VALUES (11, 8, '附件设置', '', 'attachment/index', '', '', 0, 'fa fa-folder', 0);
INSERT INTO `dr_admin_menu` VALUES (12, 8, '存储策略', '', 'attachment/remote_index', '', '', 0, 'fa fa-cloud', 0);
INSERT INTO `dr_admin_menu` VALUES (13, 8, '短信设置', '', 'sms/index', '', '', 0, 'fa fa-envelope', 0);
INSERT INTO `dr_admin_menu` VALUES (14, 8, '邮件设置', '', 'email/index', '', '', 0, 'fa fa-envelope-open', 0);
INSERT INTO `dr_admin_menu` VALUES (15, 8, '系统提醒', '', 'notice/index', '', '', 0, 'fa fa-bell', 0);
INSERT INTO `dr_admin_menu` VALUES (16, 8, '系统体检', '', 'check/index', '', '', 0, 'fa fa-wrench', 0);
INSERT INTO `dr_admin_menu` VALUES (17, 7, '日志管理', '', '', '', 'system-log', 0, 'fa fa-calendar', 0);
INSERT INTO `dr_admin_menu` VALUES (18, 17, '系统日志', '', 'error/index', '', '', 0, 'fa fa-shield', 0);
INSERT INTO `dr_admin_menu` VALUES (19, 17, '操作记录', '', 'system_log/index', '', '', 0, 'fa fa-calendar', 0);
INSERT INTO `dr_admin_menu` VALUES (20, 17, '密码错误', '', 'password_log/index', '', '', 0, 'fa fa-unlock-alt', 0);
INSERT INTO `dr_admin_menu` VALUES (21, 17, '短信错误', '', 'sms_log/index', '', '', 0, 'fa fa-envelope', 0);
INSERT INTO `dr_admin_menu` VALUES (22, 17, '邮件错误', '', 'email_log/index', '', '', 0, 'fa fa-envelope-open', 0);
INSERT INTO `dr_admin_menu` VALUES (23, 17, '慢查询日志', '', 'sql_log/index', '', '', 0, 'fa fa-database', 0);
INSERT INTO `dr_admin_menu` VALUES (24, 0, '设置', '', '', '', 'config', 0, 'fa fa-cogs', -2);
INSERT INTO `dr_admin_menu` VALUES (25, 24, '网站设置', '', '', '', 'config-web', 0, 'fa fa-cog', 0);
INSERT INTO `dr_admin_menu` VALUES (27, 0, '权限', '', '', '', 'auth', 0, 'fa fa-user-circle', 0);
INSERT INTO `dr_admin_menu` VALUES (28, 27, '后台权限', '', '', '', 'auth-admin', 0, 'fa fa-cog', 0);
INSERT INTO `dr_admin_menu` VALUES (29, 28, '后台菜单', '', 'menu/index', '', '', 0, 'fa fa-list-alt', 0);
INSERT INTO `dr_admin_menu` VALUES (30, 28, '简化菜单', '', 'min_menu/index', '', '', 0, 'fa fa-list', 0);
INSERT INTO `dr_admin_menu` VALUES (31, 28, '角色权限', '', 'role/index', '', '', 0, 'fa fa-users', 0);
INSERT INTO `dr_admin_menu` VALUES (32, 28, '角色账号', '', 'root/index', '', '', 0, 'fa fa-user', 0);
INSERT INTO `dr_admin_menu` VALUES (33, 0, '应用', '', '', '', 'app', 0, 'fa fa-puzzle-piece', 0);
INSERT INTO `dr_admin_menu` VALUES (34, 33, '应用插件', '', '', '', 'app-plugin', 0, 'fa fa-puzzle-piece', 0);
INSERT INTO `dr_admin_menu` VALUES (35, 34, '应用管理', '', 'cloud/local', '', '', 0, 'fa fa-folder', 0);
INSERT INTO `dr_admin_menu` VALUES (36, 34, '联动菜单', '', 'linkage/index', '', '', 0, 'fa fa-columns', 0);
INSERT INTO `dr_admin_menu` VALUES (37, 34, '任务队列', '', 'cron/index', '', '', 0, 'fa fa-indent', 0);
INSERT INTO `dr_admin_menu` VALUES (38, 34, '附件管理', '', 'attachments/index', '', '', 0, 'fa fa-folder', 0);
INSERT INTO `dr_admin_menu` VALUES (39, 0, '服务', '', '', '', 'cloud', 0, 'fa fa-cloud', 99);
INSERT INTO `dr_admin_menu` VALUES (40, 39, '项目服务', '', '', '', 'cloud-dayrui', 0, 'fa fa-cloud', 0);
INSERT INTO `dr_admin_menu` VALUES (41, 40, '我的项目', '', 'cloud/index', '', '', 0, 'fa fa-cog', 0);
INSERT INTO `dr_admin_menu` VALUES (42, 40, '服务工单', '', 'cloud/service', '', '', 0, 'fa fa-user-md', 0);
INSERT INTO `dr_admin_menu` VALUES (43, 40, '应用商城', '', 'cloud/app', '', '', 0, 'fa fa-puzzle-piece', 0);
INSERT INTO `dr_admin_menu` VALUES (44, 40, '模板商城', '', 'cloud/template', '', '', 0, 'fa fa-html5', 0);
INSERT INTO `dr_admin_menu` VALUES (45, 40, '版本升级', '', 'cloud/update', '', '', 0, 'fa fa-refresh', 0);
INSERT INTO `dr_admin_menu` VALUES (46, 40, '文件对比', '', 'cloud/bf', '', '', 0, 'fa fa-code', 0);
INSERT INTO `dr_admin_menu` VALUES (47, 25, '网站设置', '', 'module/site_config/index', '', 'app-module-47', 0, 'fa fa-cog', 0);
INSERT INTO `dr_admin_menu` VALUES (48, 25, '网站信息', '', 'module/site_param/index', '', 'app-module-48', 0, 'fa fa-edit', 0);
INSERT INTO `dr_admin_menu` VALUES (49, 25, '手机设置', '', 'module/site_mobile/index', '', 'app-module-49', 0, 'fa fa-mobile', 0);
INSERT INTO `dr_admin_menu` VALUES (50, 25, '域名绑定', '', 'module/site_domain/index', '', 'app-module-50', 0, 'fa fa-globe', 0);
INSERT INTO `dr_admin_menu` VALUES (51, 25, '图片设置', '', 'module/site_image/index', '', 'app-module-51', 0, 'fa fa-photo', 0);
INSERT INTO `dr_admin_menu` VALUES (52, 24, '内容设置', '', '', '', 'config-content', 0, 'fa fa-navicon', 0);
INSERT INTO `dr_admin_menu` VALUES (53, 52, '创建模块', '', 'module/module_create/index', '', 'app-module-53', 0, 'fa fa-plus', -1);
INSERT INTO `dr_admin_menu` VALUES (54, 52, '模块管理', '', 'module/module/index', '', 'app-module-54', 0, 'fa fa-gears', -1);
INSERT INTO `dr_admin_menu` VALUES (55, 52, '模块搜索', '', 'module/module_search/index', '', 'app-module-55', 0, 'fa fa-search', -1);
INSERT INTO `dr_admin_menu` VALUES (56, 24, 'SEO设置', '', '', '', 'config-seo', 0, 'fa fa-internet-explorer', 0);
INSERT INTO `dr_admin_menu` VALUES (57, 56, '站点SEO', '', 'module/seo_site/index', '', 'app-module-57', 0, 'fa fa-cog', 0);
INSERT INTO `dr_admin_menu` VALUES (58, 56, '模块SEO', '', 'module/seo_module/index', '', 'app-module-58', 0, 'fa fa-th-large', 0);
INSERT INTO `dr_admin_menu` VALUES (59, 56, '栏目SEO', '', 'module/seo_category/index', '', 'app-module-59', 0, 'fa fa-reorder', 0);
INSERT INTO `dr_admin_menu` VALUES (60, 56, 'URL规则', '', 'module/urlrule/index', '', 'app-module-60', 0, 'fa fa-link', 0);
INSERT INTO `dr_admin_menu` VALUES (61, 56, '伪静态解析', '', 'module/urlrule/rewrite_index', '', 'app-module-61', 0, 'bi bi-code-square', 0);
INSERT INTO `dr_admin_menu` VALUES (62, 0, '内容', '', '', '', 'content', 0, 'fa fa-th-large', -1);
INSERT INTO `dr_admin_menu` VALUES (63, 62, '内容管理', '', '', '', 'content-module', 0, 'fa fa-th-large', 0);
INSERT INTO `dr_admin_menu` VALUES (64, 63, '共享栏目', '', 'category/index', '', 'app-module-64', 0, 'fa fa-reorder', 0);
INSERT INTO `dr_admin_menu` VALUES (65, 62, '内容审核', '', '', '', 'content-verify', 0, 'fa fa-edit', 0);
INSERT INTO `dr_admin_menu` VALUES (66, 63, '文章管理', '', 'news/home/index', '', 'module-news', 0, 'fa fa-sticky-note', -1);
INSERT INTO `dr_admin_menu` VALUES (67, 65, '文章审核', '', 'news/verify/index', '', 'verify-module-news', 0, 'fa fa-sticky-note', -1);

-- ----------------------------
-- Table structure for dr_admin_min_menu
-- ----------------------------
DROP TABLE IF EXISTS `dr_admin_min_menu`;
CREATE TABLE `dr_admin_min_menu`  (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `pid` smallint(5) UNSIGNED NOT NULL COMMENT '上级菜单id',
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '菜单语言名称',
  `site` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '站点归属',
  `uri` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT 'uri字符串',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '外链地址',
  `mark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '菜单标识',
  `hidden` tinyint(1) UNSIGNED NULL DEFAULT NULL COMMENT '是否隐藏',
  `icon` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '图标标示',
  `displayorder` int(5) NULL DEFAULT NULL COMMENT '排序值',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `list`(`pid` ASC) USING BTREE,
  INDEX `displayorder`(`displayorder` ASC) USING BTREE,
  INDEX `mark`(`mark` ASC) USING BTREE,
  INDEX `hidden`(`hidden` ASC) USING BTREE,
  INDEX `uri`(`uri` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 17 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '后台简化菜单表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_admin_min_menu
-- ----------------------------
INSERT INTO `dr_admin_min_menu` VALUES (1, 0, '我的面板', '', '', '', 'home', 0, 'fa fa-home', 0);
INSERT INTO `dr_admin_min_menu` VALUES (2, 1, '后台首页', '', 'home/main', '', '1-0', 0, 'fa fa-home', 0);
INSERT INTO `dr_admin_min_menu` VALUES (3, 1, '资料修改', '', 'api/my', '', '1-1', 0, 'fa fa-user', 0);
INSERT INTO `dr_admin_min_menu` VALUES (4, 0, '应用插件', '', '', '', 'app-plugin', 0, 'fa fa-puzzle-piece', 0);
INSERT INTO `dr_admin_min_menu` VALUES (5, 4, '联动菜单', '', 'linkage/index', '', '4-0', 0, 'fa fa-columns', 0);
INSERT INTO `dr_admin_min_menu` VALUES (6, 4, '附件管理', '', 'attachments/index', '', '4-1', 0, 'fa fa-folder', 0);
INSERT INTO `dr_admin_min_menu` VALUES (7, 1, '网站设置', '', 'module/site_param/index', '', '', 0, 'fa fa-cog', 0);
INSERT INTO `dr_admin_min_menu` VALUES (8, 1, '图片设置', '', 'module/site_image/index', '', '', 0, 'fa fa-photo', 0);
INSERT INTO `dr_admin_min_menu` VALUES (9, 0, 'SEO设置', '', '', '', 'config-seo', 0, 'fa fa-internet-explorer', 0);
INSERT INTO `dr_admin_min_menu` VALUES (10, 9, '站点SEO', '', 'module/seo_site/index', '', '', 0, 'fa fa-cog', 0);
INSERT INTO `dr_admin_min_menu` VALUES (11, 9, '模块SEO', '', 'module/seo_module/index', '', '', 0, 'fa fa-gears', 0);
INSERT INTO `dr_admin_min_menu` VALUES (12, 9, '栏目SEO', '', 'module/seo_category/index', '', '', 0, 'fa fa-reorder', 0);
INSERT INTO `dr_admin_min_menu` VALUES (13, 9, 'URL规则', '', 'module/urlrule/index', '', '', 0, 'fa fa-link', 0);
INSERT INTO `dr_admin_min_menu` VALUES (14, 0, '内容管理', '', '', '', 'content-module', 0, 'fa fa-th-large', 0);
INSERT INTO `dr_admin_min_menu` VALUES (15, 14, '共享栏目', '', 'category/index', '', '', 0, 'fa fa-reorder', 0);
INSERT INTO `dr_admin_min_menu` VALUES (16, 14, '文章管理', '', 'news/home/index', '', 'module-news', 0, 'fa fa-sticky-note', -1);

-- ----------------------------
-- Table structure for dr_admin_notice
-- ----------------------------
DROP TABLE IF EXISTS `dr_admin_notice`;
CREATE TABLE `dr_admin_notice`  (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'id',
  `site` int(5) NOT NULL COMMENT '站点id',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '提醒类型：系统、内容、会员、应用',
  `msg` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '提醒内容说明',
  `uri` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '对应的URI',
  `to_rid` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '指定角色组',
  `to_uid` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '指定管理员',
  `status` tinyint(1) NOT NULL COMMENT '未处理0，1已查看，2处理中，3处理完成',
  `uid` int(10) NOT NULL COMMENT '申请人',
  `username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '申请人',
  `op_uid` int(10) NOT NULL COMMENT '处理人',
  `op_username` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '处理人',
  `updatetime` int(10) NOT NULL COMMENT '处理时间',
  `inputtime` int(10) NOT NULL COMMENT '提醒时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uri`(`uri` ASC) USING BTREE,
  INDEX `site`(`site` ASC) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE,
  INDEX `uid`(`uid` ASC) USING BTREE,
  INDEX `op_uid`(`op_uid` ASC) USING BTREE,
  INDEX `to_uid`(`to_uid` ASC) USING BTREE,
  INDEX `to_rid`(`to_rid` ASC) USING BTREE,
  INDEX `updatetime`(`updatetime` ASC) USING BTREE,
  INDEX `inputtime`(`inputtime` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '后台提醒表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_admin_notice
-- ----------------------------

-- ----------------------------
-- Table structure for dr_admin_role
-- ----------------------------
DROP TABLE IF EXISTS `dr_admin_role`;
CREATE TABLE `dr_admin_role`  (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `site` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '允许管理的站点，序列化数组格式',
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '角色组语言名称',
  `system` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '系统权限',
  `module` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '模块权限',
  `application` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '应用权限',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '后台角色权限表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_admin_role
-- ----------------------------
INSERT INTO `dr_admin_role` VALUES (1, '', '超级管理员', '', '', '');
INSERT INTO `dr_admin_role` VALUES (2, '', '编辑员', '', '', '');

-- ----------------------------
-- Table structure for dr_admin_role_index
-- ----------------------------
DROP TABLE IF EXISTS `dr_admin_role_index`;
CREATE TABLE `dr_admin_role_index`  (
  `id` smallint(5) NOT NULL AUTO_INCREMENT,
  `uid` mediumint(8) UNSIGNED NULL DEFAULT NULL COMMENT '会员uid',
  `roleid` mediumint(8) UNSIGNED NULL DEFAULT NULL COMMENT '角色组id',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`uid` ASC) USING BTREE,
  INDEX `roleid`(`roleid` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '后台角色组分配表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_admin_role_index
-- ----------------------------
INSERT INTO `dr_admin_role_index` VALUES (1, 1, 1);

-- ----------------------------
-- Table structure for dr_admin_setting
-- ----------------------------
DROP TABLE IF EXISTS `dr_admin_setting`;
CREATE TABLE `dr_admin_setting`  (
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`name`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '系统属性参数表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_admin_setting
-- ----------------------------

-- ----------------------------
-- Table structure for dr_attachment
-- ----------------------------
DROP TABLE IF EXISTS `dr_attachment`;
CREATE TABLE `dr_attachment`  (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uid` mediumint(8) UNSIGNED NOT NULL COMMENT '会员id',
  `author` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '会员',
  `siteid` mediumint(5) UNSIGNED NOT NULL COMMENT '站点id',
  `related` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '相关表标识',
  `tableid` tinyint(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '附件副表id',
  `download` mediumint(8) NOT NULL DEFAULT 0 COMMENT '无用保留',
  `filesize` int(10) UNSIGNED NOT NULL COMMENT '文件大小',
  `fileext` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件扩展名',
  `filemd5` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件md5值',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`uid` ASC) USING BTREE,
  INDEX `author`(`author` ASC) USING BTREE,
  INDEX `relatedtid`(`related` ASC) USING BTREE,
  INDEX `fileext`(`fileext` ASC) USING BTREE,
  INDEX `filemd5`(`filemd5` ASC) USING BTREE,
  INDEX `siteid`(`siteid` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '附件表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_attachment
-- ----------------------------
INSERT INTO `dr_attachment` VALUES (1, 1, '', 1, 'rand', 1, 0, 9831, 'png', '9e261e72adfc9c583bb6ca72843e5d55');
INSERT INTO `dr_attachment` VALUES (2, 1, '', 1, 'rand', 1, 0, 2425547, 'png', 'f39bd7e1e7440ac08322fad2c8ae2142');

-- ----------------------------
-- Table structure for dr_attachment_data
-- ----------------------------
DROP TABLE IF EXISTS `dr_attachment_data`;
CREATE TABLE `dr_attachment_data`  (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '附件id',
  `uid` mediumint(8) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员id',
  `author` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '会员',
  `related` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '相关表标识',
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '原文件名',
  `fileext` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件扩展名',
  `filesize` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文件大小',
  `attachment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '服务器路径',
  `remote` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '远程附件id',
  `attachinfo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '附件信息',
  `inputtime` int(10) UNSIGNED NOT NULL COMMENT '入库时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `inputtime`(`inputtime` ASC) USING BTREE,
  INDEX `fileext`(`fileext` ASC) USING BTREE,
  INDEX `remote`(`remote` ASC) USING BTREE,
  INDEX `author`(`author` ASC) USING BTREE,
  INDEX `uid`(`uid` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '附件已归档表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_attachment_data
-- ----------------------------
INSERT INTO `dr_attachment_data` VALUES (1, 1, '', 'rand', 'icon', 'png', 9831, '202402/f21f01b7eafac9d.png', 0, '{\"width\":512,\"height\":512}', 1707476898);
INSERT INTO `dr_attachment_data` VALUES (2, 1, '', 'rand', '微信图片_20240207123808', 'png', 2425547, '202402/22fa1e97162946d.png', 0, '{\"width\":1722,\"height\":892}', 1707476934);

-- ----------------------------
-- Table structure for dr_attachment_remote
-- ----------------------------
DROP TABLE IF EXISTS `dr_attachment_remote`;
CREATE TABLE `dr_attachment_remote`  (
  `id` tinyint(2) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` tinyint(2) NOT NULL COMMENT '类型',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '名称',
  `url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '访问地址',
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '参数值',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '远程附件表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_attachment_remote
-- ----------------------------

-- ----------------------------
-- Table structure for dr_attachment_unused
-- ----------------------------
DROP TABLE IF EXISTS `dr_attachment_unused`;
CREATE TABLE `dr_attachment_unused`  (
  `id` mediumint(8) UNSIGNED NOT NULL COMMENT '附件id',
  `uid` mediumint(8) UNSIGNED NOT NULL DEFAULT 0 COMMENT '会员id',
  `author` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '会员',
  `siteid` mediumint(5) UNSIGNED NOT NULL COMMENT '站点id',
  `filename` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '原文件名',
  `fileext` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '文件扩展名',
  `filesize` int(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '文件大小',
  `attachment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '服务器路径',
  `remote` tinyint(2) UNSIGNED NOT NULL DEFAULT 0 COMMENT '远程附件id',
  `attachinfo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '附件信息',
  `inputtime` int(10) UNSIGNED NOT NULL COMMENT '入库时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `uid`(`uid` ASC) USING BTREE,
  INDEX `inputtime`(`inputtime` ASC) USING BTREE,
  INDEX `fileext`(`fileext` ASC) USING BTREE,
  INDEX `remote`(`remote` ASC) USING BTREE,
  INDEX `author`(`author` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '未使用的附件表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_attachment_unused
-- ----------------------------

-- ----------------------------
-- Table structure for dr_cron
-- ----------------------------
DROP TABLE IF EXISTS `dr_cron`;
CREATE TABLE `dr_cron`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `site` int(10) NOT NULL COMMENT '站点',
  `type` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '类型',
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '参数值',
  `status` tinyint(1) UNSIGNED NOT NULL COMMENT '状态',
  `error` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '错误信息',
  `updatetime` int(10) UNSIGNED NOT NULL COMMENT '执行时间',
  `inputtime` int(10) UNSIGNED NOT NULL COMMENT '写入时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `site`(`site` ASC) USING BTREE,
  INDEX `type`(`type` ASC) USING BTREE,
  INDEX `status`(`status` ASC) USING BTREE,
  INDEX `updatetime`(`updatetime` ASC) USING BTREE,
  INDEX `inputtime`(`inputtime` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '任务管理' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_cron
-- ----------------------------

-- ----------------------------
-- Table structure for dr_field
-- ----------------------------
DROP TABLE IF EXISTS `dr_field`;
CREATE TABLE `dr_field`  (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '字段别名语言',
  `fieldname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '字段名称',
  `fieldtype` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '字段类型',
  `relatedid` smallint(5) UNSIGNED NOT NULL COMMENT '相关id',
  `relatedname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '相关表',
  `isedit` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否可修改',
  `ismain` tinyint(1) UNSIGNED NOT NULL COMMENT '是否主表',
  `issystem` tinyint(1) UNSIGNED NOT NULL COMMENT '是否系统表',
  `ismember` tinyint(1) UNSIGNED NOT NULL COMMENT '是否会员可见',
  `issearch` tinyint(1) UNSIGNED NOT NULL DEFAULT 1 COMMENT '是否可搜索',
  `disabled` tinyint(1) UNSIGNED NOT NULL COMMENT '禁用？',
  `setting` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '配置信息',
  `displayorder` int(5) NOT NULL COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `list`(`relatedid` ASC, `disabled` ASC, `issystem` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '字段表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_field
-- ----------------------------
INSERT INTO `dr_field` VALUES (1, '友情链接', 'yqlj', 'Ftable', 1, 'site', 1, 1, 0, 1, 0, 0, '{\"option\":{\"is_add\":\"1\",\"is_first_hang\":\"0\",\"count\":\"\",\"first_cname\":\"\",\"hang\":{\"1\":{\"name\":\"\"},\"2\":{\"name\":\"\"},\"3\":{\"name\":\"\"},\"4\":{\"name\":\"\"},\"5\":{\"name\":\"\"}},\"field\":{\"1\":{\"type\":\"1\",\"name\":\"网站名称\",\"width\":\"200\",\"option\":\"\"},\"2\":{\"type\":\"1\",\"name\":\"网站地址\",\"width\":\"\",\"option\":\"\"},\"3\":{\"type\":\"0\",\"name\":\"\",\"width\":\"\",\"option\":\"\"},\"4\":{\"type\":\"0\",\"name\":\"\",\"width\":\"\",\"option\":\"\"},\"5\":{\"type\":\"0\",\"name\":\"\",\"width\":\"\",\"option\":\"\"},\"6\":{\"type\":\"0\",\"name\":\"\",\"width\":\"\",\"option\":\"\"},\"7\":{\"type\":\"0\",\"name\":\"\",\"width\":\"\",\"option\":\"\"},\"8\":{\"type\":\"0\",\"name\":\"\",\"width\":\"\",\"option\":\"\"},\"9\":{\"type\":\"0\",\"name\":\"\",\"width\":\"\",\"option\":\"\"},\"10\":{\"type\":\"0\",\"name\":\"\",\"width\":\"\",\"option\":\"\"}},\"width\":\"\",\"height\":\"\",\"css\":\"\"},\"validate\":{\"required\":\"0\",\"pattern\":\"\",\"errortips\":\"\",\"xss\":\"1\",\"check\":\"\",\"filter\":\"\",\"tips\":\"\",\"formattr\":\"\"},\"is_right\":\"0\"}', 0);
INSERT INTO `dr_field` VALUES (2, '幻灯图片', 'hdtp', 'Ftable', 1, 'site', 1, 1, 0, 1, 0, 0, '{\"option\":{\"is_add\":\"1\",\"is_first_hang\":\"0\",\"count\":\"\",\"first_cname\":\"\",\"hang\":{\"1\":{\"name\":\"\"},\"2\":{\"name\":\"\"},\"3\":{\"name\":\"\"},\"4\":{\"name\":\"\"},\"5\":{\"name\":\"\"}},\"field\":{\"1\":{\"type\":\"3\",\"name\":\"图片\",\"width\":\"200\",\"option\":\"\"},\"2\":{\"type\":\"1\",\"name\":\"名称\",\"width\":\"200\",\"option\":\"\"},\"3\":{\"type\":\"1\",\"name\":\"跳转地址\",\"width\":\"\",\"option\":\"\"},\"4\":{\"type\":\"0\",\"name\":\"\",\"width\":\"\",\"option\":\"\"},\"5\":{\"type\":\"0\",\"name\":\"\",\"width\":\"\",\"option\":\"\"},\"6\":{\"type\":\"0\",\"name\":\"\",\"width\":\"\",\"option\":\"\"},\"7\":{\"type\":\"0\",\"name\":\"\",\"width\":\"\",\"option\":\"\"},\"8\":{\"type\":\"0\",\"name\":\"\",\"width\":\"\",\"option\":\"\"},\"9\":{\"type\":\"0\",\"name\":\"\",\"width\":\"\",\"option\":\"\"},\"10\":{\"type\":\"0\",\"name\":\"\",\"width\":\"\",\"option\":\"\"}},\"width\":\"\",\"height\":\"\",\"css\":\"\"},\"validate\":{\"required\":\"0\",\"pattern\":\"\",\"errortips\":\"\",\"xss\":\"1\",\"check\":\"\",\"filter\":\"\",\"tips\":\"\",\"formattr\":\"\"},\"is_right\":\"0\"}', 0);
INSERT INTO `dr_field` VALUES (3, '主题', 'title', 'Text', 1, 'module', 1, 1, 1, 1, 1, 0, '{\"option\":{\"width\":400,\"fieldtype\":\"VARCHAR\",\"fieldlength\":\"255\"},\"validate\":{\"xss\":1,\"required\":1,\"formattr\":\"onblur=\\\"check_title();get_keywords(\'keywords\');\\\"\"}}', 0);
INSERT INTO `dr_field` VALUES (4, '缩略图', 'thumb', 'File', 1, 'module', 1, 1, 1, 1, 1, 0, '{\"option\":{\"ext\":\"jpg,gif,png\",\"size\":10,\"width\":400,\"fieldtype\":\"VARCHAR\",\"fieldlength\":\"255\"}}', 0);
INSERT INTO `dr_field` VALUES (5, '关键字', 'keywords', 'Text', 1, 'module', 1, 1, 1, 1, 1, 0, '{\"option\":{\"width\":400,\"fieldtype\":\"VARCHAR\",\"fieldlength\":\"255\"},\"validate\":{\"xss\":1,\"formattr\":\" data-role=\\\"tagsinput\\\"\"}}', 0);
INSERT INTO `dr_field` VALUES (6, '描述', 'description', 'Textarea', 1, 'module', 1, 1, 1, 1, 1, 0, '{\"option\":{\"width\":500,\"height\":60,\"fieldtype\":\"VARCHAR\",\"fieldlength\":\"255\"},\"validate\":{\"xss\":1,\"filter\":\"dr_filter_description\"}}', 0);
INSERT INTO `dr_field` VALUES (7, '笔名', 'author', 'Text', 1, 'module', 1, 1, 1, 1, 1, 0, '{\"is_right\":1,\"option\":{\"width\":200,\"fieldtype\":\"VARCHAR\",\"fieldlength\":\"255\",\"value\":\"{name}\"},\"validate\":{\"xss\":1}}', 0);
INSERT INTO `dr_field` VALUES (8, '内容', 'content', 'Editor', 1, 'module', 1, 0, 1, 1, 1, 0, '{\"option\":{\"mode\":1,\"show_bottom_boot\":1,\"div2p\":1,\"width\":\"100%\",\"height\":400},\"validate\":{\"xss\":1,\"required\":1}}', 0);
INSERT INTO `dr_field` VALUES (9, '缩略图', 'thumb', 'File', 0, 'category-share', 1, 1, 1, 1, 1, 0, '{\"option\":{\"ext\":\"jpg,gif,png,jpeg\",\"size\":10,\"input\":1,\"attachment\":0}}', 0);
INSERT INTO `dr_field` VALUES (10, '栏目内容', 'content', 'Ueditor', 0, 'category-share', 1, 1, 1, 1, 1, 0, '{\"option\":{\"mode\":1,\"show_bottom_boot\":1,\"div2p\":1,\"width\":\"100%\",\"height\":400},\"validate\":{\"xss\":1,\"required\":1}}', 0);

-- ----------------------------
-- Table structure for dr_linkage
-- ----------------------------
DROP TABLE IF EXISTS `dr_linkage`;
CREATE TABLE `dr_linkage`  (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '菜单名称',
  `type` tinyint(1) UNSIGNED NOT NULL,
  `code` char(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `code`(`code` ASC) USING BTREE,
  INDEX `module`(`id` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '联动菜单表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_linkage
-- ----------------------------
INSERT INTO `dr_linkage` VALUES (1, '中国地区', 0, 'address');

-- ----------------------------
-- Table structure for dr_linkage_data_1
-- ----------------------------
DROP TABLE IF EXISTS `dr_linkage_data_1`;
CREATE TABLE `dr_linkage_data_1`  (
  `id` mediumint(8) UNSIGNED NOT NULL AUTO_INCREMENT,
  `site` mediumint(5) UNSIGNED NOT NULL COMMENT '站点id',
  `pid` mediumint(8) UNSIGNED NOT NULL DEFAULT 0 COMMENT '上级id',
  `pids` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL COMMENT '所有上级id',
  `name` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '栏目名称',
  `cname` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '别名',
  `child` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否有下级',
  `hidden` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '前端隐藏',
  `childids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '下级所有id',
  `displayorder` mediumint(8) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `cname`(`cname` ASC) USING BTREE,
  INDEX `hidden`(`hidden` ASC) USING BTREE,
  INDEX `list`(`site` ASC, `displayorder` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '联动菜单数据表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_linkage_data_1
-- ----------------------------
INSERT INTO `dr_linkage_data_1` VALUES (1, 1, 0, '0', '北京', 'bj', 0, 0, '1', 0);
INSERT INTO `dr_linkage_data_1` VALUES (2, 1, 0, '0', '成都', 'cd', 0, 0, '2', 0);

-- ----------------------------
-- Table structure for dr_mail_smtp
-- ----------------------------
DROP TABLE IF EXISTS `dr_mail_smtp`;
CREATE TABLE `dr_mail_smtp`  (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `host` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pass` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` mediumint(8) UNSIGNED NOT NULL,
  `displayorder` smallint(5) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `displayorder`(`displayorder` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '邮件账户表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_mail_smtp
-- ----------------------------

-- ----------------------------
-- Table structure for dr_member
-- ----------------------------
DROP TABLE IF EXISTS `dr_member`;
CREATE TABLE `dr_member`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '邮箱地址',
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '手机号码',
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '用户名',
  `password` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '加密密码',
  `login_attr` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '登录附加验证字符',
  `salt` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '随机加密码',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '姓名',
  `money` decimal(10, 2) UNSIGNED NOT NULL COMMENT 'RMB',
  `freeze` decimal(10, 2) UNSIGNED NOT NULL COMMENT '冻结RMB',
  `spend` decimal(10, 2) UNSIGNED NOT NULL COMMENT '消费RMB总额',
  `score` int(10) UNSIGNED NOT NULL COMMENT '积分',
  `experience` int(10) UNSIGNED NOT NULL COMMENT '经验值',
  `regip` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '注册ip',
  `regtime` int(10) UNSIGNED NOT NULL COMMENT '注册时间',
  `randcode` mediumint(6) UNSIGNED NOT NULL COMMENT '随机验证码',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `username`(`username` ASC) USING BTREE,
  INDEX `email`(`email` ASC) USING BTREE,
  INDEX `phone`(`phone` ASC) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会员表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_member
-- ----------------------------
INSERT INTO `dr_member` VALUES (1, 'admin@admin.com', '', 'admin', '5263c78abcb04db3915d7e78afc8f3c5', '', 'eddea82ad2', '创始人', 1000000.00, 0.00, 0.00, 1000000, 1000000, '', 1707476708, 0);

-- ----------------------------
-- Table structure for dr_member_data
-- ----------------------------
DROP TABLE IF EXISTS `dr_member_data`;
CREATE TABLE `dr_member_data`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `is_admin` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '是否是管理员',
  `is_lock` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '账号锁定标识',
  `is_verify` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '审核标识',
  `is_mobile` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '手机认证标识',
  `is_email` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '邮箱认证标识',
  `is_avatar` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '头像上传标识',
  `is_complete` tinyint(1) UNSIGNED NULL DEFAULT 0 COMMENT '完善资料标识',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '会员表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_member_data
-- ----------------------------
INSERT INTO `dr_member_data` VALUES (1, 1, 0, 1, 1, 0, 0, 1);

-- ----------------------------
-- Table structure for dr_module
-- ----------------------------
DROP TABLE IF EXISTS `dr_module`;
CREATE TABLE `dr_module`  (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `site` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '站点划分',
  `dirname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '目录名称',
  `share` tinyint(1) UNSIGNED NULL DEFAULT NULL COMMENT '是否共享模块',
  `setting` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '配置信息',
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL COMMENT '评论信息',
  `disabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '禁用？',
  `displayorder` smallint(5) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `dirname`(`dirname` ASC) USING BTREE,
  INDEX `disabled`(`disabled` ASC) USING BTREE,
  INDEX `displayorder`(`displayorder` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '模块表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_module
-- ----------------------------
INSERT INTO `dr_module` VALUES (1, '{\"1\":{\"html\":0,\"theme\":\"default\",\"domain\":\"\",\"template\":\"default\"}}', 'news', 1, '{\"order\":\"updatetime DESC\",\"verify_msg\":\"\",\"delete_msg\":\"\",\"list_field\":{\"title\":{\"use\":\"1\",\"order\":\"1\",\"name\":\"主题\",\"width\":\"\",\"func\":\"title\"},\"catid\":{\"use\":\"1\",\"order\":\"2\",\"name\":\"栏目\",\"width\":\"130\",\"func\":\"catid\"},\"author\":{\"use\":\"1\",\"order\":\"3\",\"name\":\"笔名\",\"width\":\"120\",\"func\":\"author\"},\"updatetime\":{\"use\":\"1\",\"order\":\"4\",\"name\":\"更新时间\",\"width\":\"160\",\"func\":\"datetime\"}},\"comment_list_field\":{\"content\":{\"use\":\"1\",\"order\":\"1\",\"name\":\"评论\",\"width\":\"\",\"func\":\"comment\"},\"author\":{\"use\":\"1\",\"order\":\"3\",\"name\":\"作者\",\"width\":\"100\",\"func\":\"author\"},\"inputtime\":{\"use\":\"1\",\"order\":\"4\",\"name\":\"评论时间\",\"width\":\"160\",\"func\":\"datetime\"}},\"flag\":null,\"param\":null,\"search\":{\"use\":\"1\",\"field\":\"title,keywords\",\"total\":\"500\",\"length\":\"4\",\"param_join\":\"-\",\"param_rule\":\"0\",\"param_field\":\"\",\"param_join_field\":[\"\",\"\",\"\",\"\",\"\",\"\",\"\"],\"param_join_default_value\":\"0\"}}', '', 0, 0);

-- ----------------------------
-- Table structure for dr_site
-- ----------------------------
DROP TABLE IF EXISTS `dr_site`;
CREATE TABLE `dr_site`  (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '站点名称',
  `domain` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '站点域名',
  `setting` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '站点配置',
  `disabled` tinyint(1) NOT NULL DEFAULT 0 COMMENT '禁用？',
  `displayorder` smallint(5) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `disabled`(`disabled` ASC) USING BTREE,
  INDEX `displayorder`(`displayorder` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = '站点表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_site
-- ----------------------------
INSERT INTO `dr_site` VALUES (1, 'swcms', 'dev.sw586.com', '{\"config\":{\"SITE_NAME\":\"swcms\",\"SITE_DOMAIN\":\"dev.sw586.com\",\"SITE_CLOSE\":\"0\",\"SITE_INDEX_HTML\":\"0\",\"SITE_CLOSE_MSG\":\"网站升级中....\",\"SITE_LANGUAGE\":\"zh-cn\",\"SITE_TEMPLATE\":\"default\",\"SITE_TIMEZONE\":\"8\",\"SITE_TIME_FORMAT\":\"\",\"SITE_THEME\":\"default\"}}', 0, 0);

-- ----------------------------
-- Table structure for dr_urlrule
-- ----------------------------
DROP TABLE IF EXISTS `dr_urlrule`;
CREATE TABLE `dr_urlrule`  (
  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` tinyint(1) UNSIGNED NOT NULL COMMENT '规则类型',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '规则名称',
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '详细规则',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `type`(`type` ASC) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci COMMENT = 'URL规则表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dr_urlrule
-- ----------------------------

SET FOREIGN_KEY_CHECKS = 1;
