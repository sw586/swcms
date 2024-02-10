<?php if ($fn_include = $this->_include("header.html")) include($fn_include); ?>


<div class="right-card-box">
<form class="form-horizontal" role="form" id="myform">
    <?php echo dr_form_hidden(); ?>
    <div class="bootstrap-table bootstrap-table2">
        <div id="toolbar" class="toolbar">
        <label><button type="button" onclick="dr_ajax_option('<?php echo dr_url('field/del', ['rname' => $rname, 'rid' => $rid]); ?>', '<?php echo dr_lang('你确定要删除它们吗？'); ?>', 1)" class="btn red btn-sm"> <i class="fa fa-trash"></i> <?php echo dr_lang('删除'); ?></button></label>
            <label><button type="button" onclick="javascript:dr_dcall();" class="btn blue btn-sm"> <i class="fa fa-sign-out"></i> <?php echo dr_lang('导出'); ?></button></label>
        </div>
    </div>


    <div class="clearfix"></div>
    <div class="table-scrollable table-clearfix">


        <table class="table table-noborder table-fc-upload table-striped table-bordered table-hover table-checkable dataTable">
            <thead>
            <tr class="heading">
            <?php if ($ci->_is_admin_auth('del')) { ?>
            <th class="myselect">
                <label class="mt-table mt-table mt-checkbox mt-checkbox-single mt-checkbox-outline">
                    <input type="checkbox" class="group-checkable" data-set=".checkboxes" />
                    <span></span>
                </label>
            </th>
            <?php } ?>
            <th width="65" style="text-align:center"> <?php echo dr_lang('排序'); ?> </th>
            <th style="text-align: center;font-size: 15px;" width="65">Id</th>
            <th><?php echo dr_lang('字段'); ?></th>
            <th width="120"><?php echo dr_lang('类别'); ?></th>
            <th width="70" style="text-align: center"><?php echo dr_lang('系统'); ?></th>
            <th width="70" style="text-align: center"><?php echo dr_lang('主表'); ?></th>
            <th width="80"  style="text-align: center"><?php echo dr_lang('XSS过滤'); ?></th>
            <th width="70" style="text-align: center"><?php echo dr_lang('前端'); ?></th>
            <th width="70" style="text-align: center"><?php echo dr_lang('可用'); ?></th>
            <th width="150"></th>
        </tr>
        </thead>
        <tbody>
        <?php if (isset($list) && is_array($list) && $list) { $key_t=-1;$count_t=dr_count($list);foreach ($list as $t) { $key_t++; $is_first=$key_t==0 ? 1 : 0;$is_last=$count_t==$key_t+1 ? 1 : 0;?>
        <tr class="odd gradeX" id="dr_row_<?php echo $t['id']; ?>">
            <?php if ($ci->_is_admin_auth('del')) { ?>
            <td class="myselect">
                <label class="mt-table mt-table mt-checkbox mt-checkbox-single mt-checkbox-outline">
                    <?php if ($t['issystem']) { ?>
                    <input type="checkbox" class="" disabled name="" value="" />
                    <?php } else { ?>
                    <input type="checkbox" class="checkboxes" name="ids[]" value="<?php echo $t['id']; ?>" />
                    <?php } ?>
                    <span></span>
                </label>
            </td>
            <?php } ?>
            <td style="text-align:center"> <?php if ($t['spacer']) {  } else { ?><input type="text" onblur="dr_ajax_save(this.value, '<?php echo dr_url('field/option', ['rname' => $rname, 'rid' => $rid, 'op' => 'save', 'id'=>$t['id']]); ?>', 'displayorder')" value="<?php echo $t['displayorder']; ?>" class="displayorder form-control input-sm input-inline input-mini"> <?php } ?></td>
            <td style="text-align: center;font-size: 15px;"><?php echo $t['id']; ?></td>
            <td> <?php echo $t['spacer'];  echo dr_lang($t['name']); ?> / <?php echo $t['fieldname']; ?></td>
            <td><?php echo $t['ftype']; ?></td>
            <td style="text-align: center;font-size: 15px;"><?php if ($t['issystem']) { ?><i class="fa fa-check-circle"></i><?php } else { ?>-<?php } ?></td>
            <td style="text-align: center;font-size: 15px;"><?php if ($t['ismain']) { ?><i class="fa fa-check-circle"></i><?php } else { ?><i class="fa fa-times-circle"></i><?php } ?></td>
            <td style="text-align:center">
                <a href="javascript:;" onclick="dr_ajax_open_close(this, '<?php echo dr_url('field/option', ['rname' => $rname, 'rid' => $rid, 'op' => 'xss', 'id'=>$t['id']]); ?>', 0);" class="badge badge-<?php if ($t['setting']['validate']['xss']) { ?>yes<?php } else { ?>no<?php } ?>"><i class="fa fa-<?php if ($t['setting']['validate']['xss']) { ?>check<?php } else { ?>times<?php } ?>"></i></a>
            </td>
            <td style="text-align:center">
                <a href="javascript:;" onclick="dr_ajax_open_close(this, '<?php echo dr_url('field/option', ['rname' => $rname, 'rid' => $rid, 'op' => 'member','id'=>$t['id']]); ?>', 0);" class="badge badge-<?php if (!$t['ismember']) { ?>no<?php } else { ?>yes<?php } ?>"><i class="fa fa-<?php if (!$t['ismember']) { ?>times<?php } else { ?>check<?php } ?>"></i></a>
            </td>
            <td style="text-align:center">
                <a href="javascript:;" onclick="dr_ajax_open_close(this, '<?php echo dr_url('field/option', ['rname' => $rname, 'rid' => $rid, 'op' => 'disabled','id'=>$t['id']]); ?>', 1);" class="badge badge-<?php if ($t['disabled']) { ?>no<?php } else { ?>yes<?php } ?>"><i class="fa fa-<?php if ($t['disabled']) { ?>times<?php } else { ?>check<?php } ?>"></i></a>
            </td>
            <td>
                <label><a href="<?php echo dr_url('field/edit', ['rname' => $rname, 'rid' => $rid, 'id'=>$t['id']]); ?>" class="btn btn-xs green"> <i class="fa fa-edit"></i> <?php echo dr_lang('修改'); ?> </a></label>
                <label><a href="javascript:dr_iframe_show('<?php echo dr_lang('导出'); ?>', '<?php echo dr_url('field/export', ['rname' => $rname, 'rid' => $rid, 'id'=>$t['id']]); ?>');" class="btn btn-xs red"> <i class="fa fa-sign-out"></i> <?php echo dr_lang('导出'); ?> </a></label>
            </td>
        </tr>
        <?php } } ?>
        </tbody>
    </table>
    </div>


</form>
</div>
<script>
    function dr_dcall() {
        dr_iframe_show('<?php echo dr_lang('导出'); ?>', '<?php echo dr_url('field/export_all', ['rname' => $rname, 'rid' => $rid, 'id'=>$t['id']]); ?>&ids='+$('#myform').serialize());
    }
</script>
<?php if ($fn_include = $this->_include("footer.html")) include($fn_include); ?>