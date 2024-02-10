<div class="col-md-2 col-sm-2 col-xs-6" onclick="dr_select_ids(<?php echo $tid; ?>, <?php echo $t['id']; ?>)">
    <div class="color-demo tooltips" data-original-title="<?php echo $t['filename']; ?>&nbsp;&nbsp;<?php echo dr_format_file_size($t['filesize']); ?>">
        <div class="color-view bg-default bg-font-default div_ids<?php echo $tid; ?> div_ids<?php echo $tid; ?>_<?php echo $t['id']; ?>">
            <?php echo dr_file_list_preview_html($t); ?>
        </div>
        <input type="hidden" class="select_ids<?php echo $tid; ?>_<?php echo $t['id']; ?>" name="ids<?php echo $tid; ?>[]" value="" />
    </div>
</div>