<?php

include 'http.class.php';

// 刷单接口
class shua {

	// 数据库句柄
	private $db;
	public $http;

	public function __construct($db) {
		$this->db = $db;
		$this->http = new http();
	}

	//生成指定长度随机字符串
	public function get_rand($len = 6) {
		$chars = "abcdefghijklmnopqrstuvwxyz0123456789";
		mt_srand(10000000 * (double) microtime());
		for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $len; $i++) {
			$str .= $chars[mt_rand(0, $lc)];
		}
		return $str;
	}

	// 用户注册，请使用md5加密

	// 用户登录，手机号码+密码
	public function login() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$this->mypost = json_decode($rws_post);
		$sql = 'select * from user where phone="' . $this->mypost->phone . '" and pwd="' . $this->mypost->password . '" limit 1';
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			array_push($data, $row);
		}
		$this->sendData($data, $sql);
	}

	// 用户注册，手机号码+密码
	public function register() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$this->mypost = json_decode($rws_post);
		// insert into coupon(openid, num, max_price, channel, createtime) select 'oHDjI5QkkTLAdhkhYnw88rBE54ys','100','1000','oHDjI5QkkTLAdhkhYnw88rBE54ys','1559039855'from DUAL where not exists (select id from coupon where openid='oHDjI5QkkTLAdhkhYnw88rBE54ys' and num='100' and max_price='1000')
		$openid = $this->get_rand(28); // 生成一个28位长的随机字符串，因为user表中openid是主键
		$sql = 'insert into user(openid, phone, pwd, belong) select "' . $openid . '", "' . $this->mypost->phone . '", "' . $this->mypost->password . '","shuadan" from DUAL where not exists (select id from user where phone="' . $this->mypost->phone . '" and belong="shuadan")';
		$res = $this->db->dql($sql);
		$this->sendData($res, $sql);
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

			$sql = "insert into ershou(openid,title,cont,imgs,college,address,city,phone,views,createtime,updatetime,belong) value('" . $mypost->openid . "','" . $mypost->title . "','" . $mypost->cont . "','" . serialize($mypost->imgs) . "','" . $mypost->college . "','" . json_encode($mypost->address, JSON_UNESCAPED_UNICODE) . "','" . $mypost->city . "','" . $mypost->phone . "','" . $mypost->views . "','" . time() . "','" . time() . "', '" . $mypost->belong . "')";
		}
		$res = $this->db->dql($sql);
		$this->sendData($res, $sql);
	}

	// 获取任务列表
	public function getList() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$this->mypost = json_decode($rws_post);
		$sql = 'select * from ershou where (openid="' . $this->mypost->openid . '" or "' . $this->mypost->openid . '" ="") and (belong="' . $this->mypost->belong . '" or "' . $this->mypost->belong . '" ="") order by updatetime desc limit ' . $this->mypost->page * 20 . ',20';
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			array_push($data, $row);
		}
		$this->sendData($data, $sql);
	}

	public function getDetail($get) {
		$sql = 'select e.id,e.openid,e.title,e.cont,e.college,e.imgs,e.imgs_detail,e.symbol,e.price,e.old_price,e.address,e.wechat,e.nation,e.city,e.is_new,e.level,e.classify,e.category,e.updatetime,e.status,u.nickName,u.avatarUrl from ershou e left join  user u on u.openid = e.openid where e.id="' . $_GET['id'] . '"';
		$res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
		$res['imgs'] = unserialize($res['imgs']);
		$res['imgs_detail'] = unserialize($res['imgs_detail']);
		$this->sendData($res, $sql);
		// 请求完毕后，阅读数加1
		$sql = 'update ershou set views=views+1 where id = "' . $_GET['id'] . '"';
		$this->db->dql($sql);
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