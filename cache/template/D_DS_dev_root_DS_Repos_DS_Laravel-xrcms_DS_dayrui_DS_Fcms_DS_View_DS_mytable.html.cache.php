
<form class="form-horizontal " role="form" id="myform">
    <?php echo dr_form_hidden(); ?>
    <table id="mytable" data-show-export="true"></table>
</form>
<script type="text/javascript">
    var table_name = '<?php echo $mytable_name; ?>';
    <?php if (defined("SYS_TABLE_ISFOOTER") && SYS_TABLE_ISFOOTER) { ?>
    // 批量按钮设置为下面
    var is_foot = true;
    $("#toolbar label").hide();
    $("#toolbar button").hide();
    <?php } else { ?>
    // 批量按钮设置为上面
    var is_foot = $('#toolbar label').hasClass('table_select_all') == true ? false : true;
    <?php } ?>
    var table_ignoreColumn = <?php echo dr_count($list_field)+1; ?>;
    var page_id = <?php echo max(1,intval($_GET['page'])); ?>;
    var post_token = {<?php echo csrf_token(); ?>: "<?php echo csrf_hash(); ?>"};
    var mytable = <?php echo json_encode($mytable); ?>;
    if (mytable.foot_tpl) {
        var field_columns = [{
            checkbox: true,
            footerFormatter: function stockNumFormatter(data) {
                return is_foot ? mytable.foot_tpl : '';
            }
        }];
    } else {
        var field_columns = [];
    }

    // 按自定义字段显示
    <?php if (isset($list_field) && is_array($list_field) && $list_field) { $key_tt=-1;$count_tt=dr_count($list_field);foreach ($list_field as $i=>$tt) { $key_tt++; $is_first=$key_tt==0 ? 1 : 0;$is_last=$count_tt==$key_tt+1 ? 1 : 0; ?>
    field_columns.push({
        field: '<?php echo $i; ?>',
        title: '<?php echo dr_lang($tt['name']); ?>',
        align: '<?php if ($tt['center']) { ?>center<?php } else { ?>left<?php } ?>',
        switchable: true,
        sortable: true,
    <?php if ($tt['width']) { ?>width: '<?php echo $tt['width']; ?>px',<?php } ?>
    visible: true
    });
    <?php } } ?>
        // 记录右侧操作按钮
        if (mytable.link_tpl) {
            field_columns.push({
                field: 'operation',
                title: '操作',
                formatter: function formatter (value, row, index) {
                    var html = mytable.link_tpl;
                    eval(mytable.link_var);

                    return html;
                },
                visible: true
            });
        }
</script>
<script src="<?php echo THEME_PATH; ?>assets/global/plugins/bootstrap-table/bootstrap-table<?php if (!IS_XRDEV) { ?>.min<?php } ?>.js" type="text/javascript"></script>
<script src="<?php echo THEME_PATH; ?>assets/global/plugins/bootstrap-table/tableExport<?php if (!IS_XRDEV) { ?>.min<?php } ?>.js" type="text/javascript"></script>
<link href="<?php echo THEME_PATH; ?>assets/global/plugins/bootstrap-table/bootstrap-table.min.css"  rel="stylesheet" type="text/css" />
<?php if (is_file(ROOTPATH.'api/pdfmake/pdfmake.min.js')) { ?>
<script type="text/javascript" src="<?php echo ROOT_URL; ?>api/pdfmake/pdfmake.min.js"></script>
<script type="text/javascript" src="<?php echo ROOT_URL; ?>api/pdfmake/gbsn00lp_fonts.js"></script>
<script type="text/javascript" src="<?php echo ROOT_URL; ?>api/pdfmake/FileSaver.min.js"></script>
<?php }  if ($is_fixed_columns) { ?>
<script src="<?php echo THEME_PATH; ?>assets/global/plugins/bootstrap-table/bootstrap-table-fixed-columns.js" type="text/javascript"></script>
<link href="<?php echo THEME_PATH; ?>assets/global/plugins/bootstrap-table/bootstrap-table-fixed-columns.css"  rel="stylesheet" type="text/css" />
<?php } ?>
<script type="text/javascript">
$(function (){
    var $mytable = $('#mytable').bootstrapTable({
        url : "<?php echo dr_now_url(); ?>&is_ajax=1",
        surl : "<?php echo dr_web_prefix(SELF); ?>?is_ajax=1",
        method: 'get',
        striped: true,
        toolbar:"#toolbar",
        totalField:"msg",
        dataField:"data",
        selectItemName:"ids[]",
        idField:"id",
        cache: false,
        fixedColumns: true,  //固定列
        fixedRightNumber:1,	 //固定右侧列
        classes: "table table-striped table-bordered table-bordered2 table-hover table-checkable dataTable ",
        queryParamsType: 'my',
        sidePagination: 'server',
        silent: true,
        showRefresh: <?php if ($is_search && !$is_mobile) { ?>true<?php } else { ?>false<?php } ?>,
        showFullscreen: $('#table-search-tool-submit').hasClass('btn'),
        showToggle: true,
        showColumns: true,
        showExport: <?php if ($is_show_export && $is_search && !$is_mobile) { ?>true<?php } else { ?>false<?php } ?>,
        uniqueid: "id",
        singleSelect: false,
        clickToSelect:false,
        sortName: "",
        sortOrder: "",
        pageSize: <?php echo $mytable_pagesize; ?>,
        pageNumber: page_id,
        pageList: "[<?php echo $mytable_pagelist ? $mytable_pagelist : '10, 25, 50, 100, 200, 300' ?>]",
        <?php if (!\Phpcmf\Service::IS_PC_USER()) { ?>
        paginationHAlign: 'center',
        paginationDetailHAlign: 'center',
        paginationSuccessivelySize: 0,
        paginationPagesBySide: 0,<?php } ?>
        search: false,
        pagination: true,
        paginationShowPageGo: true,
        showFooter:is_foot ,
        exportTypes:['csv', 'txt', 'doc', 'excel'<?php if (is_file(ROOTPATH.'api/pdfmake/pdfmake.min.js')) { ?>, 'pdf'<?php } ?>],
        onPostBody:function () {
            //合并页脚
            if (is_foot && mytable.foot_tpl) {
                var footer_tbody = $('.fixed-table-body table tfoot');
                var footer_tr = footer_tbody.find('>tr');
                var footer_td = footer_tr.find('>th');
                var footer_td_1 = footer_td.eq(0);
                for(var i=1;i<footer_td.length;i++) {
                    footer_td.eq(i).remove();
                }
                footer_td_1.attr('colspan', footer_td.length).show();
            } else {
                $('.fixed-table-body table tfoot').remove();
            }
        },
        onLoadSuccess: function onLoadSuccess(data) {
            if (is_foot && mytable.foot_tpl) {
                var td_size = $('.fixed-table-body table tbody tr').length;
                var dp_size = $('.fixed-table-body table tfoot .dropdown-menu li').length;
                if (dp_size > td_size) {
                    $('.fixed-table-body table tfoot .dropdown-menu').attr("style", "max-height: "+(40*td_size)+"px;overflow-y: scroll;");
                }
            }
            return false;
        },
        columns: field_columns,
            queryParams: function queryParams(params) {
            var temp = {
                page : params.pageNumber,
                pagesize : params.pageSize,
                order : params.sortName+' '+params.sortOrder,
            };
            return temp;
        }
    });
    $("#table-search-tool-submit").click(function () {
        $mytable['bootstrapTable']('refresh');
    });
    $(".table-search-tool input").keydown(function(e) {
        if (e.keyCode == 13) {
            $mytable['bootstrapTable']('refresh');
        }
    });

});
</script>