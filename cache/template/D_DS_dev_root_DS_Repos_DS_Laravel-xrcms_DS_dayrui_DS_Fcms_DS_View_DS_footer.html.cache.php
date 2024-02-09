</div>
</div>
</div>
</div>
<div class="scroll-to-top">
    <i class="bi bi-arrow-up-circle-fill"></i>
</div>
<?php if (is_file(WRITEPATH.'update.lock')) { ?>
<script>
    dr_iframe_show('<?php echo dr_lang('升级程序脚本'); ?>', '<?php echo dr_url('cache/update_index'); ?>');
</script>
<?php } ?>
</body>
</html>