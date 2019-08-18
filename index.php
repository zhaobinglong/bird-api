<?php

// 引入类文件
include 'wechat.class.php';

// 实例化对象
$wechatObj = new wechat();

if (isset($_GET['echostr'])) {

	//微信服务器验证响应
	// 后台配置开发者接口时需要
	$wechatObj->valid();

} elseif (isset($_GET['action'])) {

	// 服务器推送消息到微信app
	// $wechatObj->responsePush();

} else {

	// 接受事件推送，响应事件
	$wechatObj->responseMsg();
}

?>