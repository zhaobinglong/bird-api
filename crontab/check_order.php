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

// include 'db.class.php';
// include 'jssdk.class.php';

include '../core/lib/db.class.php';

$db = new db();
// // $jssdk = new JSSDK("wxfcacdc74295aabe5", "2465bb511cc5f5da62038e58841e78eb");

// // 每过半个小时推送一次
// // 查询所有设置到这个时间的用户，

// $time = date('H/i', time());
// echo $time;

list($msec, $sec) = explode(' ', microtime());
$msectime_now = (float) sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);

$msectime_before = $msectime_now - 12 * 60 * 60 * 1000;

$sql = 'select * from bird_order where 	flowintime >' . $msectime_before;
var_dump($sql);
$res = $db->dql($sql);

$data = array();
while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
	array_push($data, $row);
}

var_dump($data);

$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
$mypost = json_decode($rws_post);
$textTpl = "<?xml version='1.0' encoding='utf-8'?>
				<ApplyInfo>
				  <requesthead>
				    <user>%s</user>
				    <password>%s</password>
				    <server_version>%s</server_version>
				    <sender>%s</sender>
				    <uuid>%s</uuid>
				    <flowintime>%s</flowintime>
				  </requesthead>
				  <BODY>
				    <exchangeno>%s</exchangeno>
				  </BODY>
				</ApplyInfo>";
$xml = sprintf($textTpl, 'GC001', '123', '00000000', '002', $this->uuid, $this->getMsecTime(), $mypost->exchangeno);
$url = 'http://113.12.195.135:8088/picc-sinosoft-consumer-gc/Picc/Cbc';
$curl = curl_init();
$header[] = "Content-type: text/xml";
curl_setopt($curl, CURLOPT_URL, $url);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
$res = curl_exec($curl);
curl_close($curl);
// if ($res == '进入熔断器了') {
// 	$this->sendData('进入熔断器了');
// } else {
// 	$postObj = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
// 	$this->savePolicyno($postObj);
// 	$this->sendData($postObj);
// }
