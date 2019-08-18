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

class nuoya {

	// 数据库句柄
	private $db;
	public $post;
	public $from = 'ztd';
	public $openid = '0tnczsoyer39hlvwj85vpi5l80re';

	// 构造函数，将pdo句柄传递给类
	public function __construct($db) {
		$this->db = $db;
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$this->mypost = json_decode($rws_post);
	}

	public function apply() {
		$sql = "select openid from user where openid='" . $this->mypost->idcard . "' ";
		$res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
		if ($res) {
			$this->sendData(false, $sql);
		} else {
			$sql = 'insert into user(openid,nickName,phone,college,ad,formId,wechat,weibo,douyin,belong) value("' . $this->mypost->idcard . '","' . $this->mypost->name . '","' . $this->mypost->phone . '","' . $this->mypost->company . '","' . $this->mypost->company_address . '","' . $this->mypost->address . '","' . $this->mypost->wechat . '","' . $this->mypost->money . '","' . $this->mypost->pwd . '","nuoya") ';
			$res = $this->db->dql($sql);
			$this->sendData($res, $sql);
		}
	}

	public function getUserList() {
		$sql = "select * from user where belong='nuoya' and status != '0'";
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			array_push($data, $row);
		}
		$this->sendData($data);
	}

	public function delUser() {
		$sql = "update user set status = '0' where  openid = '" . $_GET['id'] . "' and belong='nuoya'";
		$res = $this->db->dql($sql);
		$this->sendData($res);
	}

	// 参数1：sql执行成功还是失败
	public function sendData($res, $sql = '') {
		$data['data'] = $res;
		$data['code'] = 200;
		echo json_encode($data);
	}

	// test
	public function test() {
		$sql = 'select id from cart where openid="oxhtp5Os1x4gcYC2fxcWy4127FkQ" and id="6"';
		$res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
		var_dump($res);
		if ($res) {
			echo 'ok';
		} else {
			echo 'no res';
		}
	}

}