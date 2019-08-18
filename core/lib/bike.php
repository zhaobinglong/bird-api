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
include 'http.class.php';

// 函数设置默认参数
// public function list($id,$page = 1,$size = 10)

class bike {

	// 数据库句柄
	private $db;
	public $post;
	public $http;
	// 构造函数，将pdo句柄传递给类
	public function __construct($db) {
		$this->db = $db;
		$this->http = new http();
		$rws_post = '';
		if (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
			$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
			$this->mypost = json_decode($rws_post);
		}
	}

	// 为每个组队页面生成二维码
	public function getLittleImg() {
		header("Content-Type:image/png");
		header("Accept-Ranges:bytes");

		$url = 'pages/join/join';
		$data = array(
			'scene' => $_GET['id'],
			'page' => $url,
		);

		$url = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=' . $this->getAccessToken();
		echo $this->http->httpsPostNoCode($url, $data);
	}

	public function getAccessToken() {
		$appId = 'wxca1f578c4d6201ea';
		$appSecret = '95bb0015fc6c1cf473a8890ff40a90b8';
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appId . "&secret=" . $appSecret;
		$res = $this->http->httpGet($url);
		$res = json_decode($res);
		return $res->access_token;
	}

	// 用户发布组队帖子
	public function push() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);

		if (isset($mypost->id)) {
			$sql = "update ershou set title='" . $mypost->title . "',cont='" . $mypost->cont . "',imgs='" . serialize($mypost->imgs) . "',imgs_detail='" . serialize($mypost->imgs_detail) . "',symbol='" . $mypost->symbol . "',college='" . $mypost->college . "',address='" . $mypost->address . "',city='" . $mypost->city . "',wechat='" . $mypost->wechat . "',price='" . $mypost->price . "',old_price='" . $mypost->old_price . "',is_new='" . $mypost->is_new . "',level='" . $mypost->level . "',classify='" . $mypost->classify . "',category='" . $mypost->category . "',status='" . $mypost->status . "', updatetime='" . time() . "' where id='" . $mypost->id . "'";
		} else {

			$sql = "insert into ershou(openid,title,cont,imgs,imgs_detail,symbol,college,address,city,wechat,price,classify,category,createtime,updatetime,belong) value('" . $mypost->openid . "','" . $mypost->title . "','" . $mypost->cont . "','" . serialize($mypost->imgs) . "','" . serialize($mypost->imgs_detail) . "','" . $mypost->symbol . "','" . $mypost->college . "','" . $mypost->address . "','" . $mypost->city . "','" . $mypost->wechat . "','" . $mypost->price . "','" . $mypost->classify . "','" . $mypost->category . "','" . time() . "','" . time() . "','" . $mypost->belong . "')";
		}
		$res = $this->db->dql($sql);
		$this->sendData($res, $sql);
	}

	// 获取列表
	public function getList() {
		if (!isset($this->mypost->status)) {
			$this->mypost->status = '1';
		}
		$sql = 'select e.id,e.openid,e.title,e.cont,e.college,e.imgs,e.imgs_detail,e.symbol,e.price,e.old_price,e.status,e.city,e.address,e.phone,e.level,e.classify,e.category,e.message,e.views,e.liked,e.updatetime,u.nickName,u.avatarUrl,u.status as user_status from ershou as e  left join user as u on e.openid = u.openid where (e.openid="' . $this->mypost->openid . '" or "' . $this->mypost->openid . '"="") and (e.city="' . $this->mypost->city . '" or "' . $this->mypost->city . '"="") and (e.cont like "%' . $this->mypost->value . '%" or e.city like "%' . $this->mypost->value . '%" or e.address like "%' . $this->mypost->value . '%" or e.title like "%' . $this->mypost->value . '%") and (e.status="' . $this->mypost->status . '" or "' . $this->mypost->status . '"="")  and e.belong="bikepark" and e.openid!="" order by updatetime desc limit ' . $this->mypost->page * 20 . ',20';

		$res = $this->db->dql($sql);
		$data = array();

		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$row['imgs'] = unserialize($row['imgs']);
			$row['address'] = json_decode($row['address']);
			array_push($data, $row);
		}

		$this->sendData($data, $sql);
	}

	// 获取内容详情
	public function getDetail() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);
		$sql = 'select e.id,e.openid,e.title,e.cont,e.college,e.imgs,e.imgs_detail,e.symbol,e.price,e.old_price,e.address,e.phone,e.wechat,e.nation,e.city,e.is_new,e.level,e.classify,e.category,e.views,e.updatetime,e.status,u.nickName,u.avatarUrl from ershou e left join  user u on u.openid = e.openid where e.id="' . $mypost->id . '"';
		$res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
		$res['imgs'] = unserialize($res['imgs']);
		$res['address'] = json_decode($res['address']);
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

}