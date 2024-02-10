<?php if ($fn_include = $this->_include("header.html")) include($fn_include); ?>
<div class="note note-danger">
    <p><?php echo dr_lang('./cache/目录一定要有可写权限'); ?></p>
</div>
<form action="" class="form-horizontal" method="post" name="myform" id="myform">
<?php echo $form; ?>
    <div class="myfbody">
    <div class="portlet bordered light">
        <div class="portlet-title tabbable-line">
            <ul class="nav nav-tabs" style="float:left;">
                <li class="<?php if ($page==0) { ?>active<?php } ?>">
                    <a href="#tab_0" data-toggle="tab" onclick="$('#dr_page').val('0')"> <i class="fa fa-cog"></i> <?php echo dr_lang('系统参数'); ?> </a>
                </li>
                <?php if (IS_USE_MODULE) { ?>
                <li class="<?php if ($page==3) { ?>active<?php } ?>">
                    <a href="#tab_3" data-toggle="tab" onclick="$('#dr_page').val('3')"> <i class="fa fa-link"></i> <?php echo dr_lang('地址设置'); ?> </a>
                </li>
                <?php } ?>
                <li class="<?php if ($page==1) { ?>active<?php } ?>">
                    <a href="#tab_1" data-toggle="tab" onclick="$('#dr_page').val('1')"> <i class="fa fa-expeditedssl"></i> <?php echo dr_lang('安全设置'); ?> </a>
                </li>
                <li class="<?php if ($page==4) { ?>active<?php } ?>">
                    <a href="#tab_4" data-toggle="tab" onclick="$('#dr_page').val('4')"> <i class="fa fa-user"></i> <?php echo dr_lang('后台登录'); ?> </a>
                </li>
                <li class="<?php if ($page==2) { ?>active<?php } ?>">
                    <a href="#tab_2" data-toggle="tab" onclick="$('#dr_page').val('2')"> <i class="fa fa-code"></i> <?php echo dr_lang('API设置'); ?> </a>
                </li>
            </ul>
        </div>
        <div class="portlet-body">
            <div class="tab-content">

                <div class="tab-pane <?php if ($page==0) { ?>active<?php } ?>" id="tab_0">
                    <div class="form-body">

                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('调试器'); ?></label>
                            <div class="col-md-9">
                                <?php if (IS_DEV) { ?>
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_DEBUG]" value="1" checked disabled} /> <?php echo dr_lang('开启'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_DEBUG]" value="0"  disabled /> <?php echo dr_lang('关闭'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('你已经在index.php中开启了开发者模式，调试器已被强制开启'); ?></span>
                                <?php } else { ?>
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_DEBUG]" value="1" <?php if ($data['SYS_DEBUG']) { ?>checked<?php } ?> /> <?php echo dr_lang('开启'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_DEBUG]" value="0" <?php if (empty($data['SYS_DEBUG'])) { ?>checked<?php } ?> /> <?php echo dr_lang('关闭'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('用于后台启用Debug工具查看程序和SQL执行详情'); ?></span>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('操作日志'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_ADMIN_LOG]" value="1" <?php if ($data['SYS_ADMIN_LOG']) { ?>checked<?php } ?> /> <?php echo dr_lang('开启'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_ADMIN_LOG]" value="0" <?php if (empty($data['SYS_ADMIN_LOG'])) { ?>checked<?php } ?> /> <?php echo dr_lang('关闭'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('记录后台新增、修改、删除数据的操作日志'); ?></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('内容临时存储'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_AUTO_FORM]" value="1" <?php if ($data['SYS_AUTO_FORM']) { ?>checked<?php } ?> /> <?php echo dr_lang('开启'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_AUTO_FORM]" value="0" <?php if (empty($data['SYS_AUTO_FORM'])) { ?>checked<?php } ?> /> <?php echo dr_lang('关闭'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('发布内容临时存储，再次发布时自动填充'); ?></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('后台列表操作按钮'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_TABLE_ISFOOTER]" value="0" <?php if (empty($data['SYS_TABLE_ISFOOTER'])) { ?>checked<?php } ?> /> <?php echo dr_lang('顶部'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_TABLE_ISFOOTER]" value="1" <?php if ($data['SYS_TABLE_ISFOOTER']) { ?>checked<?php } ?> /> <?php echo dr_lang('底部'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('列表批量操作按钮的位置设定，仅限于Table布局的表格列表'); ?></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('后台数据分页条数'); ?></label>
                            <div class="col-md-9">
                                <label><input class="form-control" type="text" name="data[SYS_ADMIN_PAGESIZE]" value="<?php echo intval($data['SYS_ADMIN_PAGESIZE']); ?>" ></label>
                                <span class="help-block"><?php echo dr_lang('针对后台列表的数量分页控制，例如文章每页显示的数量控制'); ?></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('自动检测程序版本'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_NOT_UPDATE]" value="0" <?php if (empty($data['SYS_NOT_UPDATE'])) { ?>checked<?php } ?> /> <?php echo dr_lang('允许检测'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_NOT_UPDATE]" value="1" <?php if ($data['SYS_NOT_UPDATE']) { ?>checked<?php } ?> /> <?php echo dr_lang('禁止检测'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('不主动向官网服务器检测系统框架的版本信息'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane <?php if ($page==3) { ?>active<?php } ?>" id="tab_3">
                    <div class="form-body">
                        <?php if (dr_is_root_path()) { ?>
                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('资源路径'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_THEME_ROOT_PATH]" value="0" <?php if (empty($data['SYS_THEME_ROOT_PATH'])) { ?>checked<?php } ?> /> <?php echo dr_lang('绝对路径'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_THEME_ROOT_PATH]" value="1" <?php if ($data['SYS_THEME_ROOT_PATH']) { ?>checked<?php } ?> /> <?php echo dr_lang('相对路径'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('前端界面中的static资源目录的引用方式'); ?></span>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('URL路径'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_URL_REL]" value="0" <?php if (empty($data['SYS_URL_REL'])) { ?>checked<?php } ?> /> <?php echo dr_lang('绝对路径'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_URL_REL]" value="1" <?php if ($data['SYS_URL_REL']) { ?>checked<?php } ?> /> <?php echo dr_lang('相对路径'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('前端a标签中的url包含的路径格式，相对路径则不带域名'); ?></span>
                                <span class="help-block"><?php echo dr_lang('相对路径的URL调用函数是：dr_url_rel(地址)'); ?></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('跳转404页面'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_GO_404]" value="1" <?php if ($data['SYS_GO_404']) { ?>checked<?php } ?> /> <?php echo dr_lang('开启'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_GO_404]" value="0" <?php if (empty($data['SYS_GO_404'])) { ?>checked<?php } ?> /> <?php echo dr_lang('关闭'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('开启后，404页面时直接跳转到404.html，不在当前url中显示404页面'); ?></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('地址匹配模式'); ?></label>
                            <div class="col-md-9">
                                <?php if (defined('IS_NOT_301')) { ?>
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input type="radio" disabled name="data[SYS_301]" value="0" /> <?php echo dr_lang('唯一地址'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" disabled name="data[SYS_301]" value="1" checked /> <?php echo dr_lang('自由参数'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('你已经在index.php中开启了禁止301跳转'); ?></span>
                                <?php } else { ?>
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input onclick="$('.dr_url_only').show()" type="radio" name="data[SYS_301]" value="0" <?php if (empty($data['SYS_301'])) { ?>checked<?php } ?> /> <?php echo dr_lang('唯一地址'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input onclick="$('.dr_url_only').hide()" type="radio" name="data[SYS_301]" value="1" <?php if ($data['SYS_301']) { ?>checked<?php } ?> /> <?php echo dr_lang('自由参数'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('唯一模式针对首页、栏目页、内容页等自己加入参数时将301跳转到本身的地址上'); ?></span>
                                <?php } ?>
                            </div>
                        </div>
                        <div class="form-group dr_url_only" <?php if (($data['SYS_301'])) { ?> style="display:none"<?php } ?>>
                            <label class="col-md-2 control-label"><?php echo dr_lang('地址唯一规则'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_URL_ONLY]" value="0" <?php if (empty($data['SYS_URL_ONLY'])) { ?>checked<?php } ?> /> <?php echo dr_lang('模糊匹配'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_URL_ONLY]" value="1" <?php if ($data['SYS_URL_ONLY']) { ?>checked<?php } ?> /> <?php echo dr_lang('精确匹配'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('例如地址是abc/1.html，在地址前后加任意参数时，精确匹配模式下会404，模糊匹配模式下会正常识别'); ?></span>
                            </div>
                        </div>

                        <div class="form-group ">
                            <label class="col-md-2 control-label"><?php echo dr_lang('服务器伪静态'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline" style="margin-top:-3px">
                                    <label><?php echo((string)$_SERVER['SERVER_SOFTWARE']) ?></label>
                                    <label style="padding-left: 10px"><a class="btn btn-sm blue" href="javascript:dr_iframe_show('', '<?php echo dr_url('api/rewrite_code'); ?>');"> <?php echo dr_lang('部署代码'); ?> </a></label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="tab-pane  <?php if ($page==4) { ?>active<?php } ?>" id="tab_4">
                    <div class="form-body">

                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('模式选择'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_ADMIN_MODE]" value="0" <?php if (empty($data['SYS_ADMIN_MODE'])) { ?>checked<?php } ?> /> <?php echo dr_lang('开启'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_ADMIN_MODE]" value="1" <?php if ($data['SYS_ADMIN_MODE']) { ?>checked<?php } ?> /> <?php echo dr_lang('关闭'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('是否在后台登录界面显示完整模式和简化模式的切换选项'); ?></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('登录验证码'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_ADMIN_CODE]" value="1" <?php if ($data['SYS_ADMIN_CODE']) { ?>checked<?php } ?> /> <?php echo dr_lang('开启'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_ADMIN_CODE]" value="0" <?php if (empty($data['SYS_ADMIN_CODE'])) { ?>checked<?php } ?> /> <?php echo dr_lang('关闭'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('登录后台的验证码开关'); ?></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('快捷方式登录'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_ADMIN_OAUTH]" value="1" <?php if ($data['SYS_ADMIN_OAUTH']) { ?>checked<?php } ?> /> <?php echo dr_lang('开启'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_ADMIN_OAUTH]" value="0" <?php if (empty($data['SYS_ADMIN_OAUTH'])) { ?>checked<?php } ?> /> <?php echo dr_lang('关闭'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('登录后台时使用快捷方式登录，需要提前在用户设置中开通快捷登录参数'); ?></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('密码最大错误次数'); ?></label>
                            <div class="col-md-9">
                                <div class="input-inline input-medium">
                                    <div class="input-group">
                                        <input type="text" name="data[SYS_ADMIN_LOGINS]" value="<?php echo $data['SYS_ADMIN_LOGINS'] ? $data['SYS_ADMIN_LOGINS'] : 10; ?>" class="form-control">
                                        <span class="input-group-addon">
                                            <?php echo dr_lang('次'); ?>
                                        </span>
                                    </div>
                                </div>
                                <span class="help-block"><?php echo dr_lang('密码错误过多时，将锁定账号禁止登录，默认10次'); ?></span>
                            </div>
                        </div>
                        <input type="hidden" name="data[SYS_ADMIN_LOGIN_TIME]" value="<?php echo $data['SYS_ADMIN_LOGIN_TIME']; ?>" class="form-control">
                    </div>
                </div>

                <div class="tab-pane  <?php if ($page==1) { ?>active<?php } ?>" id="tab_1">
                    <div class="form-body">

                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('系统安全密钥'); ?></label>
                            <div class="col-md-9">
                                <label><input class="form-control input-large" type="text" name="data[SYS_KEY]" id="sys_key" value="<?php if ($data['SYS_KEY']) { ?>************<?php } ?>" ></label>
                                <label><button style=" margin-top: -3px;" class="btn btn-sm blue" type="button" name="button" onclick="dr_to_key('sys_key')"> <?php echo dr_lang('重新生成'); ?> </button></label>
                                <span class="help-block"><?php echo dr_lang('密钥建议定期更换，更换密钥之后本次登录将会自动退出'); ?></span>
                            </div>
                        </div>
                        <div class="form-group" id="dr_row_https">
                            <label class="col-md-2 control-label"><?php echo dr_lang('HTTPS安全协议'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline" style="margin-top:-3px">
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_HTTPS]" value="1" <?php if ($data['SYS_HTTPS']) { ?>checked<?php } ?> /> <?php echo dr_lang('开启'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_HTTPS]" value="0" <?php if (empty($data['SYS_HTTPS'])) { ?>checked<?php } ?> /> <?php echo dr_lang('关闭'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><a class="btn btn-sm blue" href="javascript:dr_test_https();"> <?php echo dr_lang('测试'); ?> </a></label>
                                </div>
                                <input type="hidden" name="https_test" id="https_test" value="0">
                                <span class="help-block"><?php echo dr_lang('全站采用https安全模式，开启之前需要测试是否支持https'); ?></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('CSRF跨站验证'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_CSRF]" value="2" <?php if ($data['SYS_CSRF'] == 2) { ?>checked<?php } ?> /> <?php echo dr_lang('严格模式'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_CSRF]" value="1" <?php if ($data['SYS_CSRF'] == 1) { ?>checked<?php } ?> /> <?php echo dr_lang('宽松模式'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_CSRF]" value="0" <?php if (empty($data['SYS_CSRF'])) { ?>checked<?php } ?> /> <?php echo dr_lang('关闭'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('防止CSRF跨站攻击，宽松模式时针对后台操作不进行验证'); ?></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('CSRF验证有效期'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_CSRF_TIME]" value="1" <?php if ($data['SYS_CSRF_TIME']) { ?>checked<?php } ?> /> <?php echo dr_lang('每次生成'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_CSRF_TIME]" value="0" <?php if (empty($data['SYS_CSRF_TIME'])) { ?>checked<?php } ?> /> <?php echo dr_lang('定期生成'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('是否在每次请求时重新生成CSRF令牌'); ?></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('自动任务权限'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input name="test2" onclick="dr_cron_value(0)" type="radio" <?php if (!$data['SYS_CRON_AUTH']) { ?>checked<?php } ?> /> <?php echo dr_lang('任意服务器执行'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input name="test2" onclick="dr_cron_value(1)" type="radio" <?php if ($data['SYS_CRON_AUTH'] && $data['SYS_CRON_AUTH'] != 'cli') { ?>checked<?php } ?> /> <?php echo dr_lang('限本服务器IP'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input name="test2" onclick="dr_cron_value(2)" type="radio" <?php if ($data['SYS_CRON_AUTH'] == 'cli') { ?>checked<?php } ?> /> <?php echo dr_lang('CLI模式运行'); ?> <span></span></label>
                                </div>
                                <input type="hidden" id="dr_cron_sync" value="<?php echo $data['SYS_CRON_AUTH']; ?>" />
                                <label id="dr_cron_row" <?php if ($data['SYS_CRON_AUTH'] && $data['SYS_CRON_AUTH'] != 'cli') {  } else { ?>style="display:none"<?php } ?>><input id="dr_cron" class="form-control input-large" type="text" name="data[SYS_CRON_AUTH]" value="<?php echo $data['SYS_CRON_AUTH']; ?>" ></label>
                                <span class="help-block"><?php echo dr_lang('填写本服务器的IP地址或者CLI模式运行，可以限制任务脚本的执行权限，防止被其他人恶意执行'); ?></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('发送短信双重验证'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input name="data[SYS_SMS_IMG_CODE]" type="radio" value="0" <?php if (!$data['SYS_SMS_IMG_CODE']) { ?>checked<?php } ?> /> <?php echo dr_lang('使用图形验证'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input name="data[SYS_SMS_IMG_CODE]" type="radio" value="1" <?php if ($data['SYS_SMS_IMG_CODE']) { ?>checked<?php } ?> /> <?php echo dr_lang('关闭图形验证'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('在发送短信验证码时，加入图形验证进行双重验证，减少垃圾短信消耗'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-pane  <?php if ($page==2) { ?>active<?php } ?>" id="tab_2">
                    <div class="form-body">

                        <div class="form-group">
                            <label class="col-md-2 control-label">API_TOKEN</label>
                            <div class="col-md-9">
                                <label><input class="form-control input-large" type="text" name="data[SYS_API_TOKEN]" id="SYS_API_TOKEN" value="<?php echo $data['SYS_API_TOKEN']; ?>" ></label>
                                <label><button style=" margin-top: -3px;" class="btn btn-sm blue" type="button" name="button" onclick="dr_to_key('SYS_API_TOKEN')"> <?php echo dr_lang('重新生成'); ?> </button></label>
                                <span class="help-block"><?php echo dr_lang('用于api_token参数，留空表示不开启此功能'); ?></span>
                            </div>
                        </div>

						<div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('验证图片验证码'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_API_CODE]" value="1" <?php if ($data['SYS_API_CODE']) { ?>checked<?php } ?> /> <?php echo dr_lang('开启'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_API_CODE]" value="0" <?php if (empty($data['SYS_API_CODE'])) { ?>checked<?php } ?> /> <?php echo dr_lang('禁用'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('通过API插件提交数据、发送短信验证码时，禁用后不进行图片验证码的验证操作'); ?></span>
                            </div>
                        </div>

                        <?php if (SYS_URL_REL) { ?>
                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('API中URL路径补全'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline">
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_API_REL]" value="0" <?php if (empty($data['SYS_API_REL'])) { ?>checked<?php } ?> /> <?php echo dr_lang('绝对路径'); ?> <span></span></label>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[SYS_API_REL]" value="1" <?php if ($data['SYS_API_REL']) { ?>checked<?php } ?> /> <?php echo dr_lang('相对路径'); ?> <span></span></label>
                                </div>
                                <span class="help-block"><?php echo dr_lang('通过API插件请求数据时，返回的URL路径的方式'); ?></span>
                            </div>
                        </div>
                        <?php } ?>

                    </div>
                </div>

            </div>
        </div>
    </div>
    </div>
    <div class="portlet-body form myfooter">
        <div class="form-actions text-center">
            <button type="button" onclick="dr_ajax_submit('<?php echo dr_now_url(); ?>&page='+$('#dr_page').val(), 'myform', '2000')" class="btn blue"> <i class="fa fa-save"></i> <?php echo dr_lang('保存'); ?></button>
        </div>
    </div>
</form>
<script type="text/javascript">
    function dr_cron_value(id) {
        if (id == "1") {
            $("#dr_cron_row").show();
            $("#dr_cron").val($("#dr_cron_sync").val());
        } else if (id == "2") {
            $("#dr_cron_row").hide();
            $("#dr_cron_sync").val('cli');
            $("#dr_cron").val('cli');
        } else {
            $("#dr_cron_row").hide();
            $("#dr_cron_sync").val($("#dr_cron").val());
            $("#dr_cron").val('');
        }
    }
    function dr_to_key(id) {
        $.get("<?php echo dr_url('api/syskey'); ?>", function(data){
            $("#"+id).val(data);
        });
    }
    function dr_test_https() {
        var loading = layer.load(2, {
            shade: [0.3,'#fff'], //0.1透明度的白色背景
            time: 100000000
        });
        $.ajax({
            type: "GET",
            dataType: "json",
            url: "<?php echo dr_url('api/test_https'); ?>",
            success: function(json) {
                layer.close(loading);
                if (json.code == 1) {
                    $('#https_test').val(1);
                    dr_cmf_tips(json.code, json.msg);
                } else {
                    layer.open({
                        type: 1,
                        title: '<?php echo dr_lang("测试失败"); ?>',
                        fix:true,
                        shadeClose: true,
                        shade: 0,
                        area: [<?php echo \Phpcmf\Service::IS_PC_USER() ? '\'50%\', \'60%\'' : '"95%", "90%"' ?>],
                        content: "<div style=\"padding:30px 20px;\">"+json.msg+"</div>"
                    });
                }
            },
            error: function(HttpRequest, ajaxOptions, thrownError) {
                dr_ajax_alert_error(HttpRequest, this, thrownError);
            }
        });
    }
</script>
<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>