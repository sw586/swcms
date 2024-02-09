<?php if ($fn_include = $this->_include("header.html")) include($fn_include); ?>


<div class="right-card-box" style="position: inherit;">
<form class="form-horizontal" role="form" id="myform">
    <?php echo dr_form_hidden(); ?>
    <div class="table-scrollable">
        <table class="table table-striped table-bordered table-hover table-checkable dataTable">
            <thead>
            <tr class="heading">
                <th width="60" style="text-align:center">  </th>
                <th width="330"> <?php echo dr_lang('名称'); ?> / <?php echo dr_lang('目录'); ?></th>
                <?php if (!$is_oem) { ?>
                <th width="150"> <?php echo dr_lang('开发者'); ?> </th>
                <?php } ?>
                <th width="80"> <?php echo dr_lang('版本'); ?> </th>
                <th> <?php echo dr_lang('操作'); ?> </th>
            </tr>
            </thead>
            <tbody>
            <?php $i=1;  if (isset($list) && is_array($list) && $list) { $key_t=-1;$count_t=dr_count($list);foreach ($list as $dir=>$t) { $key_t++; $is_first=$key_t==0 ? 1 : 0;$is_last=$count_t==$key_t+1 ? 1 : 0; ?>
            <tr class="odd gradeX">
                <td style="text-align:center">
                    <span class="badge badge-success"> <?php echo $i; ?> </span>
                </td>
                <td><i class="<?php echo $t['icon']; ?>"></i> <?php echo $t['name']; ?> / <?php echo $dir;  if (!$is_oem && $t['vip']) { ?>
                    <span class="label label-danger"> VIP </span>
                    <?php } ?>
                </td>
                <?php if (!$is_oem) { ?>
                <td><?php if ($t['store']) { ?><a href="<?php echo $t['store']; ?>" target="_blank"><?php echo $t['author']; ?></a><?php } else {  echo $t['author'];  } ?></td>
                <?php } ?>
                <td> <?php if ($is_oem) {  echo $t['version'];  } else { ?>
                    <a href="javascript:dr_show_log('app-<?php echo $t['id']; ?>', '<?php echo $t['version']; ?>');"><?php echo $t['version']; ?></a>
                    <?php } ?></td>
                <td style="overflow: auto">
                    <?php if ($t['install']) {  if ($t['menu']) {  if (count($t['menu']) > 1) { ?>
                    <label class="dropdown-toggle">
                        <a class="btn green btn-xs dropdown-toggle dr_show_menu" data-dir="<?php echo $dir; ?>" data-toggle="dropdown"> <i class="fa fa-cog"></i> <?php echo dr_lang('进入'); ?></a>
                        <ul class="dropdown-menu" role="dropdown" id="dr_menu_<?php echo $dir; ?>">
                            <?php if (isset($t['menu']) && is_array($t['menu']) && $t['menu']) { $key_b=-1;$count_b=dr_count($t['menu']);foreach ($t['menu'] as $b) { $key_b++; $is_first=$key_b==0 ? 1 : 0;$is_last=$count_b==$key_b+1 ? 1 : 0;?>
                            <li>
                                <a href="<?php echo $b['url']; ?>"> <?php echo dr_lang($b['name']); ?> </a>
                            </li>
                            <?php } } ?>
                        </ul>
                    </label>
                    <?php } else { ?>
                    <label>
                        <a class="btn green btn-xs" href="<?php echo $t['menu'][0]['url']; ?>"> <i class="fa fa-cog"></i> <?php echo dr_lang('进入'); ?></a>
                    </label>
                    <?php }  } ?>
                    <label><a href="javascript:dr_load_ajax('<?php echo dr_lang($dir == 'module' ? '卸载内容系统会清空所有栏目及其内容，确定要卸载吗？' : '确定卸载此程序吗？'); ?>', '<?php echo dr_url('cloud/uninstall', ['dir'=>$dir]); ?>', 1);" class="btn btn-xs red"> <i class="fa fa-trash"></i> <?php echo dr_lang('卸载'); ?> </a></label>
                    <?php } else { ?>
                    <label><a href="javascript:<?php if (!$t['mtype'] && $t['ftype']=='module') { ?>dr_install_module_select('<?php echo dr_url('cloud/install', ['dir'=>$dir]); ?>')<?php } else { ?>dr_install_app('<?php echo dr_url('cloud/install', ['dir'=>$dir]); ?>')<?php } ?>;" class="btn btn-xs blue"> <i class="fa fa-plus"></i> <?php echo dr_lang('安装'); ?> </a></label>
                    <label><a href="javascript:dr_iframe('<?php echo dr_lang('删除提示'); ?>', '<?php echo dr_url('cloud/app_delete', ['dir'=>$dir]); ?>', '600px', '320px');" class="btn btn-xs red"> <i class="fa fa-close"></i> <?php echo dr_lang('删除'); ?> </a></label>
                    <?php } ?>
                    <label>
                        <a class="btn blue btn-xs" href="javascript:dr_iframe_show('<?php echo dr_lang('文件浏览'); ?>', '<?php echo dr_url('cloud/app_file', ['dir'=>$dir]); ?>', '600px', '320px');"> <i class="fa fa-file-o"></i> <?php echo dr_lang('文件'); ?></a>
                    </label>
                </td>
            </tr>
            <?php $i++;  } } ?>
            </tbody>
        </table>
    </div>


</form>
</div>

<script type="text/javascript">
    $(function (){
        $('.dr_show_menu').click(function (){
            var dir = $(this).data('dir');
            var top = $(this).offset().top+20;
            var left = $(this).offset().left;
            $("#dr_menu_"+dir).attr('style', 'top:'+top+'px;left:'+left+'px');
        });
    });
    function dr_show_log(id, v) {
        layer.open({
            type: 2,
            title: '版本日志',
            scrollbar: false,
            resize: true,
            maxmin: true, //开启最大化最小化按钮
            shade: 0,
            area: ['80%', '80%'],
            content: '<?php echo dr_url("cloud/log_show"); ?>&id='+id+'&version='+v,
        });
    }

</script>
<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>