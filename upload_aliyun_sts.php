<?php

require_once __DIR__ . './aliyun-oss-php-sdk-master/autoload.php';

use OSS\Core\OssException;
use OSS\OssClient;

// 阿里云主账号AccessKey拥有所有API的访问权限，风险很高。强烈建议您创建并使用RAM账号进行API访问或日常运维，请登录 https://ram.console.aliyun.com 创建RAM账号。
// AccessKey ID LTAI1OYzgCMdPBO2
// AccessKeySecret wQnEecSjLxE0RzHzxxZBIuXmaRDYtj

$accessKeyId = "LTAI1OYzgCMdPBO2";
$accessKeySecret = "wQnEecSjLxE0RzHzxxZBIuXmaRDYtj";
// Endpoint以杭州为例，其它Region请按实际情况填写。
$endpoint = "http://oss-cn-beijing.aliyuncs.com";
$bucket = "guichaokeji";
$object = "";
$securityToken = "<yourSecurityToken>";

$content = "Hi, OSS.";

try {
	$ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false, $securityToken);

	$ossClient->putObject($bucket, $object, $content);
} catch (OssException $e) {
	print $e->getMessage();
}