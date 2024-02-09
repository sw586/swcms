<?php if ($fn_include = $this->_include("header.html")) include($fn_include);  if ($fn_include = $this->_include("api_list_date_search.html")) include($fn_include); ?>
<script type="text/javascript">
    function dr_module_delete() {
        var url = '<?php echo dr_url(APP_DIR.'/home/del'); ?>&is_iframe=1';
        if (is_mobile_cms == 1) {
            width = height = '90%';
        }
        var data = $("#myform").serialize();
        // 判断是否有选中数据
        var params = data.split('&');
        var dataObj = {};
        for (var i = 0; i < params.length; i++) {
            var pair = params[i].split('=');
            dataObj[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
        }
        if (!'ids[]' in dataObj || dataObj['ids[]'] === null || dataObj['ids[]'] === undefined) {
            dr_tips(0, '<?php echo dr_lang('没有选择内容'); ?>');
            return;
        }
        layer.open({
            type: 2,
            title: '<?php echo dr_lang('删除确认'); ?>',
            shadeClose: true,
            shade: 0,
            area: [<?php echo \Phpcmf\Service::IS_PC_USER() ? '\'50%\', \'60%\'' : '"95%", "90%"' ?>],
            btn: [lang['ok']],
            yes: function(index, layero){
            var body = layer.getChildFrame('body', index);
            $(body).find('.form-group').removeClass('has-error');
            // 延迟加载
            var loading = layer.load(2, {
                shade: [0.3,'#fff'], //0.1透明度的白色背景
                time: 5000
            });
            $.ajax({type: "POST",dataType:"json", url: url, data: $(body).find('#myform').serialize(),
                success: function(json) {
                    layer.close(loading);
                    if (json.code == 1) {
                        layer.close(index);
                        $('#mytable').bootstrapTable('refresh');
                    } else {
                        $(body).find('#dr_row_'+json.data.field).addClass('has-error');
                    }
                    dr_tips(json.code, json.msg);
                    return false;
                },
                error: function(HttpRequest, ajaxOptions, thrownError) {
                    dr_ajax_alert_error(HttpRequest, this, thrownError);;
                }
            });
            return false;
        },
        success: function(layero, index){
            // 主要用于后台权限验证

            dr_iframe_error(layer, index, 1);
        },
        content: url+'&'+data
    });
    }
    function dr_scjt() {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "<?php echo dr_url($uriprefix.'/scjt_edit'); ?>",
            data: $("#myform").serialize(),
            success: function(json) {
                if (json.code == 1) {
                    dr_bfb('<?php echo dr_lang('生成内容'); ?>', '', json.msg);
                } else {
                    dr_cmf_tips(json.code, json.msg);
                }

            },
            error: function(HttpRequest, ajaxOptions, thrownError) {
                dr_ajax_alert_error(HttpRequest, this, thrownError);
            }
        });
    }

    function dr_module_tuigao() {
        var width = '50%';
        var height = '60%';
        if (is_mobile_cms == 1) {
            width = height = '90%';
        }
        var url = '<?php echo dr_url(APP_DIR."/home/tui_edit"); ?>&page=6&'+$("#myform").serialize();
        layer.open({
            type: 2,
            title: '<?php echo dr_lang("批量退稿确认"); ?>',
            shadeClose: true,
            shade: 0,
            area: [width, height],
            btn: [lang['ok']],
            yes: function(index, layero){
                var body = layer.getChildFrame('body', index);
                $(body).find('.form-group').removeClass('has-error');
                // 延迟加载
                var loading = layer.load(2, {
                    shade: [0.3,'#fff'], //0.1透明度的白色背景
                    time: 5000
                });
                $.ajax({type: "POST",dataType:"json", url: url, data: $(body).find('#myform').serialize(),
                    success: function(json) {
                        if (json.code == 1) {
                            layer.close(index);
                        } else {
                            layer.close(loading);
                            dr_tips(json.code, json.msg);
                            return;
                        }
                        var url2 = json.msg;
                        layer.open({
                            type: 2,
                            title: "<?php echo dr_lang('批量退稿进度'); ?>",
                            shadeClose: true,
                            shade: 0,
                            area: [<?php echo \Phpcmf\Service::IS_PC_USER() ? '\'50%\', \'60%\'' : '"95%", "90%"' ?>],
                            btn: [lang['ok'], lang['esc']],
                                yes: function(index, layero){
                                layer.close(loading);
                                var body = layer.getChildFrame('body', index);
                                $(body).find('.form-group').removeClass('has-error');
                                // 延迟加载
                                layer.close(index);
                                setTimeout("window.location.reload(true)", 2000)
                                return false;
                            },
                            success: function(layero, index){
                                // 主要用于后台权限验证
                                layer.close(loading);

                                dr_iframe_error(layer, index, 1);
                            },
                            content: url2+'&is_ajax=1'
                        });
                    },
                    error: function(HttpRequest, ajaxOptions, thrownError) {
                        dr_ajax_alert_error(HttpRequest, ajaxOptions, thrownError, this);
                    }
                });
                return false;
            },
            success: function(layero, index){
                // 主要用于后台权限验证

                dr_iframe_error(layer, index, 1);
            },
            content: url+'&is_iframe=1'
        });
    }
