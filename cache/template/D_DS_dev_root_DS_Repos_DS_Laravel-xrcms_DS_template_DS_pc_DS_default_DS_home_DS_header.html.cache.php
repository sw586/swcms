<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title><?php echo $meta_title; ?></title>
    <meta content="<?php echo $meta_keywords; ?>" name="keywords" />
    <meta content="<?php echo $meta_description; ?>" name="description" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="<?php echo HOME_THEME_PATH; ?>pc/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo HOME_THEME_PATH; ?>pc/css/style.bundle.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo THEME_PATH; ?>assets/icon/css/icon.css" rel="stylesheet" type="text/css" />
    <!-- 系统懒人版js(所有自建模板引用) -->
    <script type="text/javascript">var is_mobile_cms = '<?php echo IS_MOBILE; ?>';var web_dir = '<?php echo WEB_DIR; ?>';</script>
    <script src="<?php echo LANG_PATH; ?>lang.js" type="text/javascript"></script>
    <script src="<?php echo THEME_PATH; ?>assets/global/plugins/jquery.min.js" type="text/javascript"></script>
    <script src="<?php echo THEME_PATH; ?>assets/js/cms.js" type="text/javascript"></script>
    <!-- 系统懒人版js结束 -->
</head>

<body id="kt_app_body" data-kt-app-layout="light-header" data-kt-app-header-fixed="true" data-kt-app-toolbar-enabled="true" class="app-default">

<!--begin::App-->
<div class="d-flex flex-column flex-root app-root" id="kt_app_root">
    <!--begin::Page-->
    <div class="app-page flex-column flex-column-fluid" id="kt_app_page">
        <!--begin::Header-->
        <div id="kt_app_header" class="app-header">
            <!--begin::Header container-->
            <div class="app-container container-xxl d-flex align-items-stretch justify-content-between" id="kt_app_header_container">
                <!--begin::Logo-->
                <div class="d-flex align-items-center flex-grow-1 flex-lg-grow-0 me-lg-15">
                    <a href="/">
                        <img alt="Logo" src="<?php echo SITE_LOGO; ?>" class="h-50px app-sidebar-logo-default theme-light-show" />
                    </a>
                </div>
                <!--end::Logo-->
                <!--begin::Header wrapper-->
                <div class="d-flex align-items-stretch justify-content-between flex-lg-grow-1" id="kt_app_header_wrapper">
                    <!--begin::Menu wrapper-->
                    <div class="app-header-menu app-header-mobile-drawer align-items-stretch" data-kt-drawer="true" data-kt-drawer-name="app-header-menu" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="250px" data-kt-drawer-direction="end" data-kt-drawer-toggle="#kt_app_header_menu_toggle" data-kt-swapper="true" data-kt-swapper-mode="{default: 'append', lg: 'prepend'}" data-kt-swapper-parent="{default: '#kt_app_body', lg: '#kt_app_header_wrapper'}">
                        <!--begin::Menu-->
                        <div class="menu menu-rounded menu-column menu-lg-row my-5 my-lg-0 align-items-stretch fw-semibold px-2 px-lg-0" id="kt_app_header_menu" data-kt-menu="true">
                            <!--begin:Menu item-->
                            <div class="<?php if ($indexc) { ?> show here <?php } ?> menu-item menu-lg-down-accordion menu-sub-lg-down-indention me-0 me-lg-2">
                                <!--begin:Menu link-->
                                <a href="/" class="menu-link">
                                    <span class="menu-title fs-5">首页</span>
                                </a>
                                <!--end:Menu link-->
                            </div>
                            <!--end:Menu item-->
                            <!--调用共享栏目-->
                            <!--第一层：调用pid=0表示顶级-->
                            <?php $list_return = $this->list_tag("action=category module=share pid=0"); if ($list_return && is_array($list_return)) extract($list_return, EXTR_OVERWRITE); $count=dr_count($return); if (is_array($return) && $return) { $key=-1; foreach ($return as $t) { $key++; $is_first=$key==0 ? 1 : 0;$is_last=$count==$key+1 ? 1 : 0; ?>
                            <!--begin:Menu item-->
                            <div <?php if ($t['child']) { ?>data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-placement="bottom-start"<?php } ?> class="<?php if ($catid && dr_in_array($catid, $t['catids'])) { ?> show here <?php } ?> menu-item menu-lg-down-accordion menu-sub-lg-down-indention me-0 me-lg-2">
                                <!--begin:Menu link-->
                                <a href="<?php echo $t['url']; ?>" class="menu-link">
                                    <span class="menu-title fs-5"><?php echo $t['name']; ?></span>
                                    <?php if ($t['child']) { ?>
                                    <span class="menu-arrow"></span>
                                    <?php } ?>
                                </a>
                                <!--end:Menu link-->
                                <?php if ($t['child']) { ?>
                                <!--begin:Menu sub-->
                                <div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown px-lg-2 py-lg-4 w-lg-250px">
                                    <!--第二层：调用第二级共享栏目-->
                                    <?php $list_return_t2 = $this->list_tag("action=category module=share pid=$t[id]  return=t2"); if ($list_return_t2 && is_array($list_return_t2)) extract($list_return_t2, EXTR_OVERWRITE); $count_t2=dr_count($return_t2); if (is_array($return_t2) && $return_t2) { $key_t2=-1;  foreach ($return_t2 as $t2) { $key_t2++; $is_first=$key_t2==0 ? 1 : 0;$is_last=$count_t2==$key_t2+1 ? 1 : 0; ?>
                                    <!--begin:Menu item-->
                                    <div data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-placement="right-start" class="<?php if ($catid && dr_in_array($catid, $t2['catids'])) { ?> show here <?php } ?> menu-item menu-lg-down-accordion">
                                        <!--begin:Menu link-->
                                        <a href="<?php echo $t2['url']; ?>" class="menu-link">
                                            <span class="menu-title"><?php echo $t2['name']; ?></span>
                                            <?php if ($t2['child']) { ?>
                                            <span class="menu-arrow"></span>
                                            <?php } ?>
                                        </a>
                                        <!--end:Menu link-->
                                        <?php if ($t2['child']) { ?>
                                        <!--begin:Menu sub-->
                                        <div class="menu-sub menu-sub-lg-down-accordion menu-sub-lg-dropdown menu-active-bg px-lg-2 py-lg-4 w-lg-225px">
                                            <!--第三层：调用第三级共享栏目数据-->
                                            <?php $list_return_t3 = $this->list_tag("action=category module=share pid=$t2[id]  return=t3"); if ($list_return_t3 && is_array($list_return_t3)) extract($list_return_t3, EXTR_OVERWRITE); $count_t3=dr_count($return_t3); if (is_array($return_t3) && $return_t3) { $key_t3=-1;  foreach ($return_t3 as $t3) { $key_t3++; $is_first=$key_t3==0 ? 1 : 0;$is_last=$count_t3==$key_t3+1 ? 1 : 0; ?>
                                            <!--begin:Menu item-->
                                            <div class="menu-item <?php if ($catid && dr_in_array($catid, $t3['catids'])) { ?> show here <?php } ?> ">
                                                <!--begin:Menu link-->
                                                <a class="menu-link" href="<?php echo $t3['url']; ?>">
                                                    <span class="menu-title"><?php echo $t3['name']; ?></span>
                                                </a>
                                                <!--end:Menu link-->
                                            </div>
                                            <!--end:Menu item-->
                                            <?php } } ?>

                                        </div>
                                        <!--end:Menu sub-->
                                        <?php } ?>
                                    </div>
                                    <!--end:Menu item-->
                                    <?php } } ?>
                                </div>
                                <!--end:Menu sub-->
                                <?php } ?>

                            </div>
                            <!--end:Menu item-->
                            <?php } } ?>




                        </div>
                        <!--end::Menu-->
                    </div>
                    <!--end::Menu wrapper-->

                    <!--begin::Navbar-->
                    <div class="app-navbar flex-shrink-0">
                        <!--begin::Search-->
                        <div class="app-navbar-item align-items-stretch ms-1 ms-md-3">
                            <!--begin::Search-->
                            <div id="kt_header_search" class="header-search d-flex align-items-stretch" data-kt-search-keypress="true" data-kt-search-min-length="2" data-kt-search-enter="enter" data-kt-search-layout="menu" data-kt-menu-trigger="auto" data-kt-menu-overflow="false" data-kt-menu-permanent="true" data-kt-menu-placement="bottom-end">
                                <!--begin::Search toggle-->
                                <div class="d-flex align-items-center" data-kt-search-element="toggle" id="kt_header_search_toggle">
                                    <div class="btn btn-icon btn-custom btn-icon-muted btn-active-light btn-active-color-primary w-30px h-30px w-md-40px h-md-40px">
                                        <span class="svg-icon svg-icon-2 svg-icon-md-1">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="currentColor" />
                                                <path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="currentColor" />
                                            </svg>
                                        </span>
                                        <!--end::Svg Icon-->
                                    </div>
                                </div>
                                <!--end::Search toggle-->
                                <!--begin::Menu-->
                                <div data-kt-search-element="content" class="menu menu-sub menu-sub-dropdown p-7 w-325px w-md-375px">
                                    <!--begin::Wrapper-->
                                    <div data-kt-search-element="wrapper">
                                        <!--begin::Form-->
                                        <form data-kt-search-element="form" action="/index.php"  aclass="w-100 position-relative mb-3" autocomplete="off">
                                            <input type="hidden" name="s" value="api">
                                            <input type="hidden" name="c" value="api">
                                            <input type="hidden" name="m" value="search">
                                            <input type="hidden" name="dir" value="news" >
                                            <!--begin::Icon-->
                                            <span class="svg-icon svg-icon-2 svg-icon-lg-1 svg-icon-gray-500 position-absolute top-50 translate-middle-y ms-0">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546" height="2" rx="1" transform="rotate(45 17.0365 15.1223)" fill="currentColor" />
                                                    <path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z" fill="currentColor" />
                                                </svg>
                                            </span>
                                            <!--end::Icon-->
                                            <!--begin::Input-->
                                            <input type="text" class="search-input form-control form-control-flush ps-10" name="keyword" value="" placeholder="搜索..." data-kt-search-element="input" />
                                            <!--end::Input-->

                                        </form>
                                        <!--end::Form-->
                                    </div>
                                    <!--end::Wrapper-->
                                </div>
                                <!--end::Menu-->
                            </div>
                            <!--end::Search-->
                        </div>
                        <!--end::Search-->
                        <!--begin::Activities-->


                        <!--begin::是否安装用户系统插件-->
                        <?php if (IS_USE_MEMBER) { ?>
                        <div class="app-navbar-item ms-1 ms-md-3" id="kt_header_user_menu_toggle">
                            <!--begin::Menu wrapper-->
                            <div class="cursor-pointer symbol symbol-30px symbol-md-40px" data-kt-menu-trigger="{default: 'click', lg: 'hover'}" data-kt-menu-attach="parent" data-kt-menu-placement="bottom-end">
                                <img src="<?php echo dr_avatar($member['uid']); ?>" alt="user" />
                            </div>
                            <!--begin::用户登录信息-->
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg menu-state-color fw-semibold py-4 fs-6 w-275px" data-kt-menu="true">
                                <!--begin::Menu item-->
                                <?php if (!$member) { ?>
                                <!--begin::Menu item-->
                                <div class="menu-item px-5 my-1">
                                    <a href="<?php echo dr_member_url("login/index"); ?>" class="menu-link px-5">登录</a>
                                </div>
                                <!--end::Menu item-->
                                <!--begin::Menu item-->
                                <div class="menu-item px-5">
                                    <a href="<?php echo dr_member_url("register/index"); ?>" class="menu-link px-5">注册</a>
                                </div>
                                <?php } else { ?>
                                <div class="menu-item px-3">
                                    <div class="menu-content d-flex align-items-center px-3">
                                        <!--begin::Avatar-->
                                        <div class="symbol symbol-50px me-5">
                                            <img src="<?php echo dr_avatar($member['uid']); ?>" />
                                        </div>
                                        <!--end::Avatar-->
                                        <!--begin::Username-->
                                        <div class="d-flex flex-column">
                                            <div class="fw-bold d-flex align-items-center fs-5"><?php echo $member['username']; ?></div>
                                            <span class="fw-semibold text-muted text-hover-primary fs-7"><?php echo $member['name']; ?></span>
                                        </div>
                                        <!--end::Username-->
                                    </div>
                                </div>
                                <!--end::Menu item-->
                                <!--begin::Menu separator-->
                                <div class="separator my-2"></div>
                                <!--end::Menu separator-->

                                <div class="menu-item px-5 my-1">
                                    <a href="<?php echo dr_member_url("home/index"); ?>" class="menu-link px-5">用户中心</a>
                                </div>
                                <div class="menu-item px-5 my-1">
                                    <a href="<?php echo dr_member_url("account/index"); ?>" class="menu-link px-5">资料修改</a>
                                </div>
                                <div class="menu-item px-5 my-1">
                                    <a href="<?php echo dr_member_url("account/password"); ?>" class="menu-link px-5">密码修改</a>
                                </div>
                                <div class="menu-item px-5">
                                    <a href="javascript:dr_loginout();" class="menu-link px-5">退出账号</a>
                                </div>
                                <!--end::Menu item-->
                                <?php } ?>
                            </div>
                            <!--end::用户登录信息-->
                        </div>
                        <?php } ?>
                        <!--end::User menu-->
                        <!--begin::Header menu toggle-->
                        <div class="app-navbar-item d-lg-none ms-2 me-n3" title="Show header menu">
                            <div class="btn btn-icon btn-active-color-primary w-30px h-30px w-md-35px h-md-35px" id="kt_app_header_menu_toggle">
                                <!--begin::Svg Icon | path: icons/duotune/text/txt001.svg-->
                                <span class="svg-icon svg-icon-2 svg-icon-md-1">
											<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
												<path d="M13 11H3C2.4 11 2 10.6 2 10V9C2 8.4 2.4 8 3 8H13C13.6 8 14 8.4 14 9V10C14 10.6 13.6 11 13 11ZM22 5V4C22 3.4 21.6 3 21 3H3C2.4 3 2 3.4 2 4V5C2 5.6 2.4 6 3 6H21C21.6 6 22 5.6 22 5Z" fill="currentColor"></path>
												<path opacity="0.3" d="M21 16H3C2.4 16 2 15.6 2 15V14C2 13.4 2.4 13 3 13H21C21.6 13 22 13.4 22 14V15C22 15.6 21.6 16 21 16ZM14 20V19C14 18.4 13.6 18 13 18H3C2.4 18 2 18.4 2 19V20C2 20.6 2.4 21 3 21H13C13.6 21 14 20.6 14 20Z" fill="currentColor"></path>
											</svg>
										</span>
                                <!--end::Svg Icon-->
                            </div>
                        </div>
                        <!--end::Header menu toggle-->
                    </div>
                    <!--end::Navbar-->
                </div>
                <!--end::Header wrapper-->
            </div>
            <!--end::Header container-->
        </div>
        <!--end::Header-->
        <!--begin::Wrapper-->
        <div class="app-wrapper flex-column flex-row-fluid" id="kt_app_wrapper">
            <!--begin::Main-->
            <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
                <!--begin::Content wrapper-->
                <div class="d-flex flex-column flex-column-fluid">

                    <!--begin::Content-->
                    <div id="kt_app_content" class="app-content flex-column-fluid">
                        <!--begin::Content container-->
                        <div id="kt_app_content_container" class="app-container container-xxl">