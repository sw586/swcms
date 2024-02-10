<?php if ($fn_include = $this->_include("header.html")) include($fn_include); ?>


<form action="" class="form-horizontal " method="post" name="myform" id="myform">
    <?php echo $form; ?>
    <div class="myfbody">
    <div class="portlet bordered light ">
        <div class="portlet-title tabbable-line">
            <ul class="nav nav-tabs" style="float:left;">
                <li class="<?php if ($page==0) { ?>active<?php } ?>">
                    <a href="#tab_0" data-toggle="tab" onclick="$('#dr_page').val('0')"> <i class="fa fa-cloud"></i> <?php echo dr_lang('存储策略'); ?> </a>
                </li>
            </ul>
        </div>
        <div class="portlet-body">
            <div class="tab-content">

                <div class="tab-pane <?php if ($page==0) { ?>active<?php } ?>" id="tab_0">
                    <div class="form-body form">

                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('名称'); ?></label>
                            <div class="col-md-9">
                                <input type="text" class="form-control input-large" value="<?php echo htmlspecialchars((string)$data['name']); ?>" name="data[name]" />
                                <span class="help-block"><?php echo dr_lang('给它一个描述名称'); ?></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('存储类型'); ?></label>
                            <div class="col-md-9">
                                <div class="mt-radio-inline">
                                    <?php if (isset($ci->type) && is_array($ci->type) && $ci->type) { $key_n=-1;$count_n=dr_count($ci->type);foreach ($ci->type as $i=>$n) { $key_n++; $is_first=$key_n==0 ? 1 : 0;$is_last=$count_n==$key_n+1 ? 1 : 0; ?>
                                    <label class="mt-radio mt-radio-outline"><input type="radio" name="data[type]" onclick="dr_remote('<?php echo $i; ?>')" value="<?php echo $i; ?>" <?php if ((int)$data['type'] == $i) { ?>checked<?php } ?> /> <?php echo dr_lang($n['name']); ?> <span></span> </label>
                                    <?php } } ?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group r r0">
                            <label class="col-md-2 control-label"><?php echo dr_lang('使用说明'); ?></label>
                            <div class="col-md-9">
                                <p class="form-control-static"> <?php echo dr_lang('本地磁盘存储是将文件存储到本地的一块盘之中'); ?> </p>
                            </div>
                        </div>
                        <div class="form-group r r0">
                            <label class="col-md-2 control-label"><?php echo dr_lang('本地存储路径'); ?></label>
                            <div class="col-md-7">
                                <input class="form-control" type="text" name="data[value][0][path]" value="<?php echo htmlspecialchars((string)$data['value']['path']); ?>" />
                                <span class="help-block"><?php echo dr_lang('填写磁盘绝对路径或者相当于附件目录的目录路径，一定要以“/”结尾'); ?></span>
                            </div>
                        </div>

                        <?php if (isset($ci->load_file) && is_array($ci->load_file) && $ci->load_file) { $key_tp=-1;$count_tp=dr_count($ci->load_file);foreach ($ci->load_file as $tp) { $key_tp++; $is_first=$key_tp==0 ? 1 : 0;$is_last=$count_tp==$key_tp+1 ? 1 : 0; if ($fn_include = $this->_load("$tp")) include($fn_include);  } } ?>



                        <div class="form-group">
                            <label class="col-md-2 control-label"><?php echo dr_lang('附件访问URL'); ?></label>
                            <div class="col-md-7">
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars((string)$data['url']); ?>" name="data[url]" />
                                <span class="help-block"><?php echo dr_lang('浏览器可访问的URL地址，必须以http://或https://开头，要以“/”结尾'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    <div class="portlet-body form myfooter">
        <div class="form-actions text-center">
            <label><button type="button" onclick="dr_ajax_submit('<?php echo dr_now_url(); ?>&page='+$('#dr_page').val(), 'myform', '2000')" class="btn blue"> <i class="fa fa-save"></i> <?php echo dr_lang('保存'); ?></button></label>
                <label><button type="button" onclick="dr_test_attach()" class="btn red"> <i class="fa fa-cloud"></i> <?php echo dr_lang('测试'); ?></button></label>
        </div>
    </div>
</form>
<script type="text/javascript">
    $(function() {
        dr_remote(<?php echo intval($data['type']); ?>);
    });
    function dr_test_attach() {
        var loading = layer.load(2, {
            shade: [0.3,'#fff'], //0.1透明度的白色背景
            time: 10000
        });
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo dr_url('api/test_attach'); ?>",
            data: $("#myform").serialize(),
            success: function(json) {
                layer.close(loading);
                dr_tips(json.code, json.msg, -1);
            },
            error: function(HttpRequest, ajaxOptions, thrownError) {
                dr_ajax_alert_error(HttpRequest, this, thrownError);
            }
        });
    }
    function dr_remote(i) {
        $('.r').hide();
        $('.r'+i).show();
    }
</script>
<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>