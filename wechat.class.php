<?php

include 'db.class.php';
include 'http.class.php';
include 'log.class.php';
include 'config.php';
// 公众号接口类
// 和公众号有关的接口调用
class wechat {
	public $device_type;
	public $appId;
	public $appSecret;
	public $db;
	public $http;
	public $log;
	public $token;
	public $key;

	// 构造函数,初始化token
	public function __construct() {
		$this->db = new DB();
		$this->http = new http();
		$this->log = new log(LOG_PATH);
		$this->appId = SAIBO_APPID;
		$this->appSecret = SAIBO_APPSECRET;
		$this->token = SAIBO_TOKEN;
		$this->key = SAIBO_AES;
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

		$token = $this->key;
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

	// 保存关注者
	// $from会带有多余的字符串，需要过滤
	// $from=qrscene_+openid+@+subject
	// $openid 关注者的openid
	// $from 分享者的opened+subject
	public function saveUser($openid, $toUsername, $from) {

		$sql = 'select openid from user where openid="' . $openid . '" ';

		$arr = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);

		$from = str_replace("qrscene_", "", $from);
		$fromArr = explode("@", $from);

		// 如果用户已经关注公众号 不再写入关注来演信息
		if ($arr) {
			// 用户已经存在,该用户已经关注公众号，不再插入来源openid
			// $sql='update user set from_openid="'.$fromArr[0].'"where openid="'.$openid.'"';
			// $this->db->dql($sql);
		} else {
			$sql = 'insert into user(openid,from_openid,from_subject,subscribe_date) values("' . $openid . '","' . $fromArr[0] . '","' . $fromArr[1] . '",' . time() . ' )';
			$this->log->info($sql);
			$resout = $this->db->dql($sql);

		}

	}

	// 扫描后推送的消息
	public function pushAfterScan($fromUsername, $toUsername, $eventkey) {
		$from = str_replace("qrscene_", "", $eventkey);
		$fromArr = explode("@", $from);
		$pushArr = $this->getPushCont($fromArr[1]);
		$this->pushAfterThree($fromArr[0], $fromArr[1], $pushArr['title']);

		$time = time();
		$title = $pushArr['push_after_scan']['title'];
		$desc = $pushArr['push_after_scan']['cont'];
		$img = BASE_IMG . $pushArr['push_after_scan']['url'];
		$url = SUBJECT_PATH . $fromArr[1];

		$textTpl = "<xml>
                    <ToUserName><![CDATA[%s]]></ToUserName>
                    <FromUserName><![CDATA[%s]]></FromUserName>
                    <CreateTime>%s</CreateTime>
                    <MsgType><![CDATA[news]]></MsgType>
                    <ArticleCount>1</ArticleCount>
                    <Articles>
                    <item>
                    <Title><![CDATA[%s]]></Title>
                    <Description><![CDATA[%s]]></Description>
                    <PicUrl><![CDATA[%s]]></PicUrl>
                    <Url><![CDATA[%s]]></Url>
                    </item>
                    </Articles>
                    </xml>";

		$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $title, $desc, $img, $url);
		return $resultStr;
	}

	// 获取和课程关联的推送信息
	public function getPushCont($subject) {
		$sql = 'select push_after_scan,title from subject where id="' . $subject . '" ';
		$this->log->info($sql);
		$arr = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
		$arr['push_after_scan'] = unserialize($arr['push_after_scan']);
		return $arr;
	}

	// 订阅后推送一条文字消息
	public function pushAfterSubscribe($fromUsername, $toUsername, $eventkey) {
		$this->pushAfterScan($fromUsername, $toUsername, $eventkey);
		// $textTpl = "<xml>
		//             <ToUserName><![CDATA[%s]]></ToUserName>
		//             <FromUserName><![CDATA[%s]]></FromUserName>
		//             <CreateTime>%s</CreateTime>
		//             <MsgType><![CDATA[%s]]></MsgType>
		//             <Content><![CDATA[%s]]></Content>
		//             </xml>";
		// $time = time();
		// $msgType = "text";
		// $contentStr="欢迎关注，这里是真题研究所";
		// $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
		// return  $resultStr;
	}

	public function pushAfterThree($openid, $subject, $title) {
		// 用户分享的有三个好友关注，获得课程
		$url = 'http://examlab.cn/wechatClassApi/push.php?action=pushAfterThree&openid=' . $openid . '&subject=' . $subject . '&title=' . $title;
		$this->http->httpGet($url);
	}

	// 三个奖励之后奖励课程
	public function buyAfterThree($openid, $subject) {
		$url = 'http://examlab.cn/wechatClassApi/user.php?code=buyAfterThree';
		$data = array("openid" => $openid, "subject" => $subject);
		$data_string = json_encode($data);
		$this->http->httpsPost($url, $data_string);
	}

	// 微信服务器响应
	public function responseMsg() {

		// 接收微信推送的xml
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

		// xml有效
		if (!empty($postStr)) {

			// 将xml解析为一个对象
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);

			//判断事件类型
			$msgType = $postObj->MsgType;
			$fromUsername = $postObj->FromUserName;
			$toUsername = $postObj->ToUserName;
			$time = time();
			// 消息分类
			switch ($msgType) {

			// 事件消息
			case "event":
				$event = $postObj->Event;
				$eventkey = $postObj->EventKey;
				switch ($event) {

				// 用户关注帐号事件推送
				case "subscribe":
					echo $this->pushAfterScan($fromUsername, $toUsername, $eventkey);
					$this->saveUser($fromUsername, $toUsername, $eventkey);
					break;

				// 用户扫描事件推送
				case "SCAN":
					echo $this->pushAfterScan($fromUsername, $toUsername, $eventkey);
					break;

				}
				break;

			// 接收用户地理位置
			case "location":
				echo '接收到一个地址位置';
				break;
			// 默认
			default:
				echo "";
				break;
			}

			// xml无效
		} else {
			echo "";
			exit;
		}
	}

}

?>