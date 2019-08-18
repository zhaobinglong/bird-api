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

include 'db.class.php';
include 'wxlittle.class.php';

$db = new DB();
$jssdk = new wxlittle("wxb518954af3e70b39", "0e7ee5f0b1a5748e5ad7d5618ee01a29");

switch ($_GET['action']) {

// 用户在小程序中留言后的推送
// formid是长度不固定的字符串，在数据库中用短横线链接在一起
case 'pushAfterMessgae':
	$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
	$mypost = json_decode($rws_post);
	$sql = 'select formId from user where openid ="' . $mypost->toopenid . '" ';

	$res = mysql_fetch_array($db->dql($sql), MYSQL_ASSOC);

	$formIds = explode("-", $res['formId']);

	$formId = ''; // 本次发送使用用的formid

	if ($res) {
		$formId = array_pop($formIds);

	}

	if (!$formId) {
		echo '没有id，推送失败';
		return false;
	}
	$data['touser'] = $mypost->toopenid;
	$data['template_id'] = "9xDMxyeIvXf0_Sx0iIEykLzJ0Fzt6YUtzILPzvxMd4c";
	$data['page'] = "pages/date/detail/index?id=" . $mypost->id;
	$data['form_id'] = $formId;
	$data['data'] = array(
		'keyword1' => array('value' => $mypost->name),
		'keyword2' => array('value' => $mypost->cont),
		'keyword3' => array('value' => date('Y-m-d H:i:s', time())),
	);

	echo $jssdk->push($data);

	// 更新剩下的formid
	$sql = 'update user set formId = "' . implode('-', $formIds) . '" where openid = "' . $mypost->openid . '"';
	mysql_fetch_array($db->dql($sql), MYSQL_ASSOC);
	break;

case 'time':
	echo time();
	break;
case 'post':
	var_dump($_POST);
	break;

}