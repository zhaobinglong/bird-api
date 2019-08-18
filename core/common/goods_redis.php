<?php
require_once 'cache.php';

class GoodsRedis {
	/**
	 * @var
	 * 商品相关redis
	 */
	private $conn;
	public $log;
	//评论数 商品id hash
	private $comment_num = 'comment_num_%s';

	//转发数 hash openid time
	private $trans_num = "trans_num_%s";

	public function __construct() {
		$this->conn = (new Cache())->main();
		$this->log = '/home/wwwroot/default/tmp/unibbs.log';
	}

	public function newAddCommentNum($goods_id, $comment_id, $info) {
		$key = sprintf($this->comment_num, $goods_id);
		$this->conn->hset($key, $comment_id, json_encode($info, JSON_UNESCAPED_UNICODE));
	}

	public function getCommentNum($goods_id) {
		$key = sprintf($this->comment_num, $goods_id);
		$num = $this->conn->hLen($key);
		return $num;
	}

	// 获取手机验证码
	public function getPhoneCode($phone) {
		return $this->conn->get($phone);
	}

	// 设置手机验证码 先不设置有有效期
	public function setPhoneCode($key, $value) {
		$check = $this->conn->set($key, $value);
		file_put_contents($this->log, json_encode('设置验证码：' . $key . ':' . $value . '，设置结果：' . $check, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
		return $check;
	}

	// 给key加锁，不考虑过期
	public function add_nx_lock($key) {
		$check = $this->conn->setnx($key, 1);
		// $check = $this->conn->set($key, 1, array('nx', 'ex' => 5);
		return $check;
	}

	// 给key加锁，5s的过期
	public function add_nx_lock_ex($key, $value) {

		$check = $this->conn->set($key, $value, array('nx', 'ex' => 10));
		file_put_contents($this->log, json_encode('设置验证码：' . $key . ':' . $value . '，设置结果：' . $check, JSON_UNESCAPED_UNICODE) . PHP_EOL, FILE_APPEND);
		return $check;
	}
}