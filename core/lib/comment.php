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

class comment {

	// 数据库句柄
	private $db;
	public $http;
	// 构造函数，将pdo句柄传递给类
	public function __construct($db) {
		$this->db = $db;
		$this->http = new http();
	}

	// 获指定评论
	public function getComment() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);
		$sql = 'select * from comment where id="' . $mypost->id . '" limit 1';
		$res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
		$res['imgs'] = unserialize($res['imgs']);
		$this->sendData($res, $sql);
	}

	// 用户评论（插入或者更新）
	public function userComment() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);
		$sql = '';
		if (isset($mypost->id)) {
			$sql = "update comment set imgs = '" . serialize($mypost->imgs) . "', content = '" . $mypost->content . "' where id = '" . $mypost->id . "' ";
		} else {
			$sql = "insert into comment(openid, main_id, order_id, content, imgs, status, createtime) value('" . $mypost->openid . "','" . $mypost->main_id . "','" . $mypost->order_id . "','" . $mypost->content . "','" . serialize($mypost->imgs) . "','1','" . time() . "') ";
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