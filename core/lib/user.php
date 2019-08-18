<?php

// require __DIR__ . '/errcode.class.php';

// 返回pdo最后一次插入的自增id
// $this->db->lastInsertId()

// 数据库读写四部曲
// 拼写sql  $sql = 'select * from `user` where `openid` =:openid '
// 预处理 $stmt = $this->db->prepare($sql);
// 绑定   $stmt->bindParam(':openid',$openid);
// 执行 $stmt->execute();

// 获取结果集的方式
// 获取单条结果集 $res = $stmt->fetch(PDO::FETCH_ASSOC);
// 获取所有结果集 $res = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 函数设置默认参数
// public function list($id,$page = 1,$size = 10)

include 'http.class.php';

class user {

	// 数据库句柄
	private $db;
	public $http;
	// 构造函数，将pdo句柄传递给类
	public function __construct($db) {
		$this->db = $db;
		$this->http = new http();
	}

	// 用户注册，请使用md5加密

	// 用户登录
	public function login() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$this->mypost = json_decode($rws_post);
		$sql = 'select * from user where account="' . $this->mypost->username . '" and pwd="' . $this->mypost->password . '" and belong="' . $this->mypost->belong . '" limit 1';
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			array_push($data, $row);
		}
		$this->sendData($data, $sql);
	}

	// 根据用户openid，获取用户详细信息
	public function getUser() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$this->mypost = json_decode($rws_post);
		$sql = 'select * from user where openid ="' . $this->mypost->openid . '"';
		$res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
		$this->sendData($res, $sql);
	}

	// 更新用户信息
	public function editUser() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$this->mypost = json_decode($rws_post);
		if (!isset($this->mypost->phone)) {
			$this->mypost->phone = '';
		}
		if (!isset($this->mypost->status)) {
			$this->mypost->status = '1';
		}
		if (!isset($this->mypost->wechat)) {
			$this->mypost->wechat = '';
		}
		$sql = 'insert into user(openid,avatarUrl,nickName,phone,createtime,belong) value("' . $this->mypost->openid . '","' . $this->mypost->avatarUrl . '","' . $this->mypost->nickName . '","' . $this->mypost->phone . '","' . time() . '", "' . $this->mypost->belong . '") ON DUPLICATE KEY UPDATE avatarUrl="' . $this->mypost->avatarUrl . '",nickName="' . $this->mypost->nickName . '", phone="' . $this->mypost->phone . '",wechat="' . $this->mypost->wechat . '", belong="' . $this->mypost->belong . '",status="' . $this->mypost->status . '"';
		$res = $this->db->dql($sql);
		$this->sendData($res, $sql);
	}

	// 用户发布组队申请，该api给单车公园用
	public function push() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);

		if (isset($mypost->id)) {
			$sql = "update ershou set title='" . $mypost->title . "',cont='" . $mypost->cont . "',imgs='" . serialize($mypost->imgs) . "',phone='" . $mypost->phone . "',college='" . $mypost->college . "',address='" . json_encode($mypost->address, JSON_UNESCAPED_UNICODE) . "',city='" . $mypost->city . "',views='" . $mypost->views . "',price='" . $mypost->price . "',old_price='" . $mypost->old_price . "',is_new='" . $mypost->is_new . "',level='" . $mypost->level . "',classify='" . $mypost->classify . "',category='" . $mypost->category . "',status='" . $mypost->status . "', updatetime='" . time() . "' where id='" . $mypost->id . "'";
		} else {

			$sql = "insert into ershou(openid,title,cont,imgs,college,address,city,phone,views,createtime,updatetime,belong) value('" . $mypost->openid . "','" . $mypost->title . "','" . $mypost->cont . "','" . serialize($mypost->imgs) . "','" . $mypost->college . "','" . json_encode($mypost->address, JSON_UNESCAPED_UNICODE) . "','" . $mypost->city . "','" . $mypost->phone . "','" . $mypost->views . "','" . time() . "','" . time() . "','bikepark')";
		}
		$res = $this->db->dql($sql);
		$this->sendData($res, $sql);
	}

	// 获取特定来源的用户
	// 可选参数 belong openid
	public function getList() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$this->mypost = json_decode($rws_post);
		$sql = 'select * from user where (openid="' . $this->mypost->openid . '" or "' . $this->mypost->openid . '" ="") and (belong="' . $this->mypost->belong . '" or "' . $this->mypost->belong . '" ="") ';

		$res = $this->db->dql($sql);
		$data = array();

		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			array_push($data, $row);
		}

		$this->sendData($data, $sql);
	}

	// 为用户生成特定的二维码图片
	public function getLittleImg() {
		header("Content-Type:image/png");
		header("Accept-Ranges:bytes");

		$url = 'pages/form/form';
		$data = array(
			'scene' => $_GET['id'],
			'page' => $url,
		);

		$url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $this->getAccessToken();
		echo $this->http->httpsPostNoCode($url, $data);
	}

	public function getAccessToken() {
		$appId = 'wx27b965480e29803f';
		$appSecret = 'da7ae43fff8157451b821a13ac679355';
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appId . "&secret=" . $appSecret;
		$res = $this->http->httpGet($url);
		$res = json_decode($res);
		return $res->access_token;
	}

	//
	public function comment() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);
		$sql = '';
		if (isset($mypost->status)) {
			$sql = 'update message set status="' . $mypost->status . '" where  ershou="' . $mypost->id . '" and fromopenid="' . $mypost->fromopenid . '"';
		} else {
			$sql = 'insert into message(ershou,toopenid,fromopenid,cont,tag,status,createtime) value("' . $mypost->id . '","' . $mypost->toopenid . '","' . $mypost->fromopenid . '","' . $mypost->cont . '","' . $mypost->tag . '","1","' . time() . '")';
		}
		$res = $this->db->dql($sql);
		$this->sendData($res, $sql);
	}

	// 参数1：sql执行成功还是失败
	public function sendData($res, $sql = '') {
		$data['data'] = $res;
		$data['sql'] = $sql;
		$data['code'] = 200;
		echo json_encode($data);
	}

	public function throwError() {
		throw new Exception('openid已经存在', '0');
	}

	// 类私有函数，检查用户是否已经存在，私有方法
	private function _check() {

	}
}