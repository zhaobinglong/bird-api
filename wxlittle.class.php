<?php

// 微信小程序类文件

include 'log.class.php';
include 'http.class.php';
include 'config.php';

class wxlittle {
	private $appId;
	private $appSecret;
	public $log;
	public $http;

	public function __construct($appId, $appSecret) {
		$this->appId = APPID;
		$this->appSecret = APPSECRET;
		$this->log = new log(LOG_PATH);
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

	// 本地获取access_token
	public function getAccessToken() {
		$data = json_decode(file_get_contents("access_token.json"));
		$this->log->info('本地获取access_token过期时间：' . $data->expire_time);
		$this->log->info('当前系统时间：' . time());

		if ($data->expire_time < time()) {
			$this->log->info('token过期，重新请求');
			$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $this->appId . "&secret=" . $this->appSecret;
			$res = $this->http->httpGet($url);
			$this->log->info('向微信请求token，返回的结果是' . $res);
			$res = json_decode($res);
			if (!$res) {
				$this->log->error('获取accesstoken失败，api返回的结果是是null');
				return false;
			}
			$access_token = $res->access_token;
			if ($access_token) {
				$data->expire_time = time() + 7000;
				$this->log->info('当前系统时间：' . time());
				$this->log->info('保存后的时间：' . $data->expire_time);
				$data->access_token = $access_token;
				$fp = fopen("access_token.json", "w");
				$str = '{"expire_time":' . $data->expire_time . ',"access_token":' . $data->access_token . '}';
				$end = fwrite($fp, $str);
				fclose($fp);
				$this->log->success('accesstoken更新，本次token是' . $access_token);
			} else {
				$this->log->error('没有解析到token，请排查');
			}
		} else {
			$access_token = $data->access_token;
			$this->log->info('token没有过期，直接返回');
			$this->log->info('过期时间：' . $data->expire_time);
			$this->log->info('当前时间：' . time());
		}
		return $access_token;
	}

	// 小程序模板消息发送接口
	public function push($data) {
		$this->log->info(' 微信推送数据：' . json_encode($data));
		$url = 'https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=' . $this->getAccessToken();
		$res = $this->http->httpsPost($url, $data);
		$this->log->info(' 微信推送结果：' . json_encode($res));
		return $res;

	}

	// 获取小程序码
	// 这里微信小程序返回二进制，咋搞
	// 这里把参与都交给前端比较好，前端控制参数和页面，后端就负责请求图片就好了
	public function getLittleImg($id, $page) {
		$url = '';
		if ($page == 'index') {
			$url = 'pages/date/index/index';
		} else {
			$url = 'pages/date/detail/index';
		};
		$data = array(
			'scene' => $id,
			'page' => $url,
		);

		$url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $this->getAccessToken();
		$res = $this->http->httpsPost($url, $data);
		return $res;
	}

	// 逆地址解析，根据经纬度，返回经纬度附近的地址
	// $key应该放在配置
	public function getLocation($lat, $lng) {
		$url = 'http://apis.map.qq.com/ws/geocoder/v1/?location=' . $lat . ',' . $lng . '&key=' . MAPKEY;

		return $this->http->httpsGet($url);
	}

	// 小程序获取openid
	public function getOpenid($jscode) {
		$url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $this->appId . '&secret=' . $this->appSecret . '&js_code=' . $jscode . '&grant_type=authorization_code';
		$res = $this->http->httpGet($url);
		return $res;
	}

}
