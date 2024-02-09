<?php if ($fn_include = $this->_include("header.html")) include($fn_include); ?>

<script type="text/javascript">
    <?php if ($is_verify) { ?>
    $(function () {
        $('#dr_row_hits').hide();
    });
    <?php } else {  echo $auto_form_data_ajax;  } ?>
    function dr_syncat() {
        var title = '<i class="fa fa-refresh"></i> <?php echo dr_lang('同步到其他栏目'); ?>';
        var url = '<?php echo dr_url(MOD_DIR.'/home/syncat_edit'); ?>&catid='+$('#dr_syncatid').val();
        layer.open({
            type: 2,
            title: title,
            shadeClose: true,
            shade: 0,
            area: [<?php echo \Phpcmf\Service::IS_PC_USER() ? '"40%", "70%"' : '"95%", "90%"' ?>],
            btn: [lang['ok']],
            yes: function(index, layero){
                var body = layer.getChildFrame('body', index);
                $(body).find('.form-group').removeClass('has-error');
                // 延迟加载
                var loading = layer.load(2, {
                    shade: [0.3,'#fff'], //0.1透明度的白色背景
                    time: 10000
                });
                $.ajax({type: "POST",dataType:"json", url: url, data: $(body).find('#myform').serialize(),
                    success: function(json) {
                        layer.close(loading);
                        if (json.code == 1) {
                            layer.close(index);
                            $('#dr_syncatid').val(json.data);
                            $('#dr_syncat_text').show();
                            $('#dr_syncat_num').html(json.msg);
                        } else {
                            dr_tips(json.code, json.msg);
                        }
                        return false;
                    },
                    error: function(HttpRequest, ajaxOptions, thrownError) {
                        dr_ajax_alert_error(HttpRequest, this, thrownError);;
                    }
                });
                return false;
            },
            content: url+'&is_iframe=1'
        });
    }

    // 定时发布
    function dr_ajax_time_submit() {

        layer.open({
            type: 2,
            title: '<i class="fa fa-clock-o"></i> <?php echo dr_lang('定时发布'); ?>',
            fix:true,
            scrollbar: false,
            shadeClose: true,
            shade: 0,
            area: [<?php echo \Phpcmf\Service::IS_PC_USER() ? '"500px", "450px"' : '"95%", "90%"' ?>],
            btn: [lang['ok']],
            success: function (json) {
                if (json.code == 0) {
                    layer.close();
                    dr_tips(json.code, json.msg);
                }
            },
            yes: function(index, layero){
                var body = layer.getChildFrame('body', index);
                var loading = layer.load(2, {
                    time: 10000
                });
                var url = '<?php echo dr_url(APP_DIR.'/time/add'); ?>';
                var posttime = $(body).find('#posttime').val();
                if (posttime.length < 5) {
                    layer.closeAll('loading');
                    dr_tips(0, "<?php echo dr_lang('发布时间必须填写'); ?>");
                    return false;
                }
                $('#myform .form-group').removeClass('has-error');
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: url+"&posttime="+posttime,
                    data: $("#myform").serialize(),
                    success: function(json) {
                        layer.close(loading);
                        if (json.code > 0) {
                            dr_tips(1, json.msg);
                            setTimeout("window.location.href = '<?php echo dr_url(APP_DIR.'/time/index'); ?>'", 2000);
                        } else {
                            dr_tips(0, json.msg);
                            if (json.data.field) {
                                $('#dr_row_'+json.data.field).addClass('has-error');
                                $('#dr_'+json.data.field).focus();
                            }
                        }
                    },
                    error: function(HttpRequest, ajaxOptions, thrownError) {
                        dr_ajax_alert_error(HttpRequest, this, thrownError);;
                    }
                });

                return false;
            },
            content: "<?php echo dr_url(APP_DIR.'/time/add'); ?>&is_iframe=1"
        });
    }
	// 退稿
	function dr_ajax_tui_submit() {

        layer.open({
            type: 2,
            title: '<i class="fa fa-sign-out"></i> <?php echo dr_lang('退稿'); ?>',
            fix:true,
            scrollbar: false,
            shadeClose: true,
            shade: 0,
            area: [<?php echo \Phpcmf\Service::IS_PC_USER() ? '"40%", "70%"' : '"95%", "90%"' ?>],
            btn: [lang['ok']],
            success: function (json) {
                if (json.code == 0) {
                    layer.close();
                    dr_tips(json.code, json.msg);
                }
            },
            yes: function(index, layero){
                var body = layer.getChildFrame('body', index);
                var loading = layer.load(2, {
                    time: 10000
                });
                var url = '<?php echo dr_url(APP_DIR.'/home/tuigao_edit'); ?>&id=<?php echo $id; ?>';
                var note = $(body).find('#dr_note').val();
                var clear = $(body).find("input[name='clear']:checked").val();
		
                if (note.length == 0) {
                    layer.closeAll('loading');
                    dr_tips(0, "<?php echo dr_lang('退稿理由必须填写'); ?>");
                    return false;
                }
                $('#myform .form-group').removeClass('has-error');
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: url+"&note="+note+"&clear="+clear,
                    data: $("#myform").serialize(),
                    success: function(json) {
                        layer.close(loading);
                        if (json.code == 1) {
                            dr_tips(1, json.msg);
                            setTimeout("window.location.href = '<?php echo $reply_url; ?>'", 2000);
                        } else {
                            dr_tips(0, json.msg);
                            if (json.data.field) {
                                $('#dr_row_'+json.data.field).addClass('has-error');
                                $('#dr_'+json.data.field).focus();
                            }
                        }
                    },
                    error: function(HttpRequest, ajaxOptions, thrownError) {
                        dr_ajax_alert_error(HttpRequest, this, thrownError);;
                    }
                });

                return false;
            },
            content: "<?php echo dr_url(APP_DIR.'/home/tui_edit'); ?>&page=5&is_iframe=1"
        });
    }
    function dr_ajax_verify_submit() {
        $('#dr_is_draft').val(0);
        dr_ajax_submit('<?php echo dr_now_url(); ?>', 'myform', '2000', '<?php echo $reply_url; ?>');
    }
    $(function () {
        <?php if (isset($_GET['is_verify_iframe']) && $_GET['is_verify_iframe']) { ?>
        setTimeout(function(){
            $('#dr_is_draft').val(0);
            $.ajax({
                type: "POST",
                dataType: "json",
                url: '<?php echo dr_now_url(); ?>',
                data: $("#myform").serialize(),
                success: function(json) {
                    if (json.code) {
                        if (json.data.htmlfile) {
                            // 执行生成htmljs
                            $.ajax({
                                type: "GET",
                                url: json.data.htmlfile,
                                dataType: "jsonp",
                                success: function(json){ },
                                error: function(){ }
                            });
                        }
                        if (json.data.htmllist) {
                            // 执行生成htmljs
                            $.ajax({
                                type: "GET",
                                url: json.data.htmllist,
                                dataType: "jsonp",
                                success: function(json){ },
                                error: function(){ }
                            });
                        }
                        parent.$('#content_<?php echo $id; ?>').addClass("badge badge-success");
                        parent.$('#content_<?php echo $id; ?>').html('<?php echo dr_lang("成功"); ?>');
                    } else {
                        parent.$('#content_<?php echo $id; ?>').addClass("badge badge-danger");
                        parent.$('#content_<?php echo $id; ?>').html(json.msg);
                    }
                },
                error: function(HttpRequest, ajaxOptions, thrownError) {
                    parent.$('#content_<?php echo $id; ?>').addClass("badge badge-danger");
                    parent.$('#content_<?php echo $id; ?>').html('<?php echo dr_lang("执行失败"); ?>');
                }
            });
        }, 5000);
        <?php } ?>
    });
