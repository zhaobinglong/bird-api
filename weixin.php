
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


    //计算JSSDK的配置信息
	include 'jssdk.class.php';
	// include 'log.class.php';
	$jssdk = new JSSDK();
	// $log = new log("../log/");
	// echo json_encode($jssdk->GetSignPackage($_GET['url']));

	switch($_GET['action']){
        
        // 微信小程序 调用接口获取openid
		case 'openid':
           echo $jssdk->getOpenid($_GET['jscode']);
		   break;

		//  获取调用js api的token
		case 'token':
		   echo $jssdk->getAccessToken();
		   break;
        
        // 通过base的方式获取用户openid
		case 'baseinfo':
		   $jssdk->getBaseInfoByCode($_GET['code']);
		   break;

		// 获取调用jsapi时候的签名
		case 'signature':
		    echo  $jssdk->getSignPackage($_GET['url']);
            break;
		//一旦用户授权登陆,拿到用户openid，就去生成分享图片
		// action=qrcode&openid=123456&subject=123;
		case 'qrcode':
			$openid = $_GET['openid'];
			$subject = $_GET['subject'];
		    $jssdk->createQrcode($openid,$subject);
			break;
	    case 'test':
		    $jssdk->test();
			break;

	    // 获取微信公众后后台所有图文消息
	    case 'getArticleList':
		    echo $jssdk->getArticleList();
			break;

	}

?>
