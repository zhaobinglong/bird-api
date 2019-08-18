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

class content {

	// 数据库句柄
	private $db;
	public $http;
	// 构造函数，将pdo句柄传递给类
	public function __construct($db) {
		$this->db = $db;
	}

	// 获取内容详情
	public function getDetail() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);
		$sql = 'select e.id,e.openid,e.title,e.cont,e.college,e.imgs,e.imgs_detail,e.symbol,e.price,e.old_price,e.address,e.phone,e.wechat,e.nation,e.city,e.is_new,e.level,e.classify,e.category,e.views,e.updatetime,e.status,u.nickName,u.avatarUrl from ershou e left join  user u on u.openid = e.openid where e.id="' . $mypost->id . '"';
		$res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
		$res['imgs'] = unserialize($res['imgs']);
		$res['imgs_detail'] = unserialize($res['imgs_detail']);
		$this->sendData($res, $sql);
	}

	// 更改一篇内容状态
	public function updateStatus() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);
		$sql = "update ershou set status='" . $mypost->status . "' where id='" . $mypost->id . "'";
		$res = $this->db->dql($sql);
		$this->sendData($res, $sql);
	}

	// 获取我报名的内容列表
	public function getMyJoin() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);

		$sql = 'select e.id,e.openid,e.title,e.cont,e.college,e.imgs,e.imgs_detail,e.symbol,e.price,e.old_price,e.status,e.city,e.address,e.phone,e.level,e.classify,e.category,e.message,e.views,e.liked,e.updatetime from ershou as e  right join message as m on e.id = m.ershou where m.fromopenid="' . $mypost->openid . '"  and e.belong="' . $mypost->belong . '"  and m.status="1" order by updatetime desc limit ' . $mypost->page * 20 . ',20 ';

		$res = $this->db->dql($sql);
		$data = array();

		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$row['imgs'] = unserialize($row['imgs']);
			array_push($data, $row);
		}

		$this->sendData($data, $sql);
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