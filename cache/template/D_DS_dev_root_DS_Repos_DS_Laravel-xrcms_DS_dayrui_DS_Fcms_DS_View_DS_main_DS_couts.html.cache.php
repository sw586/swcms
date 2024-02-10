<?php if (IS_USE_MODULE) { ?>
<div class="portlet portlet-sortable light bordered">
    <div class="portlet-title tabbable-line">
        <div class="caption">
            <i class="fa fa-bar-chart"></i>
            <span class="caption-subject"> <?php echo dr_lang('数据统计'); ?> </span>
        </div>
    </div>
    <div class="portlet-body">
        <?php


		$mtotal = [];
		$module = $ci->get_cache('module-'.SITE_ID.'-content');
        if ($module) {
            foreach ($module as $dir => $t) {
                // 判断权限
                if (!$ci->_is_admin_auth($dir.'/home/index')) {
                    continue;
                }
                $mtotal[$dir] = [
                    'name' => dr_lang($t['name']),
                    'today' => \Phpcmf\Service::M('auth')->_menu_link_url($dir.'/home/index', $dir.'/home/index'),
                    'all' => \Phpcmf\Service::M('auth')->_menu_link_url($dir.'/home/index', $dir.'/home/index'),
                    'verify' => \Phpcmf\Service::M('auth')->_menu_link_url($dir.'/verify/index', $dir.'/verify/index'),
                    'recycle' => \Phpcmf\Service::M('auth')->_menu_link_url($dir.'/home/index', $dir.'/recycle/index'),
                    'timing' => \Phpcmf\Service::M('auth')->_menu_link_url($dir.'/home/index', $dir.'/time/index'),
                    'post' => \Phpcmf\Service::M('auth')->_menu_link_url($dir.'/home/index', $dir.'/home/add'),
                    'list' => \Phpcmf\Service::M('auth')->_menu_link_url($dir.'/home/index', $dir.'/home/index'),
                ];
            }
        }
        ?>
        <div class="">
            <table class="table table-mtotal table-nomargin table-bordered2 table-striped table-bordered table-advance">
                <thead>
                <tr>
                    <th><?php echo dr_lang('模块'); ?></th>
                    <th style="text-align: center"><?php echo dr_lang('总数据'); ?></th>
                    <th style="text-align: center"><?php echo dr_lang('今日'); ?></th>
                    <?php if (IS_USE_MEMBER) { ?><th style="text-align: center"><?php echo dr_lang('待审核'); ?></th><?php } ?>
                    <th style="text-align: center"><?php echo dr_lang('待发布'); ?></th>
                    <th style="text-align: center"><?php echo dr_lang('回收站'); ?></th>
                </tr>
                </thead>
                <tbody>
                <?php if (isset($mtotal) && is_array($mtotal) && $mtotal) { $key_t=-1;$count_t=dr_count($mtotal);foreach ($mtotal as $dir=>$t) { $key_t++; $is_first=$key_t==0 ? 1 : 0;$is_last=$count_t==$key_t+1 ? 1 : 0; ?>
                <tr>
                    <td><?php echo $t['name']; ?></td>
                    <td style="text-align: center"><a class="drlabel drlabel-success tooltips" data-container="body" data-placement="top" data-original-title="<?php echo dr_lang('数据量不一定是精确的，仅供参考'); ?>" href="<?php echo $t['all']; ?>" id="<?php echo $dir; ?>_all"><img src="<?php echo THEME_PATH; ?>assets/images/mloading.gif"></a></td>
                    <td style="text-align: center"><a class="drlabel drlabel-success tooltips" data-container="body" data-placement="top" data-original-title="<?php echo dr_lang('数据量不一定是精确的，仅供参考'); ?>" href="<?php echo $t['today']; ?>" id="<?php echo $dir; ?>_today"><img src="<?php echo THEME_PATH; ?>assets/images/mloading.gif"></a></td>
                    <?php if (IS_USE_MEMBER) { ?><td style="text-align: center"><a class="drlabel drlabel-important tooltips" data-container="body" data-placement="top" data-original-title="<?php echo dr_lang('数据量不一定是精确的，仅供参考'); ?>" href="<?php echo $t['verify']; ?>" id="<?php echo $dir; ?>_verify"><img src="<?php echo THEME_PATH; ?>assets/images/mloading.gif"></a></td><?php } ?>
                    <td style="text-align: center"><a class="drlabel drlabel-important tooltips" data-container="body" data-placement="top" data-original-title="<?php echo dr_lang('数据量不一定是精确的，仅供参考'); ?>" href="<?php echo $t['timing']; ?>" id="<?php echo $dir; ?>_timing"><img src="<?php echo THEME_PATH; ?>assets/images/mloading.gif"></a></td>
                    <td style="text-align: center"><a class="drlabel drlabel-important tooltips" data-container="body" data-placement="top" data-original-title="<?php echo dr_lang('数据量不一定是精确的，仅供参考'); ?>" href="<?php echo $t['recycle']; ?>" id="<?php echo $dir; ?>_recycle"><img src="<?php echo THEME_PATH; ?>assets/images/mloading.gif"></a></td>

                    <script type="text/javascript">
                        $(function(){
                            $.getScript("<?php echo dr_url('module/api/mtotal'); ?>&dir=<?php echo $dir; ?>");
                        });
                    </script>
                </tr>
                <?php } } ?>
                </tbody>
            </table>

        </div>
    </div>
</div>
<?php } ?>