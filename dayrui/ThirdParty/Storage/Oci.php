<?php namespace Phpcmf\ThirdParty\Storage;

use Oci\OciObjectStorage\Service\OciObjectStorage;

class Oci {

    protected $data;
    protected $filename;
    protected $filepath;
    protected $attachment;
    protected $watermark;
    protected $fullpath;
    protected $fullname;

    public function init($attachment, $filename) {
        // 初始化代码
    }

    public function upload($type = 0, $data, $watermark) {
        // 初始化Oci客户端
        $client = new OciObjectStorage([
            'region' => $this->attachment['value']['region'],
            'credentials' => [
                'accessKeyId' => $this->attachment['value']['accessKey'],
                'secretAccessKey' => $this->attachment['value']['secretKey'],
            ],
        ]);

        $bucketName = $this->attachment['value']['bucketName'];
        $namespace = $this->attachment['value']['namespace'];
        $objectName = $this->filename;

        // 上传文件
        try {
            $result = $client->putObject([
                'NamespaceName' => $namespace,
                'BucketName' => $bucketName,
                'ObjectName' => $objectName,
                'Body' => fopen($data, 'r'),
            ]);
            $url = $result['ObjectURL']; // 获取上传文件的URL
            return dr_return_data(1, 'ok', ['url' => $url, 'md5' => md5_file($data)]);
        } catch (\Exception $e) {
            return dr_return_data(0, '上传失败: ' . $e->getMessage());
        }
    }

    public function delete() {
        // 删除文件逻辑
    }
}