</script>

<div class="note note-danger" <?php if (!isset($get['submit']) && !$is_show_search_bar) { ?>style="display: none"<?php } ?> id="table-search-tool">
    <div class="table-search-tool row">
        <form action="<?php echo SELF; ?>" id="mysearch"  method="get">
            <?php echo dr_form_search_hidden($p);  if ($is_category_show) { ?>
            <div class="col-md-12 col-sm-12">
                <label>
                    <?php echo $category_select; ?>
                </label>
            </div>
            <?php } ?>
            <div class="col-md-12 col-sm-12">
                <label>
                    <select name="field" class="form-control">
                        <option value="id"> Id </option>
                        <?php if (isset($field) && is_array($field) && $field) { $key_t=-1;$count_t=dr_count($field);foreach ($field as $t) { $key_t++; $is_first=$key_t==0 ? 1 : 0;$is_last=$count_t==$key_t+1 ? 1 : 0; if (dr_is_admin_search_field($t)) { ?>
                        <option value="<?php echo $t['fieldname']; ?>" <?php if ($param['field']==$t['fieldname']) { ?>selected<?php } ?>><?php echo dr_lang($t['name']); ?></option>
                        <?php }  } } ?>
                    </select>
                </label>
                <label><i class="fa fa-caret-right"></i></label>
                <label><input type="text" id="search_kw" class="form-control" value="<?php echo $param['keyword']; ?>" name="keyword" /></label>
            </div>

            <div class="col-md-12 col-sm-12">
                <label>
                    <div class="input-group input-medium date-picker input-daterange" data-date="" data-date-format="yyyy-mm-dd">
                        <input type="text" class="form-control" value="<?php echo $param['date_form']; ?>" name="date_form">
                        <span class="input-group-addon"> <?php echo dr_lang('到'); ?> </span>
                        <input type="text" class="form-control" value="<?php echo $param['date_to']; ?>" name="date_to">
                    </div>
                </label>
            </div>
            <div class="col-md-12 col-sm-12">
                <label><button id="table-search-tool-submit" type="button" class="btn blue btn-sm " name="submit" > <i class="fa fa-search"></i> <?php echo dr_lang('搜索'); ?></button></label>
                <label><button id="table-search-tool-reset" type="reset" class="btn red btn-sm " name="reset" > <i class="fa fa-refresh"></i> <?php echo dr_lang('重置'); ?></button></label>
            </div>
        </form>
    </div>
</div>
<div class="right-card-box">
    <div id="toolbar" class="toolbar">
        <?php echo $mytable['foot_tpl']; ?>

    </div>
    <?php if ($fn_include = $this->_include("mytable.html")) include($fn_include); ?>
</div>


<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>