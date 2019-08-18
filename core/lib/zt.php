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

class zt {

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

	// 后台用户登录
	public function login() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$this->mypost = json_decode($rws_post);
		$sql = 'select * from user where account="' . $this->mypost->username . '" and pwd="' . $this->mypost->password . '" and belong="' . $this->mypost->belong . '" limit 1';
		$res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
		$res['formId'] = unserialize($res['formId']);
		$this->sendData($res, $sql);
	}

	// 根据belong，获取用户列表
	// formId中为权限数组
	// status = 0 用户已经被删除，不要返回
	public function getUserList() {
		$sql = 'select * from user where belong ="' . $this->mypost->belong . '" and status!="0"';
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$row['formId'] = unserialize($row['formId']);
			array_push($data, $row);
		}
		$this->sendData($data, $sql);
	}

	// 根据用户openid，获取用户详细信息
	public function getUser() {
		$sql = 'select * from user where openid ="' . $this->mypost->openid . '"';
		$res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
		$this->sendData($res, $sql);
	}

	// 编辑用户信息（包括代理商，一级用户，二级用户，普通用户）
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
		$sql = 'insert into user(openid,avatarUrl,nickName,phone,address,createtime,belong) value("' . $this->mypost->openid . '","' . $this->mypost->avatarUrl . '","' . $this->mypost->nickName . '","' . $this->mypost->phone . '","' . $this->mypost->address . '","' . time() . '", "' . $this->mypost->belong . '") ON DUPLICATE KEY UPDATE avatarUrl="' . $this->mypost->avatarUrl . '",nickName="' . $this->mypost->nickName . '", phone="' . $this->mypost->phone . '",address="' . $this->mypost->address . '",wechat="' . $this->mypost->wechat . '", belong="' . $this->mypost->belong . '",status="' . $this->mypost->status . '"';
		$res = $this->db->dql($sql);
		$this->sendData($res, $sql);
	}

	// 编辑管理员信息
	public function editAdminUser() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$this->mypost = json_decode($rws_post);
		$sql = "insert into user(openid,avatarUrl,nickName,account,pwd,formId,createtime,status,belong) value('" . $this->mypost->openid . "','" . $this->mypost->avatarUrl . "','" . $this->mypost->nickName . "','" . $this->mypost->account . "','" . $this->mypost->pwd . "', '" . serialize($this->mypost->formId) . "','" . time() . "','1','" . $this->mypost->belong . "') ON DUPLICATE KEY UPDATE avatarUrl='" . $this->mypost->avatarUrl . "',nickName='" . $this->mypost->nickName . "', account='" . $this->mypost->account . "',pwd='" . $this->mypost->pwd . "', formId='" . serialize($this->mypost->formId) . "',status='" . $this->mypost->status . "'";
		$res = $this->db->dql($sql);
		$this->sendData($res, $sql);
	}

	// 通过openid获取下级用户
	public function getNextLevel() {
		$sql = 'select * from user where channel like "%' . $this->mypost->channel . '%" ';
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			array_push($data, $row);
		}
		$this->sendData($data, $sql);
	}

	// 获取所有分类
	public function getTypeList() {

		$sql = "select * from classify where status!='0' and belong='" . $this->mypost->belong . "'";
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			if (unserialize($row['subCategory'])) {
				$row['subCategory'] = unserialize($row['subCategory']);
			} else {
				$row['subCategory'] = array();
			}

			array_push($data, $row);
		}
		$this->sendData($data, $sql);
	}

	// 更新商品
	public function push() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);

		$data = array();
		$id = '';
		if (isset($mypost->id)) {
			$data['id'] = $mypost->id;
			$sql = "update ershou set title='" . $mypost->title . "',cont='" . $mypost->cont . "',cover_img='" . $mypost->cover_img . "',imgs='" . serialize($mypost->imgs) . "',imgs_detail='" . serialize($mypost->imgs_detail) . "',price='" . $mypost->price . "',old_price='" . $mypost->old_price . "',classify='" . $mypost->classify . "',status='" . $mypost->status . "', belong='" . $mypost->belong . "' where id='" . $mypost->id . "'";
			$data['msg'] = '更新成功';
		} else {
			$sql = "insert into ershou(openid,title,cover_img,cont,imgs,imgs_detail,price,old_price,classify,status,belong,createtime,updatetime) value('" . $mypost->openid . "','" . $mypost->title . "','" . $mypost->cover_img . "','" . $mypost->cont . "','" . serialize($mypost->imgs) . "','" . serialize($mypost->imgs_detail) . "','" . $mypost->price . "','" . $mypost->old_price . "','" . $mypost->classify . "','1','" . $mypost->belong . "','" . time() . "','" . time() . "')";
			$id = mysql_insert_id();
			$data['msg'] = '发布成功';
		}
		$res = $this->db->dql($sql);

		$this->sendData($res, $sql);
	}

	// 加入购物车,默认每次都是增加一个
	// 如果用户已经添加过该商品，则直接数量+1
	public function addCart() {
		$sql = "select id from cart where openid='" . $this->mypost->openid . "' and main_id='" . $this->mypost->id . "'";
		$res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
		if ($res) {
			$this->updateCart();
		} else {
			$sql = "insert into cart(main_id,title,img,price,num,openid,createtime) value('" . $this->mypost->id . "','" . $this->mypost->title . "','" . $this->mypost->img . "','" . $this->mypost->price . "','1','" . $this->mypost->openid . "','" . time() . "')";
			$res = $this->db->dql($sql);
			$this->sendData($res, $sql);
		}
	}

	// 更新购物车中商品的信息
	// 包括数量，是否选中，
	public function updateCart() {
		$sql = "update cart set num = num+1 where openid='" . $this->mypost->openid . "' and main_id='" . $this->mypost->id . "' ";
		$res = $this->db->dql($sql);
		$this->sendData($res, $sql);
	}

	// 获取用户购物车中的n内容
	public function getCart() {
		$sql = 'select * from cart where openid="' . $this->mypost->openid . '"';
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			array_push($data, $row);
		}
		$this->sendData($data, $sql);
	}

	// 客户模糊搜索，只能搜索自己的内容
	public function searchList() {
		$sql = 'select * from ershou where title like "%' . $this->mypost->keyword . '%" and openid="' . $this->openid . '" and status!="0"';
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$row['imgs'] = unserialize($row['imgs']);
			array_push($data, $row);
		}
		$this->sendData($data, $sql);
	}

	// 获取内容列表，可以把客户视为内容管理系统中的一个用户
	// status为0的内容，表示已经被删除，任何时候都不可以显示到前端
	public function getList() {
		if (!isset($this->mypost->classify)) {
			$this->mypost->classify = '';
		}

		$sql = 'select * from ershou where openid="' . $this->mypost->openid . '" and (classify="' . $this->mypost->classify . '" or "' . $this->mypost->classify . '"="")  and status!="0" order by updatetime desc ';

		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$row['imgs'] = unserialize($row['imgs']);
			$row['imgs_detail'] = unserialize($row['imgs_detail']);
			array_push($data, $row);
		}
		$this->sendData($data, $sql);
	}

	public function getDetail() {
		$sql = 'select * from ershou where id="' . $this->mypost->id . '" limit 1';
		$res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
		$res['imgs'] = unserialize($res['imgs']) ? unserialize($res['imgs']) : array();
		$res['imgs_detail'] = unserialize($res['imgs_detail']) ? unserialize($res['imgs_detail']) : array();
		$this->sendData($res, $sql);
	}

	// orders表中，购买的商品信息是一个数组，所以要去数组中找，有没有指定的id存在
	public function checkBuy() {
		// 1586
		// oHDjI5QkkTLAdhkhYnw88rBE54ys
		// select id from orders where list like "1586" and openid="oHDjI5QkkTLAdhkhYnw88rBE54ys" limit 1
		$sql = 'select id from orders where list like "%' . $this->mypost->id . '%" and openid="' . $this->mypost->openid . '"  and status!="1" limit 1';
		$res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
		$this->sendData($res, $sql);
	}

	// 更新用户信息
	public function updateUser() {
		$sql = 'insert into user(openid,avatarUrl,nickName,gender,status,createtime,belong) value("' . $this->mypost->openid . '","' . $this->mypost->avatarUrl . '","' . $this->mypost->nickName . '","' . $this->mypost->gender . '","' . $this->mypost->status . '","' . time() . '","' . $this->mypost->belong . '") ON DUPLICATE KEY UPDATE avatarUrl="' . $this->mypost->avatarUrl . '",nickName="' . $this->mypost->nickName . '", gender="' . $this->mypost->gender . '", status="' . $this->mypost->status . '"';
		$res = $this->db->dql($sql);
		$this->sendData($res);
	}

	// 用户提交下级代理的资料
	public function signup() {
		$sql = 'update user set qrcode="' . $this->mypost->qrcode . '",email="' . $this->mypost->email . '",realName="' . $this->mypost->realName . '", phone="' . $this->mypost->phone . '",address="' . $this->mypost->address . '",channel="' . $this->mypost->channel . '" where openid="' . $this->mypost->openid . '"';
		$res = $this->db->dql($sql);
		$this->sendData($res);
	}

	// 插入订单信息
	// 如果是支付原来的订单，我们需要把原始订单删除，再插入新的订单
	public function updateOrder() {
		if (isset($this->mypost->id)) {
			$sql = 'update orders set status="0" where id="' . $this->mypost->id . '"';
			$this->db->dql($sql);
		}
		$sql = "insert into orders(prepay_id, out_trade_no, list, price,raw_price,coupon, openid, remark, name, phone, address, status,channel,createtime) value('" . $this->mypost->prepay_id . "','" . $this->mypost->out_trade_no . "','" . serialize($this->mypost->info) . "','" . $this->mypost->price . "','" . $this->mypost->raw_price . "','" . serialize($this->mypost->coupon) . "','" . $this->mypost->openid . "','" . $this->mypost->remark . "','" . $this->mypost->name . "','" . $this->mypost->phone . "','" . $this->mypost->address . "','1','" . $this->mypost->channel . "','" . time() . "')";
		$res = $this->db->dql($sql);
		$this->sendData($res, $sql);
	}

	public function editOrder() {
		$sql = 'update orders set status="' . $this->mypost->status . '", track="' . $this->mypost->track . '" where id="' . $this->mypost->id . '"';
		$res = $this->db->dql($sql);
		$this->sendData($res, $sql);
	}

	// 获取我的订单列表，已被删除的订单不要返回
	public function getOrders() {
		$sql = "select * from orders where openid='" . $this->mypost->openid . "' and status!='0' order by createtime desc";
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$row['info'] = unserialize($row['list']);
			$row['coupon'] = unserialize($row['coupon']);
			array_push($data, $row);
		}
		$this->sendData($data, $sql);
	}

	// 后台系统获取全部订单信息
	// 可以根据订单编号或者指定月份进行查询
	// select o.id,o.out_trade_no,o.list,o.price,o.raw_price,o.coupon,o.name,o.phone,o.address,o.track,o.remark,o.status,o.createtime,u.channel from orders o right join user u on o.channel=u.openid where (o.id="" or ""="") and (o.out_trade_no="" or ""="") and (o.createtime>"" or ""="") and (o.createtime<"" or ""="") and o.status!="0" and o.status!="1" order by o.createtime desc
	public function getAllOrders() {

		$beginStamp = '';
		$endStamp = '';
		if (isset($this->mypost->date)) {
			$date = $this->mypost->date;
			$day = date("t", strtotime($date));
			$date_arr = explode("-", $date);
			$beginStamp = mktime(0, 0, 0, $date_arr[1], 1, $date_arr[0]);
			$endStamp = mktime(23, 59, 59, $date_arr[1], $day, $date_arr[0]);
		}

		$sql = 'select o.id,o.out_trade_no,o.list,o.price,o.raw_price,o.coupon,o.name,o.phone,o.address,o.track,o.remark,o.status,o.createtime,u.channel from orders o right join user u on o.channel=u.openid where (o.id="' . $this->mypost->id . '" or "' . $this->mypost->id . '"="") and (o.out_trade_no="' . $this->mypost->out_trade_no . '" or "' . $this->mypost->out_trade_no . '"="") and (o.createtime>"' . $beginStamp . '" or "' . $beginStamp . '"="") and (o.createtime<"' . $endStamp . '" or "' . $endStamp . '"="") and o.status!="0" and o.status!="1" order by o.createtime desc';
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$row['info'] = unserialize($row['list']);
			$row['coupon'] = unserialize($row['coupon']);
			array_push($data, $row);
		}
		$this->sendData($data, $sql);
	}

	// 为指定的订单添加评论
	public function orderComment() {

		$sql = "insert into comment(openid,main_id,order_id,content,imgs,status,createtime) value('" . $this->mypost->openid . "','" . $this->mypost->main_id . "','" . $this->mypost->order_id . "','" . $this->mypost->content . "','" . serialize($this->mypost->imgs) . "','1','" . time() . "') ON DUPLICATE KEY UPDATE content='" . $this->mypost->content . "', imgs='" . serialize($this->mypost->imgs) . "'";
		$res = $this->db->dql($sql);

		$changeStatus = 'update orders set status="已结束" where id="' . $this->mypost->order_id . '"';
		$this->db->dql($changeStatus);

		$this->sendData($res, $sql);
	}

	// 获取指定商品的评论数据
	// select c.content,c.imgs,c.openid,c.main_id,u.nickName,u.avatarUrl from comment c join user u on u.openid=c.openid where c.main_id=1645
	public function getComment() {
		$sql = "select c.id,c.content,c.imgs,c.openid,c.main_id,c.createtime,u.nickName,u.avatarUrl from comment c join user u on u.openid=c.openid where c.main_id='" . $this->mypost->id . "'";
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$row['imgs'] = unserialize($row['imgs']);
			array_push($data, $row);
		}
		$this->sendData($data, $sql);
	}

	// 获取指定渠道的订单
	// 这里只显示已经支付的订单
	public function getChannelOrders() {
		$sql = 'select * from orders where channel="' . $this->mypost->channel . '" and status!="0" and status!="1"';
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$row['info'] = unserialize($row['list']);
			array_push($data, $row);
		}
		$this->sendData($data, $sql);
	}

	// 获取指定用户的优惠券
	public function getCoupons() {
		$sql = 'select * from coupon where  openid="' . $this->mypost->openid . '" ';
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			array_push($data, $row);
		}
		$this->sendData($data, $sql);
	}

	// 领取一个优惠券
	// insert into coupon(openid, num, max_price, channel, createtime) select 'oHDjI5QkkTLAdhkhYnw88rBE54ys','100','1000','oHDjI5QkkTLAdhkhYnw88rBE54ys','1559039855' from DUAL where not exists (select id from coupon where openid='oHDjI5QkkTLAdhkhYnw88rBE54ys' and num='100' and max_price='1000')
	public function receiveCoupon() {
		$sql = "insert into coupon(openid, num, max_price, channel, createtime) select '" . $this->mypost->openid . "','" . $this->mypost->num . "','" . $this->mypost->max . "','" . $this->mypost->channel . "','" . time() . "'from DUAL where not exists (select id from coupon where openid='" . $this->mypost->openid . "' and num='" . $this->mypost->num . "' and max_price='" . $this->mypost->max . "' limit 1)";
		$res = $this->db->dql($sql);
		$this->sendData($res, $sql);
	}

	// 参数1：sql执行成功还是失败
	public function sendData($res, $sql = '') {
		$data['data'] = $res;
		$data['code'] = 200;
		$data['sql'] = $sql;
		echo json_encode($data);
	}

	// test
	public function test() {
		$date = $this->mypost->date;
		echo date("t", strtotime("$y-$i"));

		// $sql = 'select * from orders where (id="' . $this->mypost->id . '" or "' . $this->mypost->id . '"="") and (out_trade_no="' . $this->mypost->out_trade_no . '" or "' . $this->mypost->out_trade_no . '"="")';
		// $res = $this->db->dql($sql);
		// $data = array();
		// while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		// 	$row['info'] = unserialize($row['list']);
		// 	array_push($data, $row);
		// }
		// $this->sendData($data, $sql);
	}

}