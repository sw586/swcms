<?php namespace Phpcmf\ThirdParty\Storage;

require_once __DIR__ . '/Oss/autoload.php';
use OSS\OssClient;
use OSS\Core\OssException;

class Oss {

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
    protected $accessKeyId;
    protected $accessKeySecret;
    protected $hostname;

    // 初始化参数
    public function init($attachment, $filename) {

        $this->filename = trim($filename, DIRECTORY_SEPARATOR);
        $this->filepath = dirname($filename);
        $this->filepath == '.' && $this->filepath = '';
        $this->attachment = $attachment;
        $this->accessKeyId = trim($attachment['value']['accessKeyId']);
        $this->accessKeySecret = trim($attachment['value']['accessKeySecret']);
        $this->hostname = trim($attachment['value']['endpoint']);

        return $this;
    }

    // 文件上传模式
    public function upload($type, $data, $watermark) {

        $this->data = $data;
        $this->watermark = $watermark;

        // 本地临时文件
        $locpath = WRITEPATH.'attach/'.SYS_TIME.'-'.str_replace([DIRECTORY_SEPARATOR, '/'], '-', $this->filename);
		
		$storage = new \Phpcmf\Library\Storage();
        $rt = $storage->uploadfile($type, $this->data, $locpath, $watermark, $this->attachment);
        if (!$rt['code']) {
            return $rt;
        }

        $object = $this->filename;
        if (isset($this->attachment['value']['path']) && $this->attachment['value']['path']) {
            $object = trim($this->attachment['value']['path'], '/').'/'.$this->filename;
        }

        $bucket = $this->attachment['value']['bucket'];

		try {
			$ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, 'http://'.$this->hostname);
			$ossClient->uploadFile($bucket, $object, $locpath);
		} catch (OssException $e) {
			return dr_return_data(0, $e->getMessage());
		}

        $md5 = md5_file($locpath);
        @unlink($locpath);

        // 上传成功
        return dr_return_data(1, 'ok', [
            'url' => $this->attachment['url'].$this->filename,
            'md5' => $md5,
            'size' => $rt['msg'],
            'info' => $rt['data']
        ]);
    }

    // 删除文件
    public function delete() {

        $object = $this->filename;
        $bucket = $this->attachment['value']['bucket'];
        if (isset($this->attachment['value']['path']) && $this->attachment['value']['path']) {
            $object = trim($this->attachment['value']['path'], '/').'/'.$this->filename;
        }

		try {
			$ossClient = new OssClient($this->accessKeyId, $this->accessKeySecret, 'http://'.$this->hostname);
			$ossClient->deleteObject($bucket, $object);
		} catch (OssException $e) {
            log_message('error', '阿里云存储删除失败：'.$e->getMessage());
            return;
		}
		

        return;
    }

    
}