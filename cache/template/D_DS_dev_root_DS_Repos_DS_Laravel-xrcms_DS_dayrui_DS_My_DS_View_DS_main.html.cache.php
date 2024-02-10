<?php if ($fn_include = $this->_include("header.html")) include($fn_include);  if (is_file(WRITEPATH.'password_log.php') && $ci->_is_admin_auth('password_log/index')) { ?>
<div class="note note-danger">
    <p><a style="color: red" href="<?php echo dr_url('password_log/index'); ?>"><?php echo dr_lang('存在后台密码登录错误记录，若不是你本人操作，请及时修改密码和修改后台入口文件'); ?></a></p>
</div>
<?php }  if ($admin['adminid']==1 && !IS_OEM_CMS) {  if (is_file(CMSPATH.'Config/vip.lock') && !is_file(WRITEPATH.'config/safe.php')) { ?>
<div class="note note-danger">
    <?php if (!dr_is_app('safe')) { ?>
    <p><a style="color: red" href="<?php echo dr_url('cloud/local'); ?>">当前环境未能通过安全验证，请安装【系统安全加固】插件</a></p>
    <?php } else { ?>
    <p><a style="color: red" href="<?php echo dr_url('safe/home/index'); ?>">当前环境未能通过安全验证，请按要求设置参数</a></p>
    <?php } ?>
</div>
<?php } else if (IS_DEV) { ?>
<div class="note note-danger">
    <p><a style="color: red" href="javascript:dr_help(204);">当前环境参数已经开启开发者模式，项目上线后建议关闭开发者模式</a></p>
</div>
<?php }  } ?>


<div class="row">

    <div class="col-md-6 col-sm-6">

        <?php if ($admin['adminid']==1 && !IS_OEM_CMS) { ?>
        <div class="portlet light bordered myportlet ">
            <div class="portlet-title tabbable-line">
                <div class="caption">
                    <i class="fa fa-cog"></i>
                    <span class="caption-subject"> <a style="color:#666" href="<?php echo dr_url('cloud/index'); ?>"><?php echo dr_lang('程序信息'); ?></a> </span>
                </div>
            </div>
            <div class="portlet-body">
                <ul class="use-info">
                    <li>
                        <span>系统版本：</span>
                        <a target="_blank" href="http://www.xunruicms.com/">v<?php echo $cmf_version; ?></a>
                        <a id="dr_cmf_update" href="<?php echo dr_url('cloud/update'); ?>" style="margin-left: 10px;display: none" class="badge badge-danger badge-roundless">  </a>
                    </li>

                </ul>
            </div>
        </div>
		<script>
            $(function () {
                <?php if (!defined('SYS_NOT_UPDATE') || !SYS_NOT_UPDATE) { ?>
                dr_check_version();
                <?php } else { ?>
                $('#dr_cmf_update').removeClass('badge-danger');
                $('#dr_cmf_update').show();
                $('#dr_cmf_update').attr('href', 'javascript:dr_check_version();');
                $('#dr_cmf_update').html('<?php echo dr_lang('检测版本'); ?>');
                <?php } ?>
                });
                function dr_check_version(){
                    $('#dr_cmf_update').html('<?php echo dr_lang('检测版本进行中...'); ?>');
                    $.ajax({type: "GET",dataType:"json", url: "<?php echo dr_url('cloud/check_version'); ?>&id=cms-1&isindex=1&version=<?php echo CMF_VERSION; ?>",
                        success: function(json) {
                            if (json.code) {
                                $('#dr_cmf_update').addClass('badge-danger');
                                $('#dr_cmf_update').show();
                                $('#dr_cmf_update').html(json.msg);
                            } else {
                                $('#dr_cmf_update').html("");
                            }
                        }
                    });
                }
        </script>

        <?php }  if ($fn_include = $this->_include("main/couts.html")) include($fn_include); ?>


    </div>

    <div class="col-md-6 col-sm-6">

		<?php if ($fn_include = $this->_include("main/notice.html")) include($fn_include); ?>


    </div>

</div>



</div>


<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>