<?php

namespace cheungq;

use luya\admin\storage\BaseFileSystemStorage;
use OSS\OssClient;
use yii\base\InvalidConfigException;
use Yii;

/**
 * Aliyun OSS For Luya
 *
 *
 *
 * ```php
 * 'storage' => [
 *     'class' => 'luya\aliyun\AliyunOSS',
 *     'accessKeyId' => 'xxxxx', // 阿里云OSS AccessKeyID
 *     'accessKeySecret' => 'xxxx', // 阿里云OSS AccessKeySecret
 *     'bucket' => 'xxx', // 阿里云的bucket空间
 *     'domain' => 'xxx', // 阿里云的bucket空间
 *     'pathPrefix' => 'xxx', // 阿里云的bucket空间
 *     'endPoint' => 'oss-cn-hangzhou.aliyuncs.com', //endPoint
 * ]
 * ```
 *
 * @property \OSS\OssClient $client Aliyun Oss Client.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class AliyunOSS extends BaseFileSystemStorage
{
    /**
     * @var string Contains the name of the bucket defined on amazon webservice.
     */
    public $bucket;

    /**
     * @var string The authentiication key in order to connect to the s3 bucket.
     * $accessKeyId, ,
     */
    public $accessKeyId;

    /**
     * @var string The authentification secret in order to connect to the s3 bucket.
     */
    public $accessKeySecret;

    /**
     * @var string The authentification secret in order to connect to the s3 bucket.
     */
    public $endPoint;

    public $domain;

    public $pathPrefix;
    /**
     * @var string The ACL default permission when writing new files.
     */
    public $acl = 'public-read';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ( $this->bucket === null || $this->accessKeyId === null) {
            throw new InvalidConfigException("region, bucket and key must be provided for s3 component configuration.");
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
     * @inheritdoc
     */
    public function fileHttpPath($fileName)
    {
        if (!isset($this->_httpPaths[$fileName])) {
//            Yii::debug('Get OSS object url: ' . $fileName, __METHOD__);
//            #TODO 获取oss文件地址
//            var_dump($this->getClient()->getObject($this->bucket, $this->pathPrefix.'/'.$fileName));
//            exit;
            if (!empty($this->getClient()->getObject($this->bucket, $this->pathPrefix.'/'.$fileName))){
                $this->_httpPaths[$fileName] = $this->domain .'/'.$this->pathPrefix.'/'.$fileName;
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
     * @inheritdoc
     */
    public function fileServerPath($fileName)
    {
        return $this->fileHttpPath($fileName);
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
//        $config = [
//            'ACL' => $this->acl,
//            'Bucket' => $this->bucket,
//            'Key' => $fileName,
//            'SourceFile' => $source,
//        ];
        $source =  file_get_contents($source);
        return $this->client->putObject($this->bucket,$this->pathPrefix."/".$fileName,$source);
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
        return (bool) $this->client->deleteObject($this->bucket, $fileName);
    }
}
