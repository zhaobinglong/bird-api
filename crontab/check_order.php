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

require_once __ROOT__ . '/core/lib/db.class.php';

$db = new DB();
// $jssdk = new JSSDK("wxfcacdc74295aabe5", "2465bb511cc5f5da62038e58841e78eb");

// 每过半个小时推送一次
// 查询所有设置到这个时间的用户，

$time = date('H/i', time());
echo $time;

$sql = 'select * from bird_order';
$res = $db->dql($sql);

$data = array();
while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
	array_push($data, $row);
}

var_dump($data);
