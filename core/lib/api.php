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

require_once __ROOT__ . '/core/common/goods_redis.php';

class api {

	// 数据库句柄
	public $db;
	public $post;
	public $from = 'unibbs';

	// 构造函数，将pdo句柄传递给类
	public function __construct($db) {
		$this->db = $db;
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$this->mypost = json_decode($rws_post);
	}

	// 查询帖子中的所有城市
	public function getCitys() {
		$sql = "select city from ershou where city !='' group by city";
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			array_push($data, $row);
		}
		$this->sendData($data, $sql);
	}

	// 记录访客数量
	public function takeNote() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);
		$sql = 'insert into visitior(openid,college,date,time) value("' . $mypost->openid . '","' . $mypost->college . '","' . date('Y-m-d', time()) . '","' . date('H:i:s', time()) . '")';
		$res = $this->db->dql($sql);

		// 当前日期当前学校的访客+1
		// $redis = new GoodsRedis();
		// $redis->newAddVisterNum($goods_id, $comment_id, $mypost);

		$this->sendData($res, $sql);
	}

	public function editBanner() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);
		$sql = '';
		if (isset($mypost->id)) {
			$sql = 'update banner set img="' . $mypost->img . '",title="' . $mypost->title . '", status="' . $mypost->status . '" where id="' . $mypost->id . '"';
		} else {
			$sql = 'insert into banner(img,title,createtime,belong) value("' . $mypost->img . '","' . $mypost->title . '","' . time() . '", "' . $mypost->belong . '")';
		}

		$res = $this->db->dql($sql);
		$this->sendData($res, $sql);
	}

	// 获取banner
	public function getBanner() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);
		$sql = 'select * from banner where status!="0" and belong="' . $mypost->belong . '" and (id="' . $mypost->id . '" or "' . $mypost->id . '" = "")';
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			array_push($data, $row);
		}
		$this->sendData($data, $sql);
	}

	// 获取用户信息
	public function getInfo() {
		$res = '';
		$data = array();
		$sql = 'select * from user where openid ="' . $this->mypost->openid . '" limit 1';
		$res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
		echo json_encode($res);
	}

	// 获取二手列表信息
	// 查询的条件存在时就查询，不存在就跳过该条件使用其他条件查询
	public function chooseCollege() {

		$sql = 'update user set college="' . $this->mypost->college . '" where openid="' . $this->mypost->openid . '"';
		$res = $this->db->dql($sql);

		$sql = 'select ROW_COUNT() as row';
		$r = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);

		$this->sendData($r, $sql);
	}

	// 模糊搜搜内容
	public function search() {
		$sql = "select * from ershou where cont like '%" . $this->mypost->keyword . "%'";
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$row['imgs'] = unserialize($row['imgs']);
			array_push($data, $row);
		}
		$this->sendData($data, $sql);
	}

	// 查询的条件存在时就查询，不存在就跳过该条件使用其他条件查询
	// 不对任何人展示状态俄日2的内容，代表删除
	public function getList() {
		if (!isset($this->mypost->belong)) {
			$this->mypost->belong = '';
		}
		if (!isset($this->mypost->college)) {
			$this->mypost->college = '';
		}
		if (!isset($this->mypost->page)) {
			$this->mypost->page = 0;
		}
		if (!isset($this->mypost->openid)) {
			$this->mypost->openid = '';
		}
		if (!isset($this->mypost->classify)) {
			$this->mypost->classify = '';
		}
		if (!isset($this->mypost->category)) {
			$this->mypost->category = '';
		}
		$sql = 'select e.id,e.openid,e.title,e.cont,e.college,e.imgs,e.imgs_detail,e.symbol,e.price,e.old_price,e.status,e.city,e.address,e.phone,e.level,e.classify,e.category,e.message,e.views,e.liked,e.updatetime,u.nickName,u.avatarUrl,u.status as user_status from ershou as e  left join user as u on e.openid = u.openid where (e.college="' . $this->mypost->college . '" or "' . $this->mypost->college . '" ="") and e.status!=0 and (e.classify="' . $this->mypost->classify . '" or "' . $this->mypost->classify . '" = "") and (e.category="' . $this->mypost->category . '" or "' . $this->mypost->category . '" = "") and (e.openid="' . $this->mypost->openid . '" or "' . $this->mypost->openid . '" = "") and (e.belong="' . $this->mypost->belong . '" or "' . $this->mypost->belong . '" ="") and e.openid!="" order by updatetime desc limit ' . $this->mypost->page * 20 . ',20 ';

		$res = $this->db->dql($sql);
		$data = array();

		$redis = new GoodsRedis();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$row['imgs'] = unserialize($row['imgs']);
			array_push($data, $row);
		}

		foreach ($data as &$item) {
			//查询comment数
			$num = 0;
			$id = !empty($item['id']) ? $item['id'] : '';
			if (!empty($id)) {
				$num = $redis->getCommentNum($id);
			}

			$item['comment_num'] = $num ?: 0;
		}

		$this->sendData($data, $sql);
	}

	// 获取帖子详情
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

	// 获取帖子相关参与信息
	public function getComment() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);
		$sql = 'select m.id,m.fromopenid,m.cont,m.tag,m.createtime,m.status,u.avatarUrl,u.nickName  from message m left join user u on u.openid = m.fromopenid where  m.ershou="' . $mypost->id . '" order by m.createtime ';
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			$row['imgs'] = unserialize($row['imgs']);
			array_push($data, $row);
		}
		$this->sendData($data, $sql);
	}

	// 获取我的所有评论
	public function getMyComments() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);
		$sql = 'select * from comment where openid="' . $mypost->openid . '" order by createtime desc';
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			array_push($data, $row);
		}
		$this->sendData($data, $sql);
	}

	// 更新用户信息
	public function editUser() {

		$sql = 'insert into user(openid,avatarUrl,nickName,createtime,belong) value("' . $this->mypost->openid . '","' . $this->mypost->avatarUrl . '","' . $this->mypost->nickName . '","' . time() . '", "' . $this->from . '") ON DUPLICATE KEY UPDATE avatarUrl="' . $this->mypost->avatarUrl . '",nickName="' . $this->mypost->nickName . '", ad="' . $this->mypost->ad . '",wechat="' . $this->mypost->wechat . '",douyin="' . $this->mypost->douyin . '",weibo="' . $this->mypost->weibo . '", belong="' . $this->from . '"';
		$res = $this->db->dql($sql);

		$this->sendData($res, $sql);
	}

	// 管理员更新用户信息
	public function adminEditUser() {

		$sql = 'insert into user(openid,avatarUrl,nickName,college,createtime) value("' . $this->mypost->openid . '","' . $this->mypost->avatarUrl . '","' . $this->mypost->nickName . '","' . $this->mypost->college . '","' . time() * 1000 . '") ON DUPLICATE KEY UPDATE avatarUrl="' . $this->mypost->avatarUrl . '",nickName="' . $this->mypost->nickName . '", college="' . $this->mypost->college . '", belong="' . $this->from . '"';
		$res = $this->db->dql($sql);

		$this->sendData($res);
	}

	public function getUserList() {
		$sql = "select * from user where nickName like '%" . $this->mypost->keyword . "%'";
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			array_push($data, $row);
		}
		$this->sendData($data);
	}

	// 获取列表数据

	// 用户发布
	public function push() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);

		if (!isset($mypost->belong)) {
			$mypost->belong = 'unibbs';
		}

		$data = array();
		$id = '';
		if (isset($mypost->id)) {
			$data['id'] = $mypost->id;
			$sql = "update ershou set title='" . $mypost->title . "',cont='" . $mypost->cont . "',imgs='" . serialize($mypost->imgs) . "',imgs_detail='" . serialize($mypost->imgs_detail) . "',symbol='" . $mypost->symbol . "',updatetime='" . time() . "',college='" . $mypost->college . "',address='" . $mypost->address . "',openid='" . $mypost->openid . "',wechat='" . $mypost->wechat . "',price='" . $mypost->price . "',old_price='" . $mypost->old_price . "',is_new='" . $mypost->is_new . "',level='" . $mypost->level . "',classify='" . $mypost->classify . "',category='" . $mypost->category . "',status='" . $mypost->status . "', updatetime='" . time() . "' where id='" . $mypost->id . "'";
			$data['msg'] = '更新成功';
		} else {
			$sql = "insert into ershou(openid,title,cont,imgs,imgs_detail,symbol,college,address,wechat,price,classify,category,createtime,updatetime, belong) value('" . $mypost->openid . "','" . $mypost->title . "','" . $mypost->cont . "','" . serialize($mypost->imgs) . "','" . serialize($mypost->imgs_detail) . "','" . $mypost->symbol . "','" . $mypost->college . "','" . $mypost->address . "','" . $mypost->wechat . "','" . $mypost->price . "','" . $mypost->classify . "','" . $mypost->category . "','" . time() . "','" . time() . "', '" . $mypost->belong . "')";
			$id = mysql_insert_id();
			$data['msg'] = '发布成功';
		}
		$res = $this->db->dql($sql);
		$data['code'] = 200;

		if ($res) {
			if (!isset($mypost->id)) {
				$sql = 'select LAST_INSERT_ID()';
				$res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
				$data['id'] = $res['LAST_INSERT_ID()'];
			}

		} else {
			$data['code'] = 201;
			$data['msg'] = 'sql异常';
		}

		// // update college set num = (select count(*) from ershou where college='北京大学') where uName="北京大学"
		// $sql = 'update college set num = (select count(*) from ershou where college="' . $mypost->college . '") where uName="' . $mypost->college . '" ';
		// $db->dql($sql);
		$this->sendData($data, $sql);
	}

	// 更改帖子状态
	public function editStatus() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);
		$sql = "update ershou set status='0' where id='" . $mypost->id . "'";
		$res = $this->db->dql($sql);
		$this->sendData($res, $sql);
	}

	// 用户可以点赞和取消点赞
	public function userLike() {

		// 先查询出用户对该主题最近一次的点赞记录
		$sql = "select id,status from liked where openid='" . $this->mypost->openid . "' and mainid='" . $this->mypost->id . "' order by createtime desc limit 1";
		$res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
		$status = '1'; // 默认
		if ($res && $res['status'] == '1') {
			$status = '0';
			$sql = "update ershou set liked = liked-1 where id='" . $this->mypost->id . "'";
			$this->db->dql($sql);
		} else {
			$sql = "update ershou set liked = liked+1 where id='" . $this->mypost->id . "'";
			$this->db->dql($sql);
		}

		$sql = "insert into liked(openid,mainid,status,createtime) value('" . $this->mypost->openid . "','" . $this->mypost->id . "','" . $status . "','" . time() . "') ";
		$r2 = $this->db->dql($sql);

		$data['res'] = $r2;
		$data['sql'] = $sql;
		$data['status'] = $status;
		echo json_encode($data);
	}

	// 用户评论
	public function userComment() {

		$sql = "insert into comment(openid, main_id, order_id, content, imgs, status, createtime) value('" . $this->mypost->openid . "','" . $this->mypost->main_id . "','" . $this->mypost->order_id . "','" . $this->mypost->content . "','" . serialize($this->mypost->imgs) . "','1','" . time() . "') ";
		$res = $this->db->dql($sql);
		$this->sendData($res, $sql);
	}

	// 更改分类信息
	public function editType() {
		$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
		$mypost = json_decode($rws_post);
		$sql = '';
		if (isset($mypost->id)) {
			$sql = "update classify set name='" . $mypost->name . "', subCategory = '" . serialize($mypost->subCategory) . "',status='" . $mypost->status . "',is_show='" . $mypost->is_show . "' where id='" . $mypost->id . "'";

		} else {
			$sql = "insert into classify(name,subCategory,is_show,belong) value('" . $mypost->name . "','" . serialize($mypost->subCategory) . "','" . $mypost->is_show . "','" . $mypost->belong . "')";
		}
		$res = $this->db->dql($sql);

		$this->sendData($res, $sql);
	}

	// 编辑分类
	public function getTypeList() {
		$sql = "select * from classify where status!='0' and belong='unibbs'";

		if (isset($this->mypost->belong)) {
			$sql = "select * from classify where status!='0' and belong='" . $this->mypost->belong . "'";
		}

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
		echo json_encode($data);
	}

	public function getClassify() {
		$sql = "select * from classify where status!='0' and belong='unibbs'";

		if (isset($this->mypost->belong)) {
			$sql = "select * from classify where status!='0' and belong='" . $this->mypost->belong . "'";
		}

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

	// 用户登录
	public function login() {
		// dump('ok');
		// dump(date('Y-m-d', time()));
		// dump(date('H:i:s', time()));
		$sql = 'select * from user ';
		var_dump($sql);
		$res = $this->db->dql($sql);
		$data = array();
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			array_push($data, $row);
		}
		var_dump($data);
		// $stmt = $this->db->dql($sql);
		// // // $stmt->bindParam('openid',$openid);
		// $stmt->execute();
		// // // $res = $stmt->fetch(PDO::FETCH_ASSOC);
		// $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
		// var_dump($res);
		// return $res;
	}

	public function throwError() {
		throw new Exception('openid已经存在', '0');
	}

	// 参数1：sql执行成功还是失败
	public function sendData($res, $sql = '') {
		$data['res'] = $res;
		$data['sql'] = $sql;
		$data['data'] = $res;
		$data['code'] = 200;
		echo json_encode($data);
	}

	// 类私有函数，检查用户是否已经存在，私有方法
	private function _check() {

	}

	// test
	public function test() {
		// $sql = "select * from classify where status!='0' ";
		// $res = $this->db->dql($sql);
		// $data = array();
		// while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		// 	var_dump($row['subCategory']);
		// 	var_dump(unserialize($row['subCategory']));
		// 	// $row['subCategory'] = unserialize($row['subCategory']);
		// 	// array_push($data, $row);
		// }
		$sql = "select id,status from liked where openid='oL9095XDDwBs7tsE1DpJFaihijsU' and mainid='835' order by createtime desc limit 1";
		$res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
		var_dump($res);
		var_dump($res['status']);

		$sql = "select id,status from liked where openid='oL9095XDDwBs7tsE1DpJFaihijsU' and mainid='875' order by createtime desc limit 1";
		var_dump($this->db->dql($sql));
		$res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
		var_dump($res);

	}

}