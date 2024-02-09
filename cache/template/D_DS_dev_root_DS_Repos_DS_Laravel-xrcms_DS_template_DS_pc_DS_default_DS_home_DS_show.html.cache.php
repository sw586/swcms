<?php if ($fn_include = $this->_include("header.html")) include($fn_include); ?>

<!--begin::Page title-->
<div class="page-title d-flex flex-column justify-content-center flex-wrap mt-5">
    <!--begin::Breadcrumb-->
    <ul class="breadcrumb breadcrumb-separatorless fw-semibold fs-7 my-0 pt-1">
        <!--begin::Item-->
        <li class="breadcrumb-item text-muted">
            <a href="/" class="text-muted text-hover-primary">首页</a>
        </li>
        <!--end::Item-->
        <?php echo dr_catpos($catid, '', true, '<li class="breadcrumb-item"><span class="bullet bg-gray-400 w-5px h-2px"></span></li><li class="breadcrumb-item text-muted">'.PHP_EOL.' <a href="[url]" class="text-muted text-hover-primary">[name]</a> '.PHP_EOL.' </li>'.PHP_EOL); ?>

        <!--begin::Item-->
        <li class="breadcrumb-item">
            <span class="bullet bg-gray-400 w-5px h-2px"></span>
        </li>
        <!--end::Item-->
        <!--begin::Item-->
        <li class="breadcrumb-item text-muted">正文</li>
        <!--end::Item-->
    </ul>
    <!--end::Breadcrumb-->
</div>
<!--end::Page title-->

<div class="row g-5 g-xl-10 mb-5 mb-xl-10 mt-5">

    <!--begin::Col-->
    <div class="col-xl-8 mt-0">
        <!--begin::Table widget 14-->
        <div class="card card-flush h-md-100 p-10">


            <!--begin::Title-->
            <h5 class="text-dark text-hover-primary fs-4 fw-bold"><?php echo $title; ?></h5>
            <!--end::Title-->
            <!--begin::Info-->
            <div class="d-flex flex-wrap mb-6">
                <!--begin::Item-->
                <div class="me-9 my-1">
                    <span class="fa fa-user text-gray-400"> </span>
                    <span class="fw-bold text-gray-400"><?php echo $author; ?></span>
                    <!--end::Label-->
                </div>
                <!--end::Item-->
                <div class="me-9 my-1">
                    <span class="fa fa-calendar text-gray-400"> </span>
                    <span class="fw-bold text-gray-400"><?php echo $updatetime; ?></span>
                </div>
                <div class="me-9 my-1">
                    <span class="fa fa-eye text-gray-400"> </span>
                    <span class="fw-bold text-gray-400"><?php echo dr_show_hits($id); ?>次</span>
                    <!--begin::Label-->
                </div>
                <!--end::Item-->
            </div>
            <!--end::Info-->
            <!--begin::Description-->
            <div class="neirong fs-5 fw-semibold text-gray-600">
                <!--begin::Text-->
                <?php echo $content; ?>
                <!--end::Text-->
            </div>
            <!--end::Description-->
            <div class="mt-5">
                <!--关键词搜索-->
                <?php if (isset($kws) && is_array($kws) && $kws) { $key_url2=-1;$count_url2=dr_count($kws);foreach ($kws as $name=>$url2) { $key_url2++; $is_first=$key_url2==0 ? 1 : 0;$is_last=$count_url2==$key_url2+1 ? 1 : 0; ?>

                <a href="<?php echo $url2; ?>" class="badge badge-light-primary fw-bold my-2" target="_blank"><?php echo $name; ?></a>
                <?php } } ?>
            </div>
            <div class="mt-5">
                <p class="fc-show-prev-next">
                    <strong>上一篇：</strong><?php if ($prev_page) { ?><a href="<?php echo $prev_page['url']; ?>"><?php echo $prev_page['title']; ?></a><?php } else { ?>没有了<?php } ?><br>
                </p>
                <p class="fc-show-prev-next">
                    <strong>下一篇：</strong><?php if ($next_page) { ?><a href="<?php echo $next_page['url']; ?>"><?php echo $next_page['title']; ?></a><?php } else { ?>没有了<?php } ?>
                </p>
            </div>
            <!--begin::Block-->
            <div class="d-flex flex-stack mb-2 mt-10">
                <!--begin::Title-->
                <h3 class="text-dark fs-5 fw-bold text-gray-800">相关内容</h3>
                <!--end::Title-->
            </div>
            <div class="separator separator-dashed mb-9"></div>
            <!--end::Block-->
            <div class="row g-10">
                <?php $list_return = $this->list_tag("action=related module=MOD_DIR tag=$tag num=6"); if ($list_return && is_array($list_return)) extract($list_return, EXTR_OVERWRITE); $count=dr_count($return); if (is_array($return) && $return) { $key=-1; foreach ($return as $t) { $key++; $is_first=$key==0 ? 1 : 0;$is_last=$count==$key+1 ? 1 : 0; ?>
                <!--begin::Col-->
                <div class="col-md-4">
                    <!--begin::Feature post-->
                    <div class="card-xl-stretch me-md-6">
                        <!--begin::Image-->
                        <a class="d-block bgi-no-repeat bgi-size-cover bgi-position-center card-rounded position-relative min-h-175px mb-5" style="background-image:url('<?php echo dr_thumb($t['thumb']); ?>')" href="<?php echo $t['url']; ?>">

                        </a>
                        <!--end::Image-->
                        <!--begin::Body-->
                        <div class="m-0">
                            <!--begin::Title-->
                            <a href="<?php echo $t['url']; ?>" class="fs-5 text-dark fw-bold text-hover-primary text-dark lh-base"><?php echo dr_strcut($t['title'], 13); ?></a>
                            <!--end::Title-->
                            <!--begin::Text-->
                            <div class="fw-semibold fs-8 text-gray-600 text-dark my-2"><?php echo dr_strcut($t['description'], 33); ?></div>
                            <!--end::Text-->
                            <!--begin::Content-->
                            <div class="fs-6 fw-bold">
                                <!--begin::Date-->
                                <span class="text-muted"><?php echo $t['updatetime']; ?></span>
                                <!--end::Date-->
                            </div>
                            <!--end::Content-->
                        </div>
                        <!--end::Body-->
                    </div>
                    <!--end::Feature post-->
                </div>
                <!--end::Col-->
                <?php } } ?>

            </div>


        </div>
        <!--end::Table widget 14-->
    </div>
    <!--end::Col-->

    <!--begin::Col-->
    <div class="col-xl-4 mt-0">
        <!--begin::Chart Widget 35-->
        <div class="card card-flush h-md-100">
            <!--begin::Header-->
            <div class="card-header pt-5 ">
                <!--begin::Title-->
                <h3 class="card-title align-items-start flex-column">
                    <!--begin::Statistics-->
                    <div class="d-flex align-items-center mb-2">
                        <!--begin::Currency-->
                        <span class="fs-5 fw-bold text-gray-800 ">热门资讯</span>
                        <!--end::Currency-->
                    </div>
                    <!--end::Statistics-->
                </h3>
                <!--end::Title-->
            </div>
            <!--end::Header-->
            <!--begin::Body-->
            <div class="card-body pt-3">

                <?php $list_return = $this->list_tag("action=module catid=$catid order=hits num=10"); if ($list_return && is_array($list_return)) extract($list_return, EXTR_OVERWRITE); $count=dr_count($return); if (is_array($return) && $return) { $key=-1; foreach ($return as $t) { $key++; $is_first=$key==0 ? 1 : 0;$is_last=$count==$key+1 ? 1 : 0; ?>
                <!--begin::Item-->
                <div class="d-flex flex-stack mb-7">
                    <!--begin::Symbol-->
                    <div class="symbol symbol-60px symbol-2by3 me-4">
                        <div class="symbol-label" style="background-image: url('<?php echo dr_thumb($t['thumb']); ?>')"></div>
                    </div>
                    <!--end::Symbol-->
                    <!--begin::Title-->
                    <div class="m-0">
                        <a href="<?php echo $t['url']; ?>" class="text-dark fw-bold text-hover-primary fs-6"><?php echo dr_strcut($t['title'], 15); ?></a>
                        <span class="text-gray-600 fw-semibold d-block pt-1 fs-7"><?php echo dr_strcut($t['description'], 50); ?></span>
                    </div>
                    <!--end::Title-->
                </div>
                <?php } } ?>

            </div>
            <!--end::Body-->
        </div>
        <!--end::Chart Widget 35-->
    </div>
    <!--end::Col-->
</div>

<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>