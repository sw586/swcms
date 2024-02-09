<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * 系统配置文件
 */

return [

	'SYS_DEBUG'                     => 1, //调试器开关
	'SYS_ADMIN_CODE'                => 0, //后台登录验证码开关
	'SYS_ADMIN_LOG'                 => 0, //后台操作日志开关
	'SYS_AUTO_FORM'                 => 0, //自动存储表单数据
	'SYS_ADMIN_PAGESIZE'            => 10, //后台数据分页显示数量
	'SYS_TABLE_ISFOOTER'            => '', //批量操作按钮位置设定
	'SYS_CRON_AUTH'                 => 0, //自动任务权限IP地址
	'SYS_SMS_IMG_CODE'              => 0, //发送短信验证码双重图形验证开关
	'SYS_GO_404'                    => '', //404页面跳转开关
	'SYS_301'                       => 1, //内容地址唯一模式
	'SYS_FONT_SIZE'                 => '', //字号设置
	'SYS_URL_ONLY'                  => 0, //地址匹配规则
	'SYS_URL_REL'                   => 1, //地址相对模式
	'SYS_KEY'                       => 'PHPCMF466db745eb886bca3b7e2195a4776f8d', //安全密匙
	'SYS_CSRF'                      => 0, //开启跨站验证
	'SYS_CSRF_TIME'                 => 0, //跨站验证有效期
	'SYS_HTTPS'                     => 0, //https模式
	'SYS_NOT_ADMIN_CACHE'           => 0, //禁用后台tab切换效果
	'SYS_ADMIN_MODE'                => '', //禁用后台登录进行模式选择
	'SYS_ADMIN_LOGINS'              => '', //登录失败N次后，系统将锁定登录
	'SYS_ADMIN_LOGIN_TIME'          => '', //登录失败锁定后在x分钟内禁止登录
	'SYS_ADMIN_OAUTH'               => '', //后台启用快捷登录
	'SYS_ATTACHMENT_DB'             => '', //附件归属开启模式
	'SYS_ATTACHMENT_GUEST'          => '', //游客是否附件上传
	'SYS_ATTACHMENT_PAGESIZE'       => '', //浏览附件分页数
	'SYS_ATTACHMENT_CF'             => '', //重复上传控制
	'SYS_ATTACHMENT_REL'            => '', //相对于当前站点的域名
	'SYS_ATTACHMENT_SAFE'           => '', //附件上传安全模式
	'SYS_ATTACHMENT_PATH'           => '', //附件上传路径
	'SYS_ATTACHMENT_SAVE_TYPE'      => '', //附件存储方式
	'SYS_ATTACHMENT_SAVE_DIR'       => '', //附件存储目录
	'SYS_ATTACHMENT_SAVE_ID'        => '', //附件存储全局策略
	'SYS_ATTACHMENT_URL'            => '', //附件访问地址
	'SYS_AVATAR_PATH'               => '', //头像上传路径
	'SYS_AVATAR_URL'                => '', //头像访问地址
	'SYS_API_TOKEN'                 => '', //API请求密钥
	'SYS_API_CODE'                  => '', //API请求时验证码开关
	'SYS_API_REL'                   => '', //API请求时的URL方式
	'SYS_THEME_ROOT_PATH'           => 1, //资源路径引用方式
	'SYS_NOT_UPDATE'                => 1, //禁止自动检测版本

];