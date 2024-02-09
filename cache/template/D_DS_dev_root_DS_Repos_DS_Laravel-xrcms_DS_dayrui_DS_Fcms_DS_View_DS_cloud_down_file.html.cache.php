<?php if ($fn_include = $this->_include("header.html")) include($fn_include); ?>


<div class="text-center" id="dr_check_button">
    <button type="button" id="dr_check_button_ing" disabled="disabled" class="btn blue"> <img width="15" src="<?php echo THEME_PATH; ?>assets/images/loading-2.gif">  准备中 </button>
</div>

<div id="dr_check_result" class="margin-top-30" style="display: none">

</div>

<div id="dr_check_div"  class="well margin-top-30" style="display: none">
    <div class="scroller" style="height:250px" data-rail-visible="1"  id="dr_check_html">

    </div>
</div>

<div id="dr_check_ing" style="display: none">
    <div class="progress progress-striped">
        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%">

        </div>
    </div>
</div>
<input id="dr_check_status" type="hidden" value="0">
<script>
    <?php if (IS_OEM_CMS) { ?>
    var is_oem_cms_down = 1;
    <?php } ?>
    $(function () {
        dr_checking();
    });
    function dr_checking() {
        $('#dr_check_html').html("");
        $('#dr_check_result').html($('#dr_check_ing').html());
        $('#dr_check_div').show();
        $('#dr_check_result').show();
        $('#dr_check_reing').remove();
        $('#dr_check_button_ing').addClass('blue');
        $('#dr_check_button_ing').removeClass('red');
        $('#dr_check_button_ing').html('<img width="15" src="<?php echo THEME_PATH; ?>assets/images/loading-2.gif"> 准备中');
        $('#dr_check_html').append('<p class=""><label class="rleft">正在验证服务端授权信息</label></p>');
        $.ajax({
            type: "GET",
            dataType: "json",
            url: "<?php echo dr_url('cloud/update_file'); ?>&id=<?php echo $app_id; ?>&ls=<?php echo $ls; ?>",
            success: function (json) {
                if (json.code == 0) {
                    $('#dr_check_button_ing').html('<i class="fa fa-times-circle"></i> 下载失败');
                    $('#dr_check_button_ing').addClass('red');
                    $('#dr_check_button_ing').removeClass('blue');
                    $('#dr_check_button').append('<button type="button" id="dr_check_reing" onclick="dr_checking()" class="btn green"> <i class="fa fa-refresh"></i> 重新下载 </button>');
                    $('#dr_check_html').append('<p class="p_error"><label class="rleft">'+json.msg+'</label></p>');
                } else {
                    dr_do_cron();
                }
            },
            error: function(HttpRequest, ajaxOptions, thrownError) {
                dr_ajax_alert_error(HttpRequest, this, thrownError);
            }
        });
    }
    // 执行下载
    function dr_do_cron() {
        // 开始下载他
        $('#dr_check_html').append('<p class=""><label class="rleft">正在开始下载文件</label></p>');
        $('#dr_check_button_ing').html('<img width="15" src="<?php echo THEME_PATH; ?>assets/images/loading-2.gif"> 下载中');
        $.ajax({
            type: "GET",
            dataType: "json",
            url: "<?php echo dr_url('cloud/update_file_down'); ?>&id=<?php echo $app_id; ?>&ls=<?php echo $ls; ?>",
            success: function (json) {
                if (json.code == 0) {
                    $('#dr_check_button_ing').html('<i class="fa fa-times-circle"></i> 下载失败');
                    $('#dr_check_button_ing').addClass('red');
                    $('#dr_check_button_ing').removeClass('blue');
                    $('#dr_check_button').append('<button type="button" id="dr_check_reing" onclick="dr_checking()" class="btn green"> <i class="fa fa-refresh"></i> 重新下载 </button>');
                    $('#dr_check_html').append('<p class="p_error"><label class="rleft">'+json.msg+'</label></p>');
                    clearInterval(interval_id);
                } else {

                }
            },
            error: function(HttpRequest, ajaxOptions, thrownError) {
                dr_ajax_alert_error(HttpRequest, this, thrownError);
            }
        });
        // 检测下载结果
        var is_ok_lock = 0;
        var is_jd = 0;
        var is_count = 0;
        var interval_id = window.setInterval(function() {
            $.ajax({
                type: "GET",
                dataType: "json",
                url: "<?php echo dr_url('cloud/update_file_check'); ?>&id=<?php echo $app_id; ?>&is_count="+is_count+"&is_jd="+is_jd,
                success: function (json) {
                    is_count++;
                    document.getElementById('dr_check_html').scrollTop = document.getElementById('dr_check_html').scrollHeight;
                    is_jd = json.code;
                    if (json.code == 0) {
                        $('#dr_check_button_ing').html('<i class="fa fa-times-circle"></i> 下载失败');
                        $('#dr_check_button_ing').addClass('red');
                        $('#dr_check_button_ing').removeClass('blue');
                        $('#dr_check_button').html('<button type="button" id="dr_check_reing" onclick="dr_checking()" class="btn green"> <i class="fa fa-refresh"></i> 重新下载 </button>');
                        clearInterval(interval_id);
                    } else {
                        $('#dr_check_result .progress-bar-success').attr('style', 'width:'+json.code+'%');
                        if (json.code >= 100) {
                            // 下在完成
                            clearInterval(interval_id);
                            //dr_checking_install();
                            if (is_ok_lock == 0) {
                                $('#dr_check_html').append('<p class=""><label class="rleft">文件下载完成</label></p>');
                                $('#dr_check_button_ing').html('<i class="fa fa-check-circle"></i> 下载完成');
                                $('#dr_check_button').append('<button type="button" id="dr_check_install" onclick="dr_install()" class="btn blue"> <i class="fa fa-cog"></i> 导入程序 </button>');
                            }
                            is_ok_lock = 1;
                        } else {
                            $('#dr_check_html').append(json.msg);
                            $('#dr_check_button_ing').html('<img width="15" src="<?php echo THEME_PATH; ?>assets/images/loading-2.gif">  下载进度 '+json.code+'%');
                        }
                    }
                },
                error: function(HttpRequest, ajaxOptions, thrownError) {
                    dr_ajax_alert_error(HttpRequest, this, thrownError);
                }
            });
        }, 1000);
    }
    function dr_install(cf=0) {
        $('#dr_check_html').html("");
        $('#dr_check_result').html($('#dr_check_ing').html());
        $('#dr_check_div').show();
        $('#dr_check_result').show();
        $('#dr_check_install').remove();
        $('#dr_check_button_ing').addClass('blue');
        $('#dr_check_button_ing').removeClass('red');
        $('#dr_check_button_ing').html('<img width="15" src="<?php echo THEME_PATH; ?>assets/images/loading-2.gif">  文件复制中');
        $.ajax({
            type: "GET",
            dataType: "json",
            url: "<?php echo dr_url('cloud/install_app'); ?>&id=<?php echo $app_id; ?>&cf="+cf,
            success: function (json) {
                if (json.code == 1) {
                    $('#dr_check_button_ing').html('<i class="fa fa-check-circle"></i> 下载完成');
                    $('#dr_check_html').html('<p>'+json.msg+'</p>');
                } else {
                    $('#dr_check_html').html('<p class="p_error">'+json.msg+'</p>');
                    $('#dr_check_button_ing').html('<i class="fa fa-times-circle"></i> 复制失败');
                    $('#dr_check_button_ing').addClass('red');
                    $('#dr_check_button_ing').removeClass('blue');
                    $('#dr_check_button').append('<button type="button" id="dr_check_install" onclick="dr_install()" class="btn blue"> <i class="fa fa-cog"></i> 导入程序 </button>');
                }
            },
            error: function(HttpRequest, ajaxOptions, thrownError) {
                dr_ajax_alert_error(HttpRequest, this, thrownError);
            }
        });
    }
</script>


<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>