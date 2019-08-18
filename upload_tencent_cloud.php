<?php

//允许的来源
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

require './cos-php-sdk-v5-master/cos-autoloader.php';

// 位于德国falankefu的OSS
// $cosClient = new Qcloud\Cos\Client(array('region' => 'eu-frankfurt',
// 	'credentials' => array(
// 		'appId' => '1251120507',
// 		'secretId' => 'AKID8EAwY1LOI0Z3NaTbO0RlurcBcrulKwHR',
// 		'secretKey' => 'GNJ7lFamsoYNJLajlrz48FNUchp0GYp5')));

// $bucket = 'ershou-1251120507';

// 位于北京的OSS
$cosClient = new Qcloud\Cos\Client(array('region' => 'ap-beijing',
	'credentials' => array(
		'appId' => '1251120507',
		'secretId' => 'AKIDABwYpvMVrIEuOdMTQ5UhjOounxnPw5ze',
		'secretKey' => 'dybbPx32nLhogJrCLhvxyVt51mh07zlp')));

$bucket = 'unibbs-1251120507';

$file = $_FILES;

// 内存中的文件路径
$local_path = $file['file']["tmp_name"] . '/' . $file['file']["name"];

// 文件名字
date_default_timezone_set('prc');
$key = date('YmdHis', time()) . rand(10000, 99999) . strrchr($file['file']["name"], '.');

// 先把文件上传到我们自己的服务器，再把图片转移到腾讯云oss中
// 必须保证服务器上的img文件夹具有读写权限
// 参数1：原始文件位置
// 参数e：新路径+名字

// 应该考虑把图片直接上传到oss中，中间多走一步就要多浪费时间

$res = move_uploaded_file($file['file']["tmp_name"], $_SERVER['DOCUMENT_ROOT'] . "/img/" . $key);

## 上传文件流
try {

	$result = $cosClient->putObject(array(
		'Bucket' => $bucket,
		'Key' => 'img/' . $key,
		'Body' => fopen($_SERVER['DOCUMENT_ROOT'] . "/img/" . $key, 'rb')));
	$data['name'] = $key;
	$data['url'] = $result['ObjectURL'];
	$data['oss_url'] = 'https://static.examlab.cn/img/' . $key;
	echo json_encode($data);

} catch (\Exception $e) {
	echo "$e\n";
}

// 上传成功后返回的数据
// [Expiration] =>
// [ETag] => "a174586648e93b18091d15d1fad9a77e"
// [ServerSideEncryption] =>
// [VersionId] =>
// [SSECustomerAlgorithm] =>
// [SSECustomerKeyMD5] =>
// [SSEKMSKeyId] =>
// [RequestCharged] =>
// [RequestId] => NWFmOTc4NmZfOTAwZTc4NjRfYTM4NF8xNTA4
// [ObjectURL] => http://ershou-1251120507.cos.eu-frankfurt.myqcloud.com/2018051419521521994.png
