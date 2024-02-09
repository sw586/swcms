<?php namespace Phpcmf\ThirdParty\Storage;

// qq存储
class Qcloud {

    // 存储内容
    protected $data;

    // 文件存储路径
    protected $filename;

    // 文件存储目录
    protected $filepath;

    // 附件存储的信息
    protected $attachment;

    // 是否进行图片水印
    protected $watermark;

    private $accessKeyId;
    private $accessKeySecret;
    private $Region;
    private $APPID;

    // 初始化参数
    public function init($attachment, $filename) {

        $this->filename = trim($filename, DIRECTORY_SEPARATOR);
        $this->filepath = dirname($filename);
        $this->filepath == '.' && $this->filepath = '';
        $this->attachment = $attachment;

        $this->accessKeyId = trim($attachment['value']['KeyId']);
        $this->accessKeySecret = trim($attachment['value']['KeySecret']);
        $this->Region = trim($attachment['value']['Region']);

        //
        require FCPATH.'ThirdParty/Storage/Qcloud/cos.phar';

        return $this;
    }

    // 文件上传模式
    public function upload($type, $data, $watermark) {

        $this->data = $data;
        $this->watermark = $watermark;

        // 本地临时文件
        $srcPath = WRITEPATH.'attach/'.SYS_TIME.'-'.str_replace([DIRECTORY_SEPARATOR, '/'], '-', $this->filename);
        if ($type) {
            // 移动失败
            if (!(dr_move_uploaded_file($this->data, $srcPath) || !is_file($srcPath))) {
                return dr_return_data(0, dr_lang('文件移动失败'));
            }
            $file_size = filesize($srcPath);
        } else {
            $file_size = file_put_contents($srcPath, $this->data);
            if (!$file_size || !is_file($srcPath)) {
                return dr_return_data(0, dr_lang('文件创建失败'));
            }
        }

        $info = [];
        if (dr_is_image($this->fullname)) {
            // 图片压缩处理
            if ( $this->attachment['image_reduce']) {
                \Phpcmf\Service::L('image')->reduce($this->fullname, $this->attachment['image_reduce']);
            }
            // 强制水印
            if ($this->watermark) {
                $config = \Phpcmf\Service::C()->get_cache('site', SITE_ID, 'watermark');
                $config['source_image'] = $srcPath;
                $config['dynamic_output'] = false;
                \Phpcmf\Service::L('Image')->watermark($config);
            }
            // 获取图片尺寸
            $img = getimagesize($this->fullname);
            if (!$img) {
                // 删除文件
                $this->delete();
                return dr_return_data(0, dr_lang('此图片不是一张可用的图片'));
            }
            $info = [
                'width' => $img[0],
                'height' => $img[1],
            ];
        }


        $cosClient = new \Qcloud\Cos\Client(
            array(
                'region' => $this->Region,
                'schema' => isset($this->attachment['value']['http']) && $this->attachment['value']['http'] ? 'http' : 'https', //协议头部，默认为http
                'credentials'=> array(
                    'secretId'  => $this->accessKeyId ,
                    'secretKey' => $this->accessKeySecret)
            )
        );

        try {
            $object = $this->filename;
            if (isset($this->attachment['value']['path']) && $this->attachment['value']['path']) {
                $object = trim($this->attachment['value']['path'], '/').'/'.$this->filename;
            }
            $result = $cosClient->upload(
                $bucket = trim($this->attachment['value']['bucket']), //存储桶名称
                $key = $object, //此处的 key 为对象键
                $body = fopen($srcPath, 'rb')
            );
            // 请求成功
            // 上传成功
            $md5 = md5_file($srcPath);
            @unlink($srcPath);
            return dr_return_data(1, 'ok', [
                'url' => $this->attachment['url'].$this->filename,
                'md5' => $md5,
                'size' => $file_size,
                'info' => $info
            ]);
        } catch (\Exception $e) {
            // 请求失败
            return dr_return_data(0, $e->getMessage());
        }
    }


    // 删除文件
    public function delete() {

        $cosClient = new \Qcloud\Cos\Client(
            array(
                'region' => $this->Region,
                'schema' => isset($this->attachment['value']['http']) && $this->attachment['value']['http'] ? 'http' : 'https', //协议头部，默认为http
                'credentials'=> array(
                    'secretId'  => $this->accessKeyId ,
                    'secretKey' => $this->accessKeySecret)
            )
        );
        try {
            $object = $this->filename;
            if (isset($this->attachment['value']['path']) && $this->attachment['value']['path']) {
                $object = trim($this->attachment['value']['path'], '/').'/'.$this->filename;
            }
            $result = $cosClient->deleteObject(array(
                'Bucket' => $this->attachment['value']['bucket'],
                'Key' => $object
            ));
            // 请求成功
            return;
        } catch (\Exception $e) {
            // 请求失败
            log_message('error', '腾讯云存储删除失败：'.$e->getMessage());
        }

    }



}

