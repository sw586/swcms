<?php if (IS_DEV && IS_OEM_CMS) { ?>
<html>
<head>
    <link href="<?php echo THEME_PATH; ?>assets/global/css/admin.min.css?v=<?php echo CMF_UPDATE_TIME; ?>" rel="stylesheet" type="text/css" />
    <script src="<?php echo THEME_PATH; ?>assets/global/plugins/bootstrap/js/bootstrap.min.js?v=<?php echo CMF_UPDATE_TIME; ?>" type="text/javascript"></script>
</head>
<body class="page-content-white">
<div style="<?php if (\Phpcmf\Service::IS_MOBILE_USER()) { ?> padding:100px 10px 10px 10px; <?php } else { ?>padding:220px;<?php } ?>text-align: center">
    <a href="<?php echo $url; ?>" class="btn default btn-block"><?php echo dr_lang('单击下方链接进行访问该页面，关闭开发模式后将自动跳转'); ?></a>
    <p><?php echo $url; ?></p>
</div>
</body>
</html>
<?php } else { ?>
<html>
<head>
    <meta http-equiv="refresh" content="0;url=<?php echo $url; ?>">
</head>
<body style="background: url(<?php echo THEME_PATH; ?>assets/images/loading-0.gif);background-position: center;background-color: #fff;background-repeat: no-repeat;"> </body>
</html>
<?php } ?>