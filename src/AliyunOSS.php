<?php

namespace cheungq;

use Flow\File;
use luya\admin\storage\BaseFileSystemStorage;
use OSS\Core\OssException;
use OSS\OssClient;
use yii\base\InvalidConfigException;
use Yii;

/**
 * Aliyun OSS For Luya
 *
 *
 * ```php
 * 'storage' => [
 *     'class' => 'luya\aliyun\AliyunOSS',
 *     'accessKeyId' => 'xxxxx', // 阿里云OSS AccessKeyID
 *     'accessKeySecret' => 'xxxx', // 阿里云OSS AccessKeySecret
 *     'bucket' => 'xxx', // 阿里云的bucket空间
 *     'domain' => 'xxx', // 自己解析的域名，如果没有可填endPoint带http或者https
 *     'pathPrefix' => 'xxx', // 文件夹名称
 *     'endPoint' => 'xxx', //endPoint  如oss-cn-hangzhou.aliyuncs.com
 * ]
 * ```
 *
 * @property \OSS\OssClient $client Aliyun Oss Client.
 *
 * @author CheungQ <cheungq@foxmail.com>
 * @since 0.1.0
 */
class AliyunOSS extends BaseFileSystemStorage
{
    /**
     * @var string bucket
     */
    public $bucket;

    /**
     * @var string accessKeyId
     * $accessKeyId, ,
     */
    public $accessKeyId;

    /**
     * @var string accessKeySecret
     */
    public $accessKeySecret;

    /**
     * @var string oss-cn-hangzhou.aliyuncs.com
     */
    public $endPoint;

    public $domain;

    public $pathPrefix;
    /**
     * @var string The ACL default permission when writing new files.
     */
//    public $acl = '';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ( $this->bucket === null
            || $this->accessKeyId === null
            || $this->accessKeySecret === null
            || $this->endPoint === null
        ) {
            throw new InvalidConfigException("bucket,Secret,endPoint and Key must be provided for AliyunOSS component configuration.");
        }
    }

    private $_client;

    /**
     *
     * @return OssClient
     * @throws \OSS\Core\OssException
     */
    public function getClient()
    {
        if ($this->_client === null) {
            $this->_client = new OSSClient($this->accessKeyId,$this->accessKeySecret,$this->endPoint);
        }

        return $this->_client;
    }

    private $_httpPaths = [];

    /**
     * 获取网络访问的地址
     * @inheritdoc
     */
    public function fileHttpPath($fileName)
    {
        if (!isset($this->_httpPaths[$fileName])) {
            //获取oss文件地址
            $filePath = $fileName;
            if($this->pathPrefix){
                $filePath = $this->pathPrefix.'/'.$filePath;
            }
            if (!empty($this->getClient()->getObject($this->bucket, $filePath))){
                $this->_httpPaths[$fileName] = $this->domain .'/'.$filePath;
            }
        }

        return $this->_httpPaths[$fileName];
    }

    /**
     * @inheritdoc
     */
    public function fileAbsoluteHttpPath($fileName)
    {
        return $this->fileHttpPath($fileName);
    }

    /**
     * 获取内容到本地并返回本地文件的路径
     * @inheritdoc
     */
    public function fileServerPath($fileName)
    {
        try{
            $object = $this->getClient()->getObject($this->bucket, $this->pathPrefix.'/'.$fileName);
            if (!empty($object)){
                if (function_exists('sys_get_temp_dir')) {
                    $file = tempnam(sys_get_temp_dir(), "oss_tmp");
                } else {
                    $file = tempnam("/tmp", "oss_tmp");
                }
                file_put_contents($file, $object);
                return $file;
            }
            return false;
        }catch (OssException $e){
            throw new Exception("get server path failed");
        }
    }

    /**
     * @inheritdoc
     */
    public function fileSystemContent($fileName)
    {
        try {
            $object = $this->client->getObject($this->bucket, $fileName);

            if ($object) {
                return $object;
            }
        } catch (\OSS\Core\OssException $e) {
            return false;
        }

        return false;
    }

    /**
     * @inheritdoc
     */
    public function fileSystemExists($fileName)
    {
        return !empty($this->fileHttpPath($fileName));
    }


    /**
     * @inheritdoc
     */
    public function fileSystemSaveFile($source, $fileName)
    {
        $source =  file_get_contents($source);
        $filePath = $fileName;
        if($this->pathPrefix){
            $filePath = $this->pathPrefix.'/'.$filePath;
        }
        return $this->client->putObject($this->bucket,$filePath,$source);
    }

    /**
     * @inheritdoc
     */
    public function fileSystemReplaceFile($fileName, $newSource)
    {
        return $this->fileSystemSaveFile($newSource, $fileName);
    }

    /**
     * @inheritdoc
     */
    public function fileSystemDeleteFile($fileName)
    {
        $filePath = $fileName;
        if($this->pathPrefix){
            $filePath = $this->pathPrefix.'/'.$filePath;
        }
        return (bool) $this->client->deleteObject($this->bucket, $filePath);
    }
}