</script>
<?php if ($post_notice_msg) { ?>
<div class="note note-danger">
    <p><?php echo $post_notice_msg; ?></p>
</div>
<?php } ?>
<form action="" class="form-horizontal" method="post" name="myform" id="myform">
    <?php echo $form; ?>
    <div class="myfbody">
        <?php if ($mymerge && $ci->module['setting']['merge']) { ?>
        <div class="tabbable-line margin-bottom-20">
            <ul class="nav nav-tabs">
                <li class="active">
                    <a toid="dr_default" data-toggle="tab"><?php echo dr_lang('基本内容'); ?></a>
                </li>
                <?php if (isset($mymerge) && is_array($mymerge) && $mymerge) { $key_t=-1;$count_t=dr_count($mymerge);foreach ($mymerge as $t) { $key_t++; $is_first=$key_t==0 ? 1 : 0;$is_last=$count_t==$key_t+1 ? 1 : 0;?>
                <li>
                    <a toid="dr_row_<?php echo $t; ?>" data-toggle="tab"><?php echo $field[$t]['name']; ?></a>
                </li>
                <?php } }  if ($diyfield) { ?>
                <li>
                    <a toid="dr_orther" data-toggle="tab"><?php echo dr_lang('其他内容'); ?></a>
                </li>
                <?php } ?>
            </ul>
        </div>
        <script type="text/javascript">
            $(function () {
                $('.myfield-main .portlet.light.bordered').hide();
                $('#dr_default').show();
                $('.nav-tabs a').click(function () {
                    var tid = $(this).attr('toid');
                    $('.myfield-main .portlet.light.bordered').hide();
                    $('#'+tid).show();
                });
            });
        </script>
        <?php } ?>
        <div class="row ">
            <div class="myfield-main <?php if (!$is_right_field || $is_mobile) { ?>col-md-12<?php } else { ?>col-md-9<?php } ?>">

                <div class="portlet light bordered" id="dr_default">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green sbold "><?php echo dr_lang('基本内容'); ?></span>
                        </div>

                        <div class="actions">
                            <?php if ($draft_list) { ?>
                            <div class="btn-group">
                                <a class="btn btn-default dropdown-toggle" data-toggle="dropdown" href="javascript:;" aria-expanded="false">
                                    <?php echo dr_lang('草稿'); ?>
                                    <i class="fa fa-angle-down"></i>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a href="<?php echo $draft_url; ?>"> <?php echo dr_lang('查看原文'); ?> </a>
                                    </li>
                                    <?php if (isset($draft_list) && is_array($draft_list) && $draft_list) { $key_t=-1;$count_t=dr_count($draft_list);foreach ($draft_list as $t) { $key_t++; $is_first=$key_t==0 ? 1 : 0;$is_last=$count_t==$key_t+1 ? 1 : 0;?>
                                    <li>
                                        <a href="<?php echo $draft_url; ?>&did=<?php echo $t['id']; ?>" <?php if ($t['id']==$did) { ?>style="color:red"<?php } ?>> <?php echo dr_date($t['inputtime']); ?> - <?php echo $t['title']; ?> </a>
                                    </li>
                                    <?php } } ?>
                                </ul>
                            </div>
                            <?php } ?>
                            <div class="btn-group">
                                <a class="btn" href="<?php echo $reply_url; ?>"> <i class="fa fa-mail-reply"></i> <?php echo dr_lang('返回列表'); ?></a>
                            </div>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="form-body">
                            <div class="form-group <?php if (!$is_category_show) { ?>hide<?php } ?>">
                                <label class="col-md-2 control-label"><?php echo dr_lang('栏目'); ?></label>
                                <div class="col-md-9">
                                    <?php $catid && $cat=dr_cat_value($catid);  if ($catid && $cat && $cat['setting']['notedit']) { ?>
                                    <label style="margin-top: 7px;">
                                        <span class="label label-sm label-success circle"><?php echo $cat['name']; ?></span>
                                    </label>
                                    <input type="hidden" id="dr_catid" name="catid" value="<?php echo $catid; ?>">
                                    <?php } else { ?>
                                        <label style="margin-right:10px"><?php echo $select; ?></label>
                                        <?php if ($module['setting']['sync_category']) {  if (!$id || $is_sync_cat) { ?>
                                        <label style="margin-right:10px"><a href="javascript:;" onclick="dr_syncat()">[<?php echo dr_lang('同步发布到其他栏目'); ?>]</a></label>
                                        <label>
                                            <input id="dr_syncatid" name="sync_cat" type="hidden" value="<?php echo $is_sync_cat; ?>" />
                                            <span id="dr_syncat_text" class="label label-success" style="display: <?php if ($is_sync_cat) { ?>blank<?php } else { ?>none<?php } ?>;"><b id="dr_syncat_num"><?php echo substr_count((string)$is_sync_cat, ',') + 1; ?></b></span>
                                        </label>
                                        <?php } else if ($link_id != 0) { ?>
                                        <label><?php echo dr_lang('修改内容时会同步更新其他外联文档'); ?></label>
                                        <?php }  }  if (CI_DEBUG && $ci->_is_admin_auth($module['dirname'].'/category/index')) { ?>
										<label style="margin-right:10px"><a href="<?php echo dr_url($module['dirname'].'/category/index'); ?>">[<?php echo dr_lang('栏目管理'); ?>]</a></label>
										<?php }  } ?>
                                </div>
                            </div>
                            <?php echo $myfield; ?>
                        </div>
                    </div>
                </div>

                <?php if ($diyfield) { ?>
                <div class="portlet light bordered" id="dr_orther">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green sbold "><?php echo dr_lang('其他内容'); ?></span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="form-body">
                            <?php echo $diyfield; ?>
                        </div>
                    </div>
                </div>
                <?php } ?>

            </div>
            <div class="<?php if (!$is_right_field || $is_mobile) { ?>col-md-12<?php } else { ?>col-md-3<?php } ?> my-sysfield" <?php if (!$is_right_field) { ?> style="display: none"<?php } ?>>
                <div class="portlet light bordered">
                    <div class="portlet-body">
                        <div class="form-body">
                            <?php echo $sysfield;  if ($is_flag) { ?>
                            <div class="form-group">
                                <label class="col-md-2 control-label"><?php echo dr_lang('推荐位'); ?></label>
                                <div class="col-md-9">
                                    <div class="mt-checkbox-list">
                                        <?php if (isset($is_flag) && is_array($is_flag) && $is_flag) { $key_f=-1;$count_f=dr_count($is_flag);foreach ($is_flag as $n=>$f) { $key_f++; $is_first=$key_f==0 ? 1 : 0;$is_last=$count_f==$key_f+1 ? 1 : 0; ?>
                                        <label class="mt-checkbox mt-checkbox-outline"> <?php echo dr_lang($f['name']); ?>
                                            <input type="checkbox" <?php if (dr_in_array($n, $my_flag)) { ?> checked<?php } ?> value="<?php echo $n; ?>" name="flag[]" />
                                            <span></span>
                                        </label>
                                        <?php } } ?>
                                    </div>
                                </div>
                            </div>
                            <?php }  if (!$is_verify && $is_post_time) { ?>
                            <div class="form-group">
                                <label class="col-md-2 control-label"><?php echo dr_lang('定时发布时间'); ?></label>
                                <div class="col-md-9">
                                    <span class="form-date input-group">
                                        <div class="input-group date field_date_post_inputtime">
                                            <input id="posttime" name="posttime" type="text" style="width:160px;" value="<?php echo dr_date($posttime, 'Y-m-d H:i:s'); ?>"  required="required" class="form-control ">
                                            <span class="input-group-btn">
                                                <button class="btn default date-set" type="button">
                                                    <i class="fa fa-calendar"></i>
                                                </button>
                                            </span>
                                        </div>
                                        <script>
                                        $(function(){
                                            $(".field_date_post_inputtime").datetimepicker({
                                                isRTL: App.isRTL(),
                                                format: "yyyy-mm-dd hh:ii:ss",
                                                showMeridian: true,
                                                autoclose: true,
                                                pickerPosition: (App.isRTL() ? "bottom-right" : "bottom-left"),
                                                todayBtn: true
                                            });
                                        });
                                        </script>
                                    </span>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <?php if ($is_verify) { ?>
        <div class="row ">
            <div class="col-md-12 ">
                <div class="portlet light bordered">
                    <div class="portlet-title">
                        <div class="caption">
                            <span class="caption-subject font-green sbold "><?php echo dr_lang('内容审核'); ?></span>
                        </div>
                    </div>
                    <div class="portlet-body">
                        <div class="form-body">

                            <?php if ($status) { ?>
                            <div class="form-group">
                                <label class="col-md-2 control-label"><?php echo dr_lang('当前状态'); ?></label>
                                <div class="col-md-9">
                                    <p class="form-control-static">
                                        <span class="label label-warning"> <?php echo dr_lang('%s审中', $status); ?> </span>
                                    </p>
                                </div>
                            </div>
                            <?php } else { ?>
                            <div class="form-group">
                                <label class="col-md-2 control-label"><?php echo dr_lang('当前状态'); ?></label>
                                <div class="col-md-9">
                                    <p class="form-control-static">
                                        <span class="label label-danger"> <?php echo dr_lang('被拒绝'); ?> </span>
                                    </p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label"><?php echo dr_lang('处理账号'); ?></label>
                                <div class="col-md-9">
                                    <p class="form-control-static">
                                        <?php echo \Phpcmf\Service::L('Function_list')->author($verify['backinfo']['author'], [], $verify['backinfo']); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label"><?php echo dr_lang('处理时间'); ?></label>
                                <div class="col-md-9">
                                    <p class="form-control-static">
                                        <?php echo dr_date($verify['backinfo']['optiontime']); ?>
                                    </p>
                                </div>
                            </div>
                            <?php if ($verify['backinfo']['backcontent']) { ?>
                            <div class="form-group">
                                <label class="col-md-2 control-label"><?php echo dr_lang('审核说明'); ?></label>
                                <div class="col-md-9">
                                    <p class="form-control-static">
                                        <?php echo $verify['backinfo']['backcontent']; ?>
                                    </p>
                                </div>
                            </div>
                            <?php }  }  if ($verify_step[$status]['name']) { ?>
                            <div class="form-group">
                                <label class="col-md-2 control-label"><?php echo dr_lang('当前审核'); ?></label>
                                <div class="col-md-9">
                                    <p class="form-control-static"> <?php echo $verify_step[$status]['name']; ?> </p>
                                </div>
                            </div>
                            <?php } ?>
                            <div class="form-group">
                                <label class="col-md-2 control-label"><?php echo dr_lang('下级审核'); ?></label>
                                <div class="col-md-9">
                                    <p class="form-control-static"> <?php echo $verify_step[$verify_next]['name']; ?> </p>
                                </div>
                            </div>
                            <?php if ($is_post_user) { ?>
                            <div class="form-group">
                                <label class="col-md-2 control-label">审核人</label>
                                <div class="col-md-9">
                                    <p class="form-control-static">
                                        <?php echo \Phpcmf\Service::L('Function_list')->author($verify['backinfo']['author'], [], $verify['backinfo']); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-md-2 control-label">审核时间</label>
                                <div class="col-md-9">
                                    <p class="form-control-static">
                                        <?php echo dr_date($verify['backinfo']['optiontime']); ?>
                                    </p>
                                </div>
                            </div>
                            <?php if ($verify['backinfo']['backcontent']) { ?>
                            <div class="form-group">
                                <label class="col-md-2 control-label">审核说明</label>
                                <div class="col-md-9">
                                    <p class="form-control-static">
                                        <?php echo $verify['backinfo']['backcontent']; ?>
                                    </p>
                                </div>
                            </div>
                            <?php }  } else { ?>
                            <div class="form-group">
                                <label class="col-md-2 control-label"><?php echo dr_lang('操作状态'); ?></label>
                                <div class="col-md-9">
                                    <div class="mt-radio-inline">
                                        <label class="mt-radio mt-radio-outline"><input type="radio" onclick="$('.dr_close_msg').hide();$('.dr_verify_time').show()" name="verify[status]" value="1" <?php if (!$back_note) { ?> checked<?php } ?> /> <?php echo dr_lang('通过'); ?> <span></span></label>
                                        <label class="mt-radio mt-radio-outline"><input type="radio" onclick="$('.dr_close_msg').show();$('.dr_verify_time').hide()" name="verify[status]" value="0" <?php if ($back_note) { ?> checked<?php } ?> /> <?php echo dr_lang('拒绝'); ?> <span></span></label>
                                    </div>
                                </div>
                            </div>
                            <?php if ($verify_next == 9) { ?>
                            <div class="form-group dr_verify_time">
                                <label class="col-md-2 control-label"><?php echo dr_lang('发布时间'); ?></label>
                                <div class="col-md-9">
                                    <span class="form-date input-group">
                                    <div class="input-group date field_date_inputtime">
                                        <input id="verify_posttime" name="verify_posttime" type="text" style="width:160px;" placeholder="<?php echo dr_lang('立即发布'); ?>"  required="required" class="form-control ">
                                        <span class="input-group-btn">
                                            <button class="btn default date-set" type="button">
                                                <i class="fa fa-calendar"></i>
                                            </button>
                                        </span>
                                    </div>
                                    <script>
                                    $(function(){
                                        $(".field_date_inputtime").datetimepicker({
                                            isRTL: App.isRTL(),
                                            format: "yyyy-mm-dd hh:ii:ss",
                                            showMeridian: true,
                                            autoclose: true,
                                            pickerPosition: (App.isRTL() ? "bottom-right" : "bottom-left"),
                                            todayBtn: true
                                        });
                                    });
                                    </script>
                                </span>
                                    <span class="help-block"><?php echo dr_lang('可设置定时发布，定时时间不能晚于当前时间'); ?></span>
                                </div>
                            </div>
                            <?php }  if (dr_is_app('cverify')) { ?>
                            <div class="form-group ">
                                <?php } else { ?>
                            <div class="form-group dr_close_msg" style="<?php if (!$back_note) { ?> display:none<?php } ?>">
                                <?php } ?>
                                <label class="col-md-2 control-label"><?php echo dr_lang('审核说明'); ?></label>
                                <div class="col-md-9">
                                    <textarea id="dr_verify_msg" class="form-control" style="height:100px" name="verify[msg]"><?php echo $back_note; ?></textarea>
                                </div>
                            </div>
                                <?php if (dr_is_app('cverify')) { ?>
                                <div class="form-group ">
                                    <?php } else { ?>
                                    <div class="form-group dr_close_msg" style="<?php if (!$back_note) { ?> display:none<?php } ?>">
                                        <?php } ?>
                                <label class="col-md-2 control-label"><?php echo dr_lang('常用理由'); ?></label>
                                <div class="col-md-9">
                                    <label>
                                        <select class="form-control" onchange="javascript:$('#dr_verify_msg').val(this.value)">
                                            <option value=""> -- </option>
                                            <?php if (isset($verify_msg) && is_array($verify_msg) && $verify_msg) { $key_t=-1;$count_t=dr_count($verify_msg);foreach ($verify_msg as $t) { $key_t++; $is_first=$key_t==0 ? 1 : 0;$is_last=$count_t==$key_t+1 ? 1 : 0;?>
                                            <option value="<?php echo $t; ?>"><?php echo $t; ?></option>
                                            <?php } } ?>
                                        </select>
                                    </label>
                                </div>
                            </div>
                            <?php } ?>



                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>

    </div>


    <div class="portlet-body form myfooter">
        <div class="form-actions text-center">
            <?php if (!defined('IS_MODULE_RECYCLE')) {  if ($is_verify) { ?>
                    <label><button type="button" onclick="dr_ajax_verify_submit()" class="btn blue"> <i class="fa fa-save"></i> <?php echo dr_lang('提交审核'); ?></button></label>
                <?php } else if ($is_post_time) { ?>
                    <label><button type="button" onclick="$('#dr_is_draft').val(0);dr_ajax_submit('<?php echo dr_now_url(); ?>&posttime=<?php echo dr_date($posttime, 'Y-m-d H:i:s'); ?>', 'myform', '2000')" class="btn blue"> <i class="fa fa-save"></i> <?php echo dr_lang('保存内容'); ?></button></label>
                <?php } else { ?>
                    <label><button type="button" onclick="$('#dr_is_draft').val(0);dr_ajax_submit('<?php echo dr_now_url(); ?>&is_self=1', 'myform', '2000')" class="btn blue"> <i class="fa fa-save"></i> <?php echo dr_lang('保存内容'); ?></button></label>
                    <label><button type="button" onclick="$('#dr_is_draft').val(0);dr_ajax_submit('<?php echo dr_now_url(); ?>', 'myform', '2000', '<?php echo $post_url; ?>&catid='+$('#dr_catid').val())" class="btn green"> <i class="fa fa-plus"></i> <?php echo dr_lang('保存再添加'); ?></button></label>
                    <label><button type="button" onclick="$('#dr_is_draft').val(0);dr_ajax_submit('<?php echo dr_now_url(); ?>', 'myform', '2000', '<?php echo $reply_url; ?>')" class="btn yellow"> <i class="fa fa-mail-reply-all"></i> <?php echo dr_lang('保存并返回'); ?></button></label>
                    <?php if ($ci->_is_admin_auth(MOD_DIR.'/draft/add')) { ?>
                    <label><button type="button" onclick="$('#dr_is_draft').val(1);dr_ajax_submit('<?php echo dr_now_url(); ?>', 'myform', '2000')" class="btn red"> <i class="fa fa-pencil"></i> <?php echo dr_lang('保存草稿'); ?></button></label>
                    <?php }  if (!$id && $ci->_is_admin_auth(MOD_DIR.'/time/add')) { ?>
                    <label><button type="button" onclick="dr_ajax_time_submit()" class="btn dark"> <i class="fa fa-clock-o"></i> <?php echo dr_lang('定时发布'); ?></button></label>
                    <?php }  if ($is_form_cache) { ?>
                    <label><button type="button" onclick="auto_form_data_delete()" class="btn red"> <i class="fa fa-trash"></i> <?php echo dr_lang('删除历史缓存'); ?></button></label>
                    <?php }  echo $clink_btn;  }  if ($id && !$is_verify && !$is_post_user) { ?>
                <label><button type="button" onclick="dr_ajax_tui_submit()" class="btn green"> <i class="fa fa-sign-out"></i> <?php echo dr_lang('退稿'); ?></button></label>
                <?php }  } else { ?>
            <label><a href="<?php echo $reply_url; ?>" class="btn yellow"> <i class="fa fa-mail-reply-all"></i> <?php echo dr_lang('返回列表'); ?></a></label>
            <label><button type="button" onclick="$('#dr_is_draft').val(0);dr_ajax_submit('<?php echo dr_now_url(); ?>', 'myform', '2000', '<?php echo $reply_url; ?>')" class="btn blue"> <i class="fa fa-save"></i> <?php echo dr_lang('恢复此内容'); ?></button></label>
            <?php }  if ($web_url) { ?>
            <label><a href="<?php echo $web_url; ?>" target="_blank" class="btn dark"> <i class="fa fa-link"></i> <?php echo dr_lang('浏览'); ?></a></label>
            <?php } ?>
        </div>
    </div>
</form>

<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>