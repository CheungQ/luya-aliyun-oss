# LUYA Aliyun OSS

Aliyun OSS for LUYA

## Installation

执行composer，或者修改composer.json

```sh
composer require cheungq/luya-aliyun-oss:~0.1.0
```
composer.json
```json
"require": {
        "cheungq/luya-aliyun-oss": "~0.1.0"
    },
```

### Configuration 

修改env-local.php 添加相应的配置

```php
'components' => [
   'storage' => [
       'class' => 'luya\aliyun\AliyunOSS',
       'accessKeyId' => 'xxxxx', // 阿里云OSS AccessKeyID
       'accessKeySecret' => 'xxxx', // 阿里云OSS AccessKeySecret
       'bucket' => 'xxx', // 阿里云的bucket空间
       'domain' => 'xxx', // 自己解析的域名，如果没有可填endPoint带http或者https
       'pathPrefix' => 'xxx', // 文件夹名称
       'endPoint' => 'xxx', //endPoint  如oss-cn-hangzhou.aliyuncs.com
   ]
]
```