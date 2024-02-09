<?php if ($fn_include = $this->_include("header.html")) include($fn_include);  if ($admin['adminid']==1 && !IS_OEM_CMS) {  if (is_file(CMSPATH.'Config/vip.lock') && !is_file(WRITEPATH.'config/safe.php')) { ?>
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

<form class="form-horizontal" role="form" id="myform">
<div class="row">
    <div class="col-md-6">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject font-green sbold ">系统信息</span>
                </div>

            </div>
            <div class="portlet-body">
                <div class="form-body fc-yun-list">

                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo dr_lang('系统版本'); ?></label>
                        <div class="col-md-9">
                            <div class="form-control-static"><a href="<?php echo dr_url('cloud/update'); ?>"><?php echo $cmf_version['version']; ?></a>
                                <a id="dr_cmf_update" href="<?php echo dr_url('cloud/update'); ?>" style="margin-left: 10px;display: none" class="badge badge-danger badge-roundless">  </a>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo dr_lang('发布时间'); ?></label>
                        <div class="col-md-9">
                            <div class="form-control-static"><?php echo $cmf_version['updatetime']; ?></div>
                        </div>
                    </div>
					
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo dr_lang('版权所有'); ?></label>
                        <div class="col-md-9">
                            <div class="form-control-static"><a href="<?php echo $license['url']; ?>" target="_blank"><?php echo $license['name']; ?></a></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo dr_lang('官方网站'); ?></label>
                        <div class="col-md-9">
                            <div class="form-control-static"><a href="<?php echo $license['url']; ?>" target="_blank"><?php echo $license['url']; ?></a></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption">
                    <span class="caption-subject font-green sbold "><?php echo dr_lang('服务器信息'); ?></span>
                </div>
            </div>
            <div class="portlet-body">
                <div class="form-body fc-yun-list">


                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo dr_lang('上传最大值'); ?></label>
                        <div class="col-md-9">
                            <div class="form-control-static"><a href="javascript:dr_iframe_show('show', '<?php echo dr_url('api/config'); ?>');"><?php echo @ini_get("upload_max_filesize"); ?></a></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo dr_lang('POST最大值'); ?></label>
                        <div class="col-md-9">
                            <div class="form-control-static"><a href="javascript:dr_iframe_show('show', '<?php echo dr_url('api/config'); ?>');"><?php echo @ini_get("post_max_size"); ?></a></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo dr_lang('PHP内存上限'); ?></label>
                        <div class="col-md-9">
                            <div class="form-control-static"><a href="javascript:dr_iframe_show('show', '<?php echo dr_url('api/config'); ?>');"><?php echo @ini_get("memory_limit"); ?></a></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo dr_lang('提交表单数量'); ?></label>
                        <div class="col-md-9">
                            <div class="form-control-static"><a href="javascript:dr_iframe_show('show', '<?php echo dr_url('api/config'); ?>');"><?php echo @ini_get("max_input_vars"); ?></a></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo dr_lang('Web网站目录'); ?></label>
                        <div class="col-md-9">
                            <div class="form-control-static"><?php echo WEBPATH; ?></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo dr_lang('核心程序目录'); ?></label>
                        <div class="col-md-9">
                            <div class="form-control-static"><?php echo FCPATH; ?></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo dr_lang('附件存储目录'); ?></label>
                        <div class="col-md-9">
                            <div class="form-control-static"><?php echo SYS_UPLOAD_PATH; ?></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo dr_lang('模板文件目录'); ?></label>
                        <div class="col-md-9">
                            <div class="form-control-static"><?php echo TPLPATH; ?></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label"><?php echo dr_lang('应用程序目录'); ?></label>
                        <div class="col-md-9">
                            <div class="form-control-static"><?php echo APPSPATH; ?></div>
                        </div>
                    </div>


                </div>
            </div>
        </div>
    </div>

</div>
</form>

<script>
    $(function () {
        $.ajax({type: "GET",dataType:"json", url: "<?php echo dr_url('api/version_cmf'); ?>",
            success: function(json) {
                if (json.code) {
                    $('#dr_cmf_update').show();
                    $('#dr_cmf_update').html(json.msg);
                }
            }
        });
    });
</script>
<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>