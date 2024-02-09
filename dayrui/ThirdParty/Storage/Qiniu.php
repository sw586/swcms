<?php namespace Phpcmf\ThirdParty\Storage;

require_once __DIR__ . '/Qiniu/autoload.php';
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;

// qiniu存储
class Qiniu {

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

    private $accessKey;
    private $secretKey;

    // 初始化参数
    public function init($attachment, $filename) {

        $this->filename = trim($filename, DIRECTORY_SEPARATOR);
        $this->filepath = dirname($filename);
        $this->filepath == '.' && $this->filepath = '';
        $this->attachment = $attachment;


        $this->accessKey = $this->attachment['value']['AK'];
        $this->secretKey = $this->attachment['value']['SK'];

        return $this;
    }

    // 文件上传模式
    public function upload($type, $data, $watermark) {

        $this->data = $data;
        $this->watermark = $watermark;

        // 本地临时文件
        $filePath = WRITEPATH.'attach/'.SYS_TIME.'-'.str_replace([DIRECTORY_SEPARATOR, '/'], '-', $this->filename);
        $storage = new \Phpcmf\Library\Storage();
        $rt = $storage->uploadfile($type, $this->data, $filePath, $watermark, $this->attachment);
        if (!$rt['code']) {
            return $rt;
        }

        // 要上传的空间
        $bucket = $this->attachment['value']['bucket'];
		
		// 初始化Auth状态
		$auth = new Auth($this->accessKey, $this->secretKey);
		
		// 生成上传 Token
		$token = $auth->uploadToken($bucket);
		// 初始化 UploadManager 对象并进行文件的上传。
		$uploadMgr = new UploadManager();
		// 调用 UploadManager 的 putFile 方法进行文件的上传。
		list($ret, $err) = $uploadMgr->putFile($token, $this->filename, $filePath);
		if ($err !== null) {
			return dr_return_data(0, 'Error: '.var_export($err, true));
		}

		$md5 = md5_file($filePath);
        @unlink($filePath);

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

        $key = $this->filename;
        $bucket = $this->attachment['value']['bucket'];

        $auth = new Auth($this->accessKey, $this->secretKey);
        $config = new \Qiniu\Config();
        $bucketManager = new \Qiniu\Storage\BucketManager($auth, $config);
        $bucketManager->delete($bucket, $key);

        return;
    }

}

