<?php

include 'log.class.php';
include 'http.class.php';
include 'config.php';

class weixin {
	private $appId;
	private $appSecret;
	public $log;
	public $http;
	public $db;

	public function __construct($db) {
		$this->appId = ZTD_APPID;
		$this->appSecret = ZTD_APPSECRET;
		$this->db = $db;
		// $this->log = new log(LOG_PATH);
		$this->http = new http();
	}

	// 微信服务器验证
	public function valid() {
		$echoStr = $_GET["echostr"];
		if ($this->checkSignature()) {
			echo $echoStr;
			exit;
		}
	}

	// 字符串验证
	private function checkSignature() {
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"];

		// 使用构造函数中定义的token
		$token = $this->token;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode($tmpArr);
		$tmpStr = sha1($tmpStr);

		if ($tmpStr == $signature) {
			return true;
		} else {
			return false;
		}
	}

	// 获取签名字符串
	public function getSignPackage($url) {
		$jsapiTicket = $this->getJsApiTicket();
		// $url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
		$timestamp = time();
		$nonceStr = $this->createNonceStr();

		// 这里参数的顺序要按照 key 值 ASCII 码升序排序
		$string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

		$signature = sha1($string);

		$signPackage = array(
			"appId" => $this->appId,
			"nonceStr" => $nonceStr,
			"timestamp" => $timestamp,
			"url" => $url,
			"signature" => $signature,
			"rawString" => $string,
		);
		return json_encode($signPackage);
	}

	// 生成随机字符串
	private function createNonceStr($length = 16) {
		$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$str = "";
		for ($i = 0; $i < $length; $i++) {
			$str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
		}
		return $str;
	}

	//获取调用jsapi需要的ticket
	public function getJsApiTicket() {
		// jsapi_ticket 应该全局存储与更新，以下代码以写入到文件中做示例
		$data = json_decode(file_get_contents("jsapi_ticket.json"));
		// var_dump($data);
		if (!$data || $data->expire_time < time()) {
			$accessToken = $this->getAccessToken();
			$url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=" . $accessToken;
			$res = json_decode($this->http->httpGet($url));
			$ticket = $res->ticket;
			if ($ticket) {
				$data->expire_time = time() + 7000;
				$data->jsapi_ticket = $ticket;
				$fp = fopen("jsapi_ticket.json", "w");
				fwrite($fp, json_encode($data));
				fclose($fp);
			}
		} else {
			$ticket = $data->jsapi_ticket;
		}

		return $ticket;
	}

	// 获取服务器端保存的access_token
	// __DIR__代表当前文件本文件所在的目录
	// 在保存进入静态文件保存时，必须json_encode转码
	public function getAccessToken() {
		$path = __DIR__ . "/access_token.json";
		$string = file_get_contents($path);
		$data = json_decode($string);
		if ($data->expire_time < time()) {
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->appId . "&secret=" . $this->appSecret;
			$res = $this->http->httpGet($url);
			$res = json_decode($res);
			$access_token = $res->access_token;
			if ($access_token) {
				// 这里必须使用绝对路径，否则在ubuntu中读取不到
				$fp = fopen($path, "w");
				$data->expire_time = time() + 7000;
				$data->access_token = $access_token;
				$end = fwrite($fp, json_encode($data));
				fclose($fp);
				return $access_token;
			} else {
				// $this->log->error('没有解析到token，请排查');
			}
		} else {
			return $data->access_token;
		}
	}

	//  获取带参数的二维码的ticket
	public function getQrcodeTicket($token, $openid, $subject) {
		$url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $token;
		$str = $openid . '@' . $subject;
		$data = array(
			'action_name' => 'QR_LIMIT_STR_SCENE',
			'action_info' => array("scene" => array("scene_str" => $str)),
		);
		// $this->log->info('传递的参数为:' . json_encode($data));
		$res = $this->http->httpsPost($url, json_encode($data));
		// $this->log->info('请求ticket，返回的结果为:' . $res);
		return json_decode($res)->ticket;
	}

	// 后台生成等待分享的二维码
	// public function createQrcode($openid, $subject) {
	// 	$this->log->info('开始获取二维码，本次openid为' . $openid);
	// 	$this->log->info('开始获取二维码，本次subject为' . $subject);
	// 	$token = $this->getAccessToken();
	// 	$ticket = $this->getQrcodeTicket($token, $openid, $subject);
	// 	$url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $ticket;
	// 	$this->download($url, $openid, $subject);
	// }

	// 保存二维码到本地服务器上,二维码命令 openid@subject.jpg
	// public function download($url, $openid, $subject) {
	// 	$name = $openid . '@' . $subject;
	// 	$path = '../img/' . $name . '.jpg'; //文件路径和文件名

	// 	if (file_exists($path)) {
	// 		$this->log->info('下载二维码，但是已经存在，name=' . $name);
	// 		// $this->mergeImg($openid,$subject);
	// 	} else {
	// 		$s = file_get_contents($url);
	// 		$res = file_put_contents($path, $s);
	// 		// if($res){
	// 		//    $this->mergeImg($openid,$subject);
	// 		// }
	// 	}

	// }

	// 合成二维码和背景图片
	// $name是二维码
	// public function mergeImg($openid, $subject) {

	// 	// 如果文件已经存在，就不要合并了
	// 	if (file_exists('../img/'+$openid . '@' . $subject+'.png')) {
	// 		$this->log->info('合并的二维码存在，openid=' . $openid);
	// 		return false;
	// 	}

	// 	$QR = "../img/" . $openid . '@' . $subject . ".jpg"; //二维码
	// 	$bk = './img/share.jpg'; //背景图片  由系统指定

	// 	$QR = imagecreatefromstring(file_get_contents($QR)); //open picture source
	// 	$this->log->info('载入二维码成功');
	// 	$bk = imagecreatefromstring(file_get_contents($bk)); //open picture source
	// 	$this->log->info('载入背景成功');
	// 	$QR_width = 430;
	// 	$QR_height = 430;
	// 	imagecopyresampled($bk, $QR, 200, 600, 0, 0, 355, 355, $QR_width, $QR_height); // mixed picture
	// 	$result_png = $openid . '@' . $subject . ".png"; // file name
	// 	$file = '../img/' . $result_png;
	// 	$res = imagepng($bk, $file); //output picture
	// 	$this->log->info('合并结果：' . json_decode($res));
	// 	if ($res) {
	// 		$this->log->success('图片合并成功');
	// 	} else {
	// 		$this->log->error('图片合并失败');
	// 	}
	// }

	// 给公众号设置菜单
	// public function setMenu($menu) {
	// 	echo 'access_token:' . $this->getAccessToken();
	// 	print_r($menu);
	// 	$url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token=" . $this->getAccessToken();
	// 	return $this->http->httpsPost($url, $menu);
	// }

	// 通过code获取用户基本信息
	public function getBaseInfoByCode($code) {
		$url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . $this->appId . '&secret=' . $this->appSecret . '&code=' . $code . '&grant_type=authorization_code';
		$res = json_decode($this->http->httpGet($url));
		var_dump($res);
	}

	// 小程序获取openid
	public function getOpenid($jscode) {
		$url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $this->appId . '&secret=' . $this->appSecret . '&js_code=' . $_GET['code'] . '&grant_type=authorization_code';
		$res = $this->http->httpGet($url);
		echo $res;
		// $this->sendData($res, '', '200');
	}

	// 根据前端传递的三个参数，获取用户的openid
	// 不需要token，所以没有次数限制
	public function getMyOpenid() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$this->mypost = json_decode($rws_post);
		$url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $this->mypost->appId . '&secret=' . $this->mypost->appSecret . '&js_code=' . $this->mypost->code . '&grant_type=authorization_code';
		$res = $this->http->httpGet($url);

		$this->sendData(json_decode($res), $res, 200);
	}

	public function sendData($res, $sql = '', $code = '') {
		$data['data'] = $res;
		$data['code'] = $code;
		$data['sql'] = $sql;
		echo json_encode($data);
	}

	//微信模板消息接口
	// public function push($data) {
	// 	$url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=' . $this->getAccessToken();
	// 	$this->log->info($data);
	// 	$res = $this->http->httpsPost($url, $data);
	// 	$this->log->info(' 微信推送结果：' . json_encode($res));
	// 	echo $res;
	// }

	public function test() {
		echo $this->getAccessToken();
	}

}
