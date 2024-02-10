<?php if ($fn_include = $this->_include("header.html")) include($fn_include); ?>

<form class="form-horizontal" method="post" role="form" id="myform" style="margin-top:-20px;">
    <?php echo $form; ?>
    <div class="portlet light " style="padding: 0">

        <div class="portlet-title tabbable-line">
            <ul class="nav nav-tabs" style="float:left;">
                <?php if ($unused) { ?>
                <li id="tab_nav_0" class="<?php if ($pp==0) { ?>active<?php } ?>">
                    <a href="<?php echo $tab_url; ?>&pp=0"><i class="fa fa-folder"></i> <?php echo dr_lang('未归档'); ?> </a>
                </li>
                <?php } ?>
                <li class="dev <?php if ($pp==1) { ?>active<?php } ?>">
                    <a href="<?php echo $tab_url; ?>&pp=1"><i class="bi bi-folder-check"></i> <?php echo dr_lang('已归档'); ?> </a>
                </li>
                <?php if ($member['is_admin']) { ?>
                <li class="dev <?php if ($pp==2) { ?>active<?php } ?>">
                    <a href="<?php echo $tab_url; ?>&pp=2"><i class="fa fa-folder-open"></i> <?php echo dr_lang('浏览'); ?> </a>
                </li>
                <?php }  if ($field) { ?>
                <link href="<?php echo ROOT_THEME_PATH; ?>assets/global/plugins/jquery-fileupload/css/jquery.fileupload.css" rel="stylesheet" type="text/css" />
                <script src="<?php echo ROOT_THEME_PATH; ?>assets/global/plugins/jquery-fileupload/js/jquery.fileupload.js" type="text/javascript"></script>
                <li class="dev" id="fileupload">
                    <a href="JavaScript:;" title="<?php echo $field['tips']; ?>" class="fileinput-button tooltips" data-container="body" data-placement="bottom" data-original-title="<?php echo $field['tips']; ?>"> <i class="fa fa-upload"></i> <?php echo dr_lang('上传'); ?><input type="file" multiple name="file_data"> </a>
                </li>
                <li class="dev">
                    <input id="dr_file_count" value="0" type="hidden">
                    <a class="fileupload-progress"></a>
                </li>
                <script type="text/javascript">
                    $(function() {
                        $("#fileupload").fileupload({
                            disableImageResize: false,
                            autoUpload: true,
                            maxFileSize: "<?php echo $field['param']['size']; ?>",
                            url: "<?php echo $field['url']; ?>",
                            dataType: "json",
                            acceptFileTypes: "<?php echo $field['param']['exts']; ?>",
                            maxChunkSize: '<?php echo $field['param']['chunk']; ?>',
                            progressall: function (e, data) {
                                // 上传进度条 all
                                var progress = parseInt(data.loaded / data.total * 100, 10);
                                $(".fileupload-progress").show();
                                $(".fileupload-progress").html(""+progress+"%");
                            },
                            add: function (e, data) {
                                $(".fileupload-progress").hide();
                                data.submit();

                            },
                            done: function (e, data) {
                                var count = parseInt($('#dr_file_count').val()) + 1;
                                $('#dr_file_count').val(count);
                                $(".fileupload-progress").hide();
                                if (data.result.code > 0) {
                                    dr_tips(data.result.code, data.result.msg);
                                    if (count == data.originalFiles.length) {
                                        setTimeout("window.location.href = '<?php echo $field['back']; ?>'", 2000);
                                    }
                                    //setTimeout("window.location.href = '<?php echo $field['back']; ?>'", 2000);
                                } else {
                                    dr_tips(data.result.code, data.result.msg, -1);
                                }
                            },
                            fail: function (e, data) {
                                //console.log(data.errorThrown);
                                dr_tips(0, "系统故障："+data.errorThrown, -1);
                                layer.closeAll('tips');
                                $(".fileupload-progress").hide();

                            },
                        });
                    });
                </script>
                <?php } ?>
            </ul>
        </div>

        <div class="portlet-body table-finecms-upload">

            <?php if ($unused && $pp==0) { ?>
            <div role="presentation" class="table table-fc-upload table-striped clearfix">
                <div class="files row">
                    <?php $tid=0; ?>
                    <?php $list_return = $this->list_tag("action=table cache=0 table=attachment_unused where=$list[unused] IN_fileext=$fileext order=id_desc pagefile=admin page=1 pagesize=$psize urlrule=$urlrule"); if ($list_return && is_array($list_return)) extract($list_return, EXTR_OVERWRITE); $count=dr_count($return); if (is_array($return) && $return) { $key=-1; foreach ($return as $t) { $key++; $is_first=$key==0 ? 1 : 0;$is_last=$count==$key+1 ? 1 : 0; ?>
                    <?php if ($fn_include = $this->_include("api_upload_data.html")) include($fn_include); ?>
                    <?php } } ?>
                </div>
            </div>
            <?php } else if ($pp==2 && $member['is_admin']) { ?>
            <div role="presentation" class="table table-fc-filelist table-striped clearfix">

            </div>
            <script>
                function dr_filelist(dir) {
                    $.get('<?php echo $listurl; ?>&dir='+dir, function (text){
                        $('.table-fc-filelist').html(text);
                    }, 'text');
                }
                function dr_file_select(file) {

                }
                dr_filelist('');
            </script>
            <?php } else { ?>
            <div class="row">
                <div class="col-md-12 text-center margin-bottom-20">
                    <label><select  id="dr_field_name"class="form-control">
                        <?php if (isset($sfield) && is_array($sfield) && $sfield) { $key_t=-1;$count_t=dr_count($sfield);foreach ($sfield as $i=>$t) { $key_t++; $is_first=$key_t==0 ? 1 : 0;$is_last=$count_t==$key_t+1 ? 1 : 0; ?>
                        <option value="<?php echo $i; ?>" <?php if ($param['name'] == $i) { ?> selected<?php } ?>><?php echo $t; ?></option>
                        <?php } } ?>
                    </select></label>
                    <label>
                        <input type="text" class="form-control" value="<?php echo $param['value']; ?>" id="dr_field_value">
                    </label>
                    <label><a class="btn green btn-sm onloading" href="javascript:;" onclick="dr_fsearch()"> <i class="fa fa-search"></i> <?php echo dr_lang('搜索'); ?></a></label>
                </div>
            </div>
            <div role="presentation" class="table table-fc-upload table-striped clearfix">

                <div class="files row">
                    <?php $tid=1; ?>
                    <?php $list_return = $this->list_tag("action=table cache=0 table=attachment_data where=$list[used] IN_fileext=$fileext order=id_desc pagefile=admin page=1 pagesize=$psize urlrule=$urlrule"); if ($list_return && is_array($list_return)) extract($list_return, EXTR_OVERWRITE); $count=dr_count($return); if (is_array($return) && $return) { $key=-1; foreach ($return as $t) { $key++; $is_first=$key==0 ? 1 : 0;$is_last=$count==$key+1 ? 1 : 0; ?>
                    <?php if ($fn_include = $this->_include("api_upload_data.html")) include($fn_include); ?>
                    <?php } } ?>
                </div>
            </div>

            <?php } ?>
            <div class="row">
                <div class="col-md-12 text-center margin-bottom-20">
                    <?php echo $pages; ?>
                </div>
            </div>

        </div>
    </div>


    <input type="hidden" name="is_ajax" value="1">
    <input type="hidden" name="is_page" id="dr_page" value="<?php echo $pp; ?>">
</form>

<script>
    function dr_fsearch() {
        var url = '<?php echo $search_url; ?>&name='+$('#dr_field_name').val()+'&value='+$('#dr_field_value').val();
        window.location.href = url;
    }
    function dr_file_delete_tips(obj, id) {
        layer.confirm(
            "<?php echo dr_lang('确定要删除本文件吗？'); ?>",
            {
                icon: 3,
                shade: 0,
                title: lang['ts'],
                btn: [lang['ok'], lang['esc']]
            }, function(index){
                layer.close(index);
                dr_file_delete(obj, id);
                setTimeout("window.location.reload(true)", 2000);
            });
    }
    function dr_select_ids(tid, id) {
        var obj = $('.div_ids'+tid+'_'+id);
        var select = $('.select_ids'+tid+'_'+id);
        if (obj.hasClass('on-view')) {
            obj.removeClass('on-view');
            select.val('');
        } else {
            obj.addClass('on-view');
            select.val(id);
        }
    }
   $(function (){
        $(".rs-load").each(function (){
            $(this).attr("src", $(this).attr("rs-src"))
        });
    });
</script>

<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>