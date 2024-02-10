<?php if ($fn_include = $this->_include("head.html")) include($fn_include);  foreach ($my_menu as $tid => $topm) {
$_left = 0; // 是否第一个分组菜单，0表示第一个
$_link = 0; // 是否第一个链接菜单，0表示第一个
$left_string = '';
$mleft_string = [];
!$first && $first = $tid;
foreach ($topm['left'] as $if => $left) {
// 链接菜单开始
$link_string = '';
$mlink_string = '';
foreach ($left['link'] as $i => $link) {
$url = $link['url'];
if (!$_link) {
// 第一个链接菜单时 指定class
$class = 'nav-item active open';
$topm['url'] = $link['url'];
$topm['link_id'] = $link['id'];
$topm['left_id'] = $left['id'];
} else {
$class = 'nav-item';
}
$_link = 1; // 标识以后的菜单就不是第一个了
$link_string.= '<li id="dr_menu_link_'.$link['id'].'" class="'.$class.'"><a href="javascript:Mlink('.$tid.', '.$left['id'].', '.$link['id'].', \''.$url.'\');" class="tooltips" data-container="body" data-placement="right" data-original-title="'.dr_lang($link['name']).'" title="'.dr_lang($link['name']).'"><i class="iconm '.$link['icon'].'"></i> <span class="title" title="'.dr_lang($topm['name']).' - '.dr_lang($left['name']).' - '.dr_lang($link['name']).'">'.dr_lang($link['name']).'</span></a></li>';
$mlink_string.= '<li id="dr_menu_m_link_'.$link['id'].'" class="'.$class.'"><a href="javascript:Mlink('.$tid.', '.$left['id'].', '.$link['id'].', \''.$url.'\');"><i class="iconm '.$link['icon'].'"></i> <span class="title" title="'.dr_lang($topm['name']).' - '.dr_lang($left['name']).' - '.dr_lang($link['name']).'">'.dr_lang($link['name']).'</span></a></li>';
}
$left_string.= '
<li id="dr_menu_left_'.$left['id'].'" class="dr_menu_'.$tid.' dr_menu_item nav-item '.($_left ? '' : 'active open').' " style="'.($first==$tid ? '' : 'display:none').'">
    <a href="javascript:;" class="nav-link nav-toggle tooltips" data-container="body" data-placement="right" data-original-title="'.dr_lang($left['name']).'">
        <i class="'.$left['icon'].'"></i>
        <span class="title">'.dr_strcut(dr_lang($left['name']), 5).'</span>
        <span class="selected" style="'.($_left ? 'display:none' : '').'"></span>
        <span class="arrow '.($_left ? '' : ' open').'"></span>
    </a>
    <ul class="sub-menu">'.$link_string.'</ul>
</li>';
$mleft_string[] = $mlink_string;
$_left = 1; // 标识以后的菜单就不是第一个了
}
$string.= $left_string;
$mstring.= '<li class="dropdown dropdown-extended dropdown-tasks fc-mb-sum-menu" id="dr_m_top_'.$tid.'" style=" '.($first == $tid ? '' : 'display:none').'">
    <a class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
        <i class="fa fa-angle-down"></i>
    </a>
    <ul class="dropdown-menu" role="menu">
        '.implode('<li class="divider"> </li>', $mleft_string).'
    </ul>
</li>';
$menu_top[$tid] = $topm;
}
// 自定义后台菜单显示
if (function_exists('dr_my_admin_menu')) {
list($string, $mstring, $menu_top, $first) = dr_my_admin_menu($my_menu, $string, $mstring, $menu_top, $first);
}
$top = $menu_top;
?>
<body scroll="no" style="overflow: hidden;" class="page-sidebar-closed-hide-logo page-admin-all page-content-white page-header-fixed page-sidebar-fixed ">
<style>.page-content {padding:0px !important;} </style>
<div class="page-header navbar navbar-fixed-top">
    <div class="page-header-inner ">
        <div class="page-logo">
            <a href="<?php echo SITE_URL; ?>" target="_blank"><img src="<?php echo THEME_PATH; ?>assets/logo.png" alt="logo" class="logo-default" /> </a>
        </div>
        <a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse">
            <span></span>
        </a>
        <div class="top-menu my-top-left pull-left">
            <ul class="nav navbar-nav pull-left fc-all-menu-top ">
                <?php if (isset($top) && is_array($top) && $top) { $key_t=-1;$count_t=dr_count($top);foreach ($top as $t) { $key_t++; $is_first=$key_t==0 ? 1 : 0;$is_last=$count_t==$key_t+1 ? 1 : 0;?>
                <li id="dr_menu_top_<?php echo $t['id']; ?>" class="dropdown <?php if ($t['id']==$first) { ?>open<?php } ?>">
                    <a class="dropdown-toggle popovers" href="javascript:Mlink('<?php echo $t['id']; ?>', '<?php echo $t['left_id']; ?>', '<?php echo $t['link_id']; ?>', '<?php echo $t['url']; ?>');">
                        <div class="menu-top-icon"><i class="<?php echo $t['icon']; ?>"></i></div>
                        <div class="menu-top-name"><i class="top-txt-menu"><?php echo dr_lang($t['name']); ?></i></div>
                    </a>
                </li>
                <?php } } ?>
            </ul>
        </div>
        <div class="top-menu my-top-right">
            <ul class="nav navbar-nav pull-right">
                <?php if ($is_mobile) { ?>
                <li class="dropdown fc-mini-menu-top">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                        <i class="fa fa-bars"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-default fc_mini_menu_top">
                        <?php if (isset($top) && is_array($top) && $top) { $key_t=-1;$count_t=dr_count($top);foreach ($top as $t) { $key_t++; $is_first=$key_t==0 ? 1 : 0;$is_last=$count_t==$key_t+1 ? 1 : 0;?>
                        <li>
                            <a id="dr_mini_menu_top_<?php echo $t['id']; ?>" class="dr_mini_menu_top <?php if ($t['id']==$first) { ?>open<?php } ?>" href="javascript:Mlink('<?php echo $t['id']; ?>', '<?php echo $t['left_id']; ?>', '<?php echo $t['link_id']; ?>', '<?php echo $t['url']; ?>');">
                                <i class="<?php echo $t['icon']; ?>"></i> <?php echo dr_lang($t['name']); ?>
                            </a>
                        </li>
                        <?php } } ?>
                    </ul>
                </li>
                <?php echo $mstring;  }  if (count($ci->site_info) > 1 && dr_is_app('sites')) { ?>
                <li class="dropdown dropdown-extended dropdown-tasks">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                        <div class="menu-top-icon"><i class="fa fa-share-alt"></i>
                        </div>
                        <div class="top-txt-menu"><?php echo dr_lang('多站'); ?></div>
                    </a>
                    <ul class="dropdown-menu extended tasks">
                        <li>
                            <ul class="dropdown-menu-list scroller" style="height:400px;overflow: scroll;" data-handle-color="#637283">
                                <?php if (isset($ci->site_info) && is_array($ci->site_info) && $ci->site_info) { $key_t=-1;$count_t=dr_count($ci->site_info);foreach ($ci->site_info as $i=>$t) { $key_t++; $is_first=$key_t==0 ? 1 : 0;$is_last=$count_t==$key_t+1 ? 1 : 0;  if (\Phpcmf\Service::M('auth')->_check_site($i)) { ?>
                                <li>
                                    <a href="javascript:;" onclick="dr_select_site('<?php echo $i; ?>')" title="<?php echo $t['SITE_NAME']; ?>" <?php if (SITE_ID == $i) { ?>style="color:red"<?php } ?>>
                                        <p style="margin: 0"><?php echo dr_strcut($t['SITE_NAME'], 30); ?></p>
                                        <p style="margin: 0;font-size: 10px;margin-top: -4px;"><?php echo trim(str_replace(['http://', 'https://'], '', $t['SITE_URL']), '/'); ?></p>
                                    </a>
                                </li>
                                <?php }  } }  if ($ci->_is_admin_auth('sites/home/index')) { ?>
                                <li class="external text-center">
                                    <a href="javascript:dr_go_url('<?php echo dr_url('sites/home/index'); ?>');"><?php echo dr_lang('站点管理'); ?></a>
                                </li>
                                <?php } ?>
                            </ul>
                        </li>
                    </ul>
                </li>
                <?php }  $notice = \Phpcmf\Service::M('auth')->admin_notice(10, true); if ($notice) { ?>
                <li class="dropdown dropdown-extended dropdown-notification">
                    <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                        <div class="menu-top-icon"><i class="fa fa-bell"></i>
                        </div>
                        <div class="top-txt-menu"><?php echo dr_lang('提醒'); ?></div>
                    </a>
                    <ul class="dropdown-menu extended tasks">
                        <li>
                            <ul class="dropdown-menu-list scroller" style="height:400px;overflow: scroll;" data-handle-color="#637283">
                                <?php if (isset($notice) && is_array($notice) && $notice) { $key_t=-1;$count_t=dr_count($notice);foreach ($notice as $t) { $key_t++; $is_first=$key_t==0 ? 1 : 0;$is_last=$count_t==$key_t+1 ? 1 : 0;?>
                                <li>
                                    <a  onclick="dr_hide_left_tab()" href="javascript:dr_go_url('<?php echo dr_url('api/notice', array('id' => $t['id'])); ?>');">
                                        <span class="time"><?php echo dr_fdate($t['inputtime']); ?></span>
                                        <span class="details"> <?php echo $t['msg']; ?> </span>
                                    </a>
                                </li>
                                <?php } }  if ($ci->_is_admin_auth('notice/index')) { ?>
                                <li class="external text-center">
                                    <a href="javascript:dr_go_url('<?php echo dr_url('notice/index'); ?>');"><?php echo dr_lang('更多提醒'); ?></a>
                                </li>
                                <?php } ?>
                            </ul>
                        </li>
                    </ul>
                </li>
                <?php }  if ($is_mobile) { ?>
                <li class="dropdown">
                    <a href="javascript:;"  class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                        <div class="menu-top-icon"> <i class="fa fa-wrench"></i> </div>
                        <div class="menu-top-icon"><i class="top-txt-menu"><?php echo dr_lang('账号'); ?></i></div>
                    </a>
                    <?php } else { ?>
                <li class="dropdown dropdown-user">
                    <a style="margin-right: -10px;" href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                        <img alt="<?php echo $admin['username']; ?>" class="img-circle" src="<?php echo dr_avatar($admin['uid']); ?>" />
                    </a>
                    <?php } ?>
                    <ul class="dropdown-menu dropdown-menu-default">
                        <?php if (IS_USE_MEMBER) { ?><li><a href="<?php echo dr_url('api/alogin', ['id'=>$admin['uid']]); ?>" target="_blank"><i class="fa fa-user"></i> <?php echo dr_lang('用户中心'); ?> </a></li><?php } ?>
                        <li><a href="javascript:dr_go_url('<?php echo dr_url('api/my'); ?>');"><i class="fa fa-edit"></i> <?php echo dr_lang('修改资料'); ?> </a></li>
                        <li><a href="<?php echo dr_url('api/admin_min'); ?>"><i class="fa fa-retweet"></i> <?php echo dr_lang('简化模式'); ?></a></li>
                        <li><a href="javascript:;" onClick="dr_logout('<?php echo dr_url('login/out'); ?>');"><i class="fa fa-user-times"></i> <?php echo dr_lang('退出系统'); ?></a></li>
                        <li class="divider"> </li>
                        <?php if ($ci->_is_admin_auth('cache/index')) { ?>
                        <li><a href="javascript:dr_go_url('<?php echo dr_url('cache/index'); ?>');"><i class="fa fa-cogs"></i> <?php echo dr_lang('系统更新'); ?></a></li>
                        <?php }  if ($ci->_is_admin_auth('check/index')) { ?>
                        <li><a href="javascript:dr_go_url('<?php echo dr_url('check/index'); ?>');"><i class="fa fa-wrench"></i> <?php echo dr_lang('系统体检'); ?></a></li>
                        <?php } ?>
                        <li><a href="javascript:dr_update_cache_all();"><i class="fa fa-refresh"></i> <?php echo dr_lang('更新缓存'); ?></a></li>
                        <li><a href="javascript:dr_update_cache_data(1);"><i class="fa fa-trash"></i> <?php echo dr_lang('更新数据'); ?></a></li>
                        <?php if ($admin['adminid']==1) { ?>
                        <li class="divider"> </li>
                        <li><a href="javascript:dr_go_url('<?php echo dr_url('error/index'); ?>');"><i class="fa fa-shield"></i> <?php echo dr_lang('系统日志'); ?></a></li>
                        <?php }  if ($is_search_help) { ?>
                        <li><a href="https://www.xunruicms.com/doc/" target="_blank"><i class="fa fa-book"></i> <?php echo dr_lang('帮助手册'); ?></a></li>
                        <?php } ?>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</div>

<div class="clearfix"> </div>

<div class="page-container">
    <div class="page-sidebar-wrapper">

        <div class="page-sidebar navbar-collapse collapse">
            <ul class="page-sidebar-menu  page-header-fixed  page-sidebar-menu-light" data-keep-expanded="false" data-auto-scroll="true" data-slide-speed="200" style="padding-top: 20px">
                <li class="sidebar-toggler-wrapper hide">
                    <div class="sidebar-toggler">
                        <span></span>
                    </div>
                </li>

                <li class="sidebar-search-wrapper hidden-xs hidden-sm margin-bottom-15">

                </li>
                <?php echo $string; ?>
            </ul>
        </div>
    </div>
    <div class="page-content-wrapper">
        <div class="page-content index-content">
            <?php if (!SYS_NOT_ADMIN_CACHE && !$is_mobile) { ?>
            <ul class="page-toolbar fc-mb-left-menu" id="dr_go_url">
            </ul>
            <?php } ?>
            <div id="myiframe" cid="right_page">
                <iframe class="myiframe active" name="right" id="right_page" src="<?php echo $main_url; ?>" url="<?php echo $main_url; ?>" style="border:none; margin-bottom:0px;" width="100%" height="auto" allowtransparency="true"></iframe>
            </div>
        </div>
    </div>


</div>
<script>
    // 关闭栏
    function dr_hide_left_tab() {
        $(".page-quick-sidebar-toggler").click();
    }
    if (self != top) {
        top.location.href = admin_file;
    }
    var url = '<?php echo dr_url_prefix('/'); ?>';
    var p = url.split('/');
    var ptl = document.location.protocol;
    if ((p[0] == 'http:' || p[0] == 'https:') && ptl != p[0]) {
        alert('当前访问是'+ptl.replace(':', '')+'模式，本项目设置的是'+p[0].replace(':', '')+'模式，请使用'+p[0].replace(':', '')+'模式访问，会导致部分功能无法正常使用');
    }
</script>
</body>
</html>