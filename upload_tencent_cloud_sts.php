<?php

header("Access-Control-Allow-Origin:*");
//OPTIONS通过后，保存的时间，如果不超过这个时间，是不会再次发起OPTIONS请求的。
header("Access-Control-Max-Age: 86400");
//!!!之前我碰到和你一样的问题，这个没有加导致的。
header("Access-Control-Allow-Headers: Content-Type");
//允许的请求方式
header("Access-Control-Allow-Methods: OPTIONS, GET, PUT, POST, DELETE");
//允许携带cookie
header("Access-Control-Allow-Credentials: true");

header("Content-type: text/html; charset=utf-8");

// 腾讯云文件上传临时密钥计算样例
// 这里获取 sts.php https://github.com/tencentyun/qcloud-cos-sts-sdk/blob/master/php/sts/sts.php
include './sts.php';
$sts = new STS();
// 配置参数
$config = array(
	'url' => 'https://sts.tencentcloudapi.com/',
	'domain' => 'sts.tencentcloudapi.com',
	'proxy' => '',
	'secretId' => 'AKIDABwYpvMVrIEuOdMTQ5UhjOounxnPw5ze', // 固定密钥
	'secretKey' => 'dybbPx32nLhogJrCLhvxyVt51mh07zlp', // 固定密钥
	'bucket' => 'unibbs-1251120507', // 换成你的 bucket
	'region' => 'ap-beijing', // 换成 bucket 所在园区
	'durationSeconds' => 1800, // 密钥有效期
	'allowPrefix' => '*', // 这里改成允许的路径前缀，可以根据自己网站的用户登录态判断允许上传的目录，例子：* 或者 a/* 或者 a.jpg
	// 密钥的权限列表。简单上传和分片需要以下的权限，其他权限列表请看 https://cloud.tencent.com/document/product/436/31923
	'allowActions' => array(
		// 所有 action 请看文档 https://cloud.tencent.com/document/product/436/31923
		// 简单上传
		'name/cos:PutObject',
		'name/cos:PostObject',
		// 分片上传
		'name/cos:InitiateMultipartUpload',
		'name/cos:ListMultipartUploads',
		'name/cos:ListParts',
		'name/cos:UploadPart',
		'name/cos:CompleteMultipartUpload',
	),
);
// 获取临时密钥，计算签名
$tempKeys = $sts->getTempKeys($config);
// 返回数据给前端
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // 这里修改允许跨域访问的网站
header('Access-Control-Allow-Headers: origin,accept,content-type');
echo json_encode($tempKeys);