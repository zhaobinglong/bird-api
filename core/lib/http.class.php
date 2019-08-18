<?php
// header("Content-type: text/html; charset=utf-8");
//http类
class http {

	//构造函数
	function __construct() {

	}

	// http get请求
	public function httpGet($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($ch, CURLOPT_TIMEOUT, 15);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
		$res = curl_exec($ch);
		curl_close($ch);
		return $res;

	}

	// 模拟发送http post请求
	public function httpPost($url, $data = null) {

		$postData = json_encode($data, JSON_UNESCAPED_UNICODE);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // stop verifying certificate
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		$res = curl_exec($curl);
		curl_close($curl);
		return $res;
	}

	// 模拟发送https post请求
	public function httpsPost($url, $data = null) {

		$postData = json_encode($data);
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // stop verifying certificate
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		$res = curl_exec($curl);
		curl_close($curl);
		return $res;
	}

	public function httpsPostNoCode($url, $data = null) {

		$postData = $data;
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // stop verifying certificate
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $postData);
		if (ini_get('open_basedir') == '' && ini_get('safe_mode' == 'Off')) {
			curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
		}
		$res = curl_exec($curl);
		curl_close($curl);
		return $res;
	}
	// 模拟发送https get请求
	public function httpsGet($url) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //禁止直接显示获取的内容 重要
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); //
		curl_setopt($ch, CURLOPT_HEADER, 0);
		$res = curl_exec($ch);
		return $res;
	}

	// 重新封装一个http接口，用来给腾讯云短信接口调用
	// public function tencentHttpsPost($url, $post = null) {
	// 	$ch = curl_init();
	// 	curl_setopt($ch, CURLOPT_URL, $url);
	// 	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	// 	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

	// 	$httpheader[] = "Accept:application/json";
	// 	$httpheader[] = "Accept-Encoding:gzip,deflate,sdch";
	// 	$httpheader[] = "Accept-Language:zh-CN,zh;q=0.8";
	// 	$httpheader[] = "Connection:close";
	// 	curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
	// 	if ($post) {
	// 		curl_setopt($ch, CURLOPT_POST, 1);
	// 		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	// 	}

	// 	curl_setopt($ch, CURLOPT_TIMEOUT, 3);
	// 	curl_setopt($ch, CURLOPT_ENCODING, "gzip");
	// 	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	// 	$ret = curl_exec($ch);
	// 	curl_close($ch);
	// 	return $ret;
	// }

}
