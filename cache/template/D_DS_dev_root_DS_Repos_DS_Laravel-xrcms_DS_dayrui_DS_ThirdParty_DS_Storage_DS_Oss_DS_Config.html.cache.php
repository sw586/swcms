<div class="form-group r r2">
    <label class="col-md-2 control-label">Access Key ID</label>
    <div class="col-md-7">
        <input name="data[value][2][accessKeyId]" value="<?php echo $data['value']['accessKeyId']; ?>" class="form-control" type="text" />
    </div>
</div>
<div class="form-group r r2">
    <label class="col-md-2 control-label">Access Key Secret</label>
    <div class="col-md-7">
        <input name="data[value][2][accessKeySecret]" value="<?php echo $data['value']['accessKeySecret']; ?>" class="form-control" type="text" />
    </div>
</div>
<div class="form-group r r2">
    <label class="col-md-2 control-label">BucketName</label>
    <div class="col-md-7">
        <input name="data[value][2][bucket]" value="<?php echo $data['value']['bucket']; ?>" class="form-control" type="text" />
    </div>
</div>
<div class="form-group r r2">
    <label class="col-md-2 control-label"><?php echo dr_lang('指定目录'); ?></label>
    <div class="col-md-7">
        <input class="form-control " type="text" name="data[value][2][path]" value="<?php echo $data['value']['path']; ?>" />
        <span class="help-block"> <?php echo dr_lang('选填，不填的话默认为根目录，填写格式：test或者test/test2'); ?> </span>
        <span class="help-block"> <?php echo dr_lang('注意：当填写指定目录时，下方的访问地址必须也要加上这个目录'); ?> </span>
    </div>
</div>
<div class="form-group r r2">
    <label class="col-md-2 control-label"><?php echo dr_lang('图片后缀'); ?></label>
    <div class="col-md-7">
        <input class="form-control " type="text" name="data[value][2][image]" value="<?php echo $data['value']['image']; ?>" />
        <span class="help-block"> <?php echo dr_lang('选填，使用dr_thumb函数或者编辑器中的远程图片，会自动加上此处设置的后缀字符串'); ?> </span>
    </div>
</div>
<div class="form-group r r2">
    <label class="col-md-2 control-label"><?php echo dr_lang('带尺寸的图片后缀'); ?></label>
    <div class="col-md-7">
        <input class="form-control " type="text" name="data[value][2][wh_prefix_image]" value="<?php echo $data['value']['wh_prefix_image']; ?>" />
        <span class="help-block"> <?php echo dr_lang('选填，与通用图片后缀功能一样，这个能根据参数来匹配，填写格式：{width}x{height}'); ?> </span>
    </div>
</div>
<div class="form-group r r2">
    <label class="col-md-2 control-label"><?php echo dr_lang('OSS域名'); ?></label>
    <div class="col-md-7">
        <input class="form-control " type="text" name="data[value][2][endpoint]" value="<?php echo $data['value']['endpoint']; ?>" />
        <span class="help-block"> <?php echo dr_lang('如果和服务器在同一网段，尽量使用内网域名，不要填写自定义域名，也不要加http://'); ?> </span>
    </div>
</div>