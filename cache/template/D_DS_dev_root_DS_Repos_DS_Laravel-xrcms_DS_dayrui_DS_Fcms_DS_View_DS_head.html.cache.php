<!DOCTYPE html>
<html lang="<?php echo SITE_LANGUAGE; ?>">
<head>
    <meta charset="utf-8" />
    <title><?php if ($meta_title) {  echo $meta_title;  } else {  echo SITE_NAME; ?> - <?php echo dr_lang('后台管理平台');  } ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1" name="viewport" />
    <script src="<?php echo $THEME_PATH; ?>assets/global/plugins/jquery.min.js?v=<?php echo CMF_UPDATE_TIME; ?>" type="text/javascript"></script>
    <link href="<?php echo $THEME_PATH; ?>assets/icon/css/icon.css?v=<?php echo CMF_UPDATE_TIME; ?>" rel="stylesheet" type="text/css" />
    <link href="<?php echo $THEME_PATH; ?>assets/global/css/admin<?php if (!IS_XRDEV) { ?>.min<?php } ?>.css?v=<?php echo CMF_UPDATE_TIME; ?>" rel="stylesheet" type="text/css" />
    <?php if ($is_min) { ?>
    <link href="<?php echo $THEME_PATH; ?>assets/global/css/min<?php if (!IS_XRDEV) { ?>.min<?php } ?>.css?v=<?php echo CMF_UPDATE_TIME; ?>" rel="stylesheet" type="text/css" />
    <?php }  if ($admin['setting']['font_size']) { ?>
    <link href="<?php echo $THEME_PATH; ?>assets/global/css/font-max.css?v=<?php echo CMF_UPDATE_TIME; ?>" rel="stylesheet" type="text/css" />
    <?php } ?>
    <link href="<?php echo defined('MYCSS_FILE') ? MYCSS_FILE : $THEME_PATH.'assets/global/css/my.css'; ?>?v=<?php echo CMF_UPDATE_TIME; ?>" rel="stylesheet" type="text/css" />
    <script type="text/javascript">
        var admin_file = '<?php echo SELF; ?>';
        var is_cms_dev = '<?php echo CI_DEBUG ? 1 : ''; ?>';
        var is_min = '<?php echo $is_min; ?>';
        var is_oemcms = '<?php echo IS_OEM_CMS; ?>';
        var is_mobile_cms = '<?php echo $is_mobile; ?>';
        var is_admin = '<?php if (dr_in_array(1, $admin['roleid'])) { ?>1<?php } else { ?>2<?php } ?>';
    </script>
    <script src="<?php echo $LANG_PATH; ?>lang.js?v=<?php echo CMF_UPDATE_TIME; ?>" type="text/javascript"></script>
    <script src="<?php echo $THEME_PATH; ?>assets/global/plugins/bootstrap/js/bootstrap.min.js?v=<?php echo CMF_UPDATE_TIME; ?>" type="text/javascript"></script>
    <script src="<?php echo $THEME_PATH; ?>assets/global/scripts/app<?php if (!IS_XRDEV) { ?>.min<?php } ?>.js?v=<?php echo CMF_UPDATE_TIME; ?>" type="text/javascript"></script>
    <script src="<?php echo $THEME_PATH; ?>assets/js/cms.js?v=<?php echo CMF_UPDATE_TIME; ?>" type="text/javascript"></script>
    <?php if ($is_index) { ?>
    <script src="<?php echo $THEME_PATH; ?>assets/global/plugins/jquery.md5.js?v=<?php echo CMF_UPDATE_TIME; ?>" type="text/javascript"></script>
    <?php } else { ?>
    <script src="<?php echo $THEME_PATH; ?>assets/js/my.js?v=<?php echo CMF_UPDATE_TIME; ?>" type="text/javascript"></script>
    <?php } ?>
    <script type="text/javascript">
        function dr_update_cache_all() {
            <?php if ($is_min && $is_mobile) { ?>
                $('.page-header .responsive-toggler').click();
                $(".nav-item").removeClass("active open");
            <?php } ?>
            var index = layer.load(2, {
                shade: [0.3,'#fff'], //0.1透明度的白色背景
                time: 10000
            });
            $.ajax({type: "GET",dataType:"json", url: admin_file+"?c=api&m=cache_update",
                success: function(json) {
                    layer.close(index);
                    dr_tips(1, "<?php echo dr_lang('全站缓存更新完成'); ?>");
                },
                error: function(HttpRequest, ajaxOptions, thrownError) {
                    layer.closeAll('loading');
                    dr_tips(0, "<?php echo dr_lang('更新失败，请检查错误日志'); ?>");
                }
            });
        }
        function dr_update_cache_data(all = 0) {
            <?php if ($is_min && $is_mobile) { ?>
            $('.page-header .responsive-toggler').click();
            $(".nav-item").removeClass("active open");
            <?php } ?>
            var index = layer.load(2, {
                shade: [0.3,'#fff'], //0.1透明度的白色背景
                time: 10000
            });
            $.ajax({type: "GET",dataType:"json", url: admin_file+"?c=api&m=cache_clear&all="+all,
                success: function(json) {
                    layer.close(index);
                    dr_tips(1, "<?php echo dr_lang('前端数据缓存更新完成'); ?>");
                },
                error: function(HttpRequest, ajaxOptions, thrownError) {
                    layer.closeAll('loading');
                    dr_tips(0, "<?php echo dr_lang('更新失败，请检查错误日志'); ?>");
                }
            });
        }
        function show_category_field(catid) {
            <?php if ($category_field_url) { ?>
            window.location.href = '<?php echo $category_field_url; ?>&catid='+catid;
            <?php } ?>
        }
        <?php if ($is_index) { ?>
            // 退出
            function dr_logout(url) {
                var r=confirm(lang['logout']);
                if (r==true) {
                    $.ajax({
                        type: "GET",
                        dataType: "json",
                        url: url,
                        success: function(json) {
                            if (json.code == 1) {
                                setTimeout("window.location.href='<?php echo dr_url("login/index"); ?>'", 1000);
                            }
                            dr_tips(json.code, json.msg);
                        },
                        error: function(HttpRequest, ajaxOptions, thrownError) {
                            dr_ajax_alert_error(HttpRequest, this, thrownError);
                        }
                    });
                }
            }
            function dr_select_site(id) {
                var r=confirm('<?php echo dr_lang("你确定要切换到选中站点吗？"); ?>')
                if (r==true) {
                    window.location.href='<?php echo dr_url("sites/api/login_select"); ?>&id='+id
                }
            }
            function dr_go_url(url, name, nocache) {
                <?php if ($is_min && $is_mobile) { ?>
                 if (name == 'null') {
                     $('.page-header .responsive-toggler').click();
                     $(".nav-item").removeClass("active open");
                     name = '';
                 }
                <?php } ?>

                var cmd = $.md5(url);
                $('#myiframe iframe').hide();
                $('#myiframe').attr("cid", 'right_page_'+cmd);

                $('#dr_go_url').html('<li id="dr_tab_close"><i onclick="dr_tab_close(\''+cmd+'\')" class="fa fa-refresh"></i></li>');

                if ($('#right_page_'+cmd).length > 0) {
                    // 存在
                    $('#right_page_'+cmd).show();
                    var iurl = document.getElementById("right_page_"+cmd).contentWindow.location.href;
                    if (iurl.indexOf(url) == -1 && nocache != 'true') {
                        // 地址不符合，重置
                        $('#right_page_'+cmd).attr('src', url);
                    }
                } else {
                    var html = '<iframe class="myiframe" name="right" id="right_page_'+cmd+'" src="'+url+'" url="'+url+'" style="border:none; margin-bottom:0px;height:'+$('#right_page').height()+'px" width="100%" height="auto" allowtransparency="true"></iframe>';
                    $("#myiframe").append(html);
                }
            }
            function dr_tab_close(cmd) {
                var url =  $('#right_page_'+cmd).attr('src');
                document.getElementById("right_page_"+cmd).contentWindow.location.replace(url);
            }
            function Mlink(top, left, link, url) {

                $('.tooltip').hide();
                <?php if ($is_min) {  if ($is_mobile) { ?>$('.page-header .responsive-toggler').click();<?php } ?>
                $(".nav-item").removeClass("active open");
                <?php } else { ?>
                $('.dr_menu_item').hide();
                $('.dr_menu_'+top).show();
                $('.dr_menu_'+top+' .sub-menu').hide();
                $('#dr_m_top_'+top+' li').removeClass('active open');
                $('.dr_menu_'+top+' li').removeClass('active open');
                <?php } ?>

                $('#dr_menu_link_'+link).addClass('active open');
                $('#dr_menu_m_link_'+link).addClass('active open');

                // 顶级菜单选择
                $('.top-menu .navbar-nav li').removeClass('open');
                $('.dr_mini_menu_top').removeClass('open');
                $('#dr_menu_top_'+top).addClass('open');
                $('#dr_mini_menu_top_'+top).addClass('open');

                // 移动端选择
                $('.fc-mb-sum-menu').hide();
                $('#dr_m_top_'+top).show();

                // 分组菜单选择
                $('.dr_menu_'+top+'').removeClass('active open');
                $('.dr_menu_'+top+' .selected').hide();
                $('.dr_menu_'+top+' .arrow').removeClass('open');

                $('#dr_menu_left_'+left).addClass('active open');
                $('#dr_menu_left_'+left+' .selected').show();
                $('#dr_menu_left_'+left+' .arrow').addClass('open');
                $('#dr_menu_left_'+left+' .sub-menu').show();

                if (url) {
                    dr_go_url(url, $('#dr_menu_link_'+link).find('a').html());
                }
            }
            function wSize(){
                var str=getWindowSize();
                var strs= new Array(); //定义一数组
                strs=str.toString().split(","); //字符分割
                var heights = strs[0]-70,Body = $('body');
                $('#right_page').height(heights);
                $('#dr_go_url .dropdown-menu').attr('style', 'max-height:'+(heights-50)+'px;overflow-y: scroll;');
            }
            if(!Array.prototype.map)
                Array.prototype.map = function(fn,scope) {
                    var result = [],ri = 0;
                    for (var i = 0,n = this.length; i < n; i++){
                        if(i in this){
                            result[ri++]  = fn.call(scope ,this[i],i,this);
                        }
                    }
                    return result;
                };

            var getWindowSize = function(){
                return ["Height","Width"].map(function(name){
                    return window["inner"+name] ||
                        document.compatMode === "CSS1Compat" && document.documentElement[ "client" + name ] || document.body[ "client" + name ]
                });
            }

            $(function(){
                <?php if ($main_link) {  echo $main_link;  } ?>
                $('#dr_go_url').show();
                window.onresize=wSize;
                wSize();
                // 宽度小时
                if ($(document).width() < 900) {
                    $('.page-sidebar .sidebar-toggler').click();
                }
            });

        <?php } ?>
    </script>
</head>
