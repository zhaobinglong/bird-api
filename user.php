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

// 加载配置文件，配置文件应该和入口文件放在一起
require_once 'config.php';

//异常处理
require_once 'error_handler.php';

//redis
require_once __ROOT__ . '/core/common/goods_redis.php';

include 'db.class.php';

// include('http.class.php');
include 'wxlittle.class.php';

$db = new DB();
// $http = new http();

// 小程序对象
//$wxlittle = new wxlittle();

// 服务号对象

// 过滤非法字符

// 关于数组处理,数组需要用serialize转化，查询的时候用unserialize再转回来
// serialize(XXX),注意单引号和双引号
// demo:"insert into classify(name,subCategory) value('" . $mypost->name . "','".serialize($mypost->subCategory)."')"

switch ($_GET['code']) {

// 复制指定学校的二手到新的学校
case 'copy':
	$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
	$mypost = json_decode($rws_post);
	$sql = 'select * from ershou where college = "' . $mypost->fromCollege . '" ';
	$res = $db->dql($sql);
	$data = array();
	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		array_push($data, $row);
	}
	for ($i = 0; $i < count($data); $i++) {
		# code...
		$sql = "insert into ershou(openid,cont,imgs,symbol,college,address,wechat,price,type,createtime,updatetime) value('" . $data[$i]['openid'] . "','" . $data[$i]['cont'] . "','" . $data[$i]['imgs'] . "','" . $data[$i]['symbol'] . "','" . $mypost->toCollege . "','" . $data[$i]['address'] . "','" . $data[$i]['wechat'] . "','" . $data[$i]['price'] . "','" . $data[$i]['type'] . "','" . time() . "','" . time() . "')";
		$db->dql($sql);
	}
	$res['code'] = 200;
	$res['msg'] = '复制成功';
	echo json_encode($res);
	break;

// 获取指定日期的访客 基数270
case 'getVisitior':
	$sql = "select * from visitior where date='" . $_GET['date'] . "' and college='" . $_GET['college'] . "' ";
	$res = $db->dql($sql);
	$data = array();
	for ($i = 0; $i < 270; $i++) {
		array_push($data, '');
	}
	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		array_push($data, $row);
	}
	echo json_encode($data);
	break;

// 访客统计
case 'takeNote':
	$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
	$mypost = json_decode($rws_post);

	$sql = 'insert into visitior(openid,college,date,time) value("' . $mypost->openid . '","' . $mypost->college . '","' . date('Y-m-d', time()) . '","' . date('H:i:s', time()) . '")';

	mysql_fetch_array($db->dql($sql), MYSQL_ASSOC);
	echo $sql;
	break;
// 获取货币
case 'editCurrency':
	$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
	$mypost = json_decode($rws_post);
	$sql = '';
	if (isset($mypost->id)) {
		$sql = 'update currency set nation="' . $mypost->nation . '",name="' . $mypost->name . '",symbol="' . $mypost->symbol . '", spell="' . $mypost->spell . '"where id="' . $mypost->id . '"';

	} else {
		$sql = 'insert into currency(nation,name,symbol,spell) value("' . $mypost->nation . '","' . $mypost->name . '","' . $mypost->symbol . '","' . $mypost->spell . '")';
	}
	mysql_fetch_array($db->dql($sql), MYSQL_ASSOC);

	$res['code'] = 200;
	$res['msg'] = '更新';

	echo json_encode($res);
	break;

// 获取货币
case 'getCurrency':
	$sql = "select * from currency ";
	$res = $db->dql($sql);
	$data = array();
	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		array_push($data, $row);
	}
	echo json_encode($data);
	break;

case 'editType':
	$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
	$mypost = json_decode($rws_post);
	$sql = '';
	if (isset($mypost->id)) {
		$sql = "update classify set name='" . $mypost->name . "', subCategory = '" . serialize($mypost->subCategory) . "',status='" . $mypost->status . "' where id='" . $mypost->id . "'";

	} else {
		$sql = "insert into classify(name,subCategory) value('" . $mypost->name . "','" . serialize($mypost->subCategory) . "')";
	}
	mysql_fetch_array($db->dql($sql), MYSQL_ASSOC);

	$res['code'] = 200;
	$res['msg'] = '更新';
	echo json_encode($res);
	break;
// 编辑分类
case 'getTypeList':
	$sql = "select * from classify where status!='0' ";
	$res = $db->dql($sql);
	$data = array();
	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		$row['subCategory'] = unserialize($row['subCategory']);
		array_push($data, $row);
	}
	echo json_encode($data);
	break;

// 通过id获取学校信息
case 'getCollegeById':
	$sql = 'select * from college where sid ="' . $_GET['sid'] . '"';
	$res = mysql_fetch_array($db->dql($sql), MYSQL_ASSOC);
	echo json_encode($res);
	break;
// 获取小程序二维码
case 'getLittleImg':

	header("Content-Type:image/png");
	header("Accept-Ranges:bytes");
	echo $wxlittle->getLittleImg($_GET['id'], $_GET['page']);
	break;

// 搜索学校
case 'searchSchool':
	$sql = "select * from college where uName like '%" . $_GET['name'] . "%'";
	$res = $db->dql($sql);
	$data = array();
	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		array_push($data, $row);
	}
	echo json_encode($data);
	break;

// 搜索用户
case 'searchUser':
	$sql = "select * from user where nickName like '%" . $_GET['name'] . "%'";
	$res = $db->dql($sql);
	$data = array();
	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		array_push($data, $row);
	}
	echo json_encode($data);
	break;

// 全局根据关键词搜索二手
case 'search':

	$sql = "select * from ershou where cont like '%" . $_GET['keyword'] . "%' and status!=0";
	$res = $db->dql($sql);
	$data = array();
	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		$row['imgs'] = unserialize($row['imgs']);
		array_push($data, $row);
	}
	echo json_encode($data);
	break;

// 根据指定字段获取大学相关信息
case 'getCollege':
	$sql = 'select * from college where uName ="' . $_GET['uName'] . '"';
	$res = mysql_fetch_array($db->dql($sql), MYSQL_ASSOC);

	// 获取该学校成员
	$sql = 'select avatarUrl,nickName,openid,createtime from user where college ="' . $_GET['uName'] . '" order by createtime desc ';
	$res2 = $db->dql($sql);
	$member = array();
	while ($row = mysql_fetch_array($res2, MYSQL_ASSOC)) {
		array_push($member, $row);
	}

	$res['member'] = $member;

	echo json_encode($res);
	break;
// 获取大学名字获取本大学成员
case 'getMember':
	$sql = 'select avatarUrl from user where college ="' . $_GET['uName'] . '"';
	$res = $db->dql($sql);
	$data = array();
	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		array_push($data, $row);
	}
	echo json_encode($data);
// 获取学校详情
case 'getSchool':
	$sql = 'select * from college where sid ="' . $_GET['id'] . '"';
	$res = mysql_fetch_array($db->dql($sql), MYSQL_ASSOC);
	echo json_encode($res);
	break;

// 编辑学校 如果没有学校id，则查插入新的学校
case 'editSchool':
	$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
	$mypost = json_decode($rws_post);
	$sql = '';
	if (isset($mypost->sid)) {
		$sql = 'update college  set uName="' . $mypost->uName . '",top_img="' . $mypost->top_img . '",logo="' . $mypost->logo . '" where sid="' . $mypost->sid . '"';
	} else {
		$sql = 'insert into college(uName,top_img,logo) value("' . $mypost->uName . '","' . $mypost->top_img . '","' . $mypost->logo . '")';
	}

	$res = $db->dql($sql);
	$data['code'] = 200;
	$data['res'] = $res;
	$data['sql'] = $sql;
	$data['msg'] = '更新成功';
	echo json_encode($data);
	break;
// 获取定位服务
case 'getLocation':
	echo $wxlittle->getLocation($_GET['lat'], $_GET['lng']);
	// $key = 'ZT2BZ-C7FWP-DUMD2-VASMB-EUKXJ-ADF7N';
	// $url = 'http://apis.map.qq.com/ws/geocoder/v1/?location='.$_GET['lat'].','.$_GET['lng'].'&key='.$key;
	// $res = $http->httpsGet($url);
	// echo $res;
	break;

// 根据输入的大学名字模糊搜索大学列表
case 'getColleges':
	$sql = "select uName from college where uName like '%" . $_GET['name'] . "%'";
	$res = $db->dql($sql);
	$data = array();
	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		array_push($data, $row);
	}
	echo json_encode($data);
	break;

// 根据城市，模糊搜索大学列表
case 'getCollegesByCity':
	$sql = "select uName from college where city like '%" . $_GET['city'] . "%'";
	$res = $db->dql($sql);
	$data = array();
	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		array_push($data, $row);
	}
	echo json_encode($data);
	break;
// 获取城市列表
case 'getCitys':
	$sql = 'select city,nation from ershou ';
	$res = $db->dql($sql);
	$data = array();
	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		// if($row['city']){
		array_push($data, $row);
		// }
	}
	// $arr = array_unique($data);
	// echo json_encode(array_values($arr));
	echo json_encode($data);
	break;
case 'formId':
	$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
	$mypost = json_decode($rws_post);
	$formId = '-' . $mypost->formId;
	// update user set formId=concat($formId,formId) where openid="'.$mypost->openid.'"

	$sql = 'update user set formId=concat(formId,"' . $formId . '") where openid="' . $mypost->openid . '" ';
	$res = $db->dql($sql);

	if ($res) {
		$data['code'] = 200;
		$data['msg'] = '更新成功';
		$data['sql'] = $sql;
		echo json_encode($data);
	} else {
		$data['code'] = 200;
		$data['sql'] = $sql;
		$data['msg'] = '更新失败';
		echo json_encode($data);
	}

	break;

// 设置我的消息已经阅读
case 'updateMessageStatus':
	$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
	$mypost = json_decode($rws_post);
	$sql = 'update message set status = 1 where id="' . $mypost->id . '" ';
	$res = mysql_fetch_array($db->dql($sql), MYSQL_ASSOC);
	if ($res) {
		$res['code'] = 200;
		$res['sql'] = $sql;
		$res['msg'] = '更新成功';
	} else {
		$res['code'] = 201;
		$res['sql'] = $sql;
		$res['msg'] = '失败';
	}
	echo json_encode($res);
	break;

// 获取我的消息
case 'getMyMessage':
	$sql = 'select m.id,m.ershou,m.fromopenid,m.cont,m.tag,m.createtime,m.status,u.avatarUrl,u.nickName  from message m left join user u on u.openid = m.fromopenid where m.toopenid="' . $_GET['openid'] . '" and m.fromopenid!="' . $_GET['openid'] . '" order by m.createtime desc';
	$res = $db->dql($sql);
	$data = array();
	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		array_push($data, $row);
	}
	echo json_encode($data);
	break;

// 获取我和指定用户在详情中的咨询和回复
// select m.id,m.fromopenid,m.cont,m.tag,m.createtime,m.status,u.avatarUrl,u.nickName  from message m left join user u on u.openid = m.fromopenid where (m.toopenid="'omIsn41XFxxAeVaKUSP0N_NixdSY" or m.fromopenid="omIsn41XFxxAeVaKUSP0N_NixdSY") and m.ershou="457" order by m.createtime
case 'getMessageByDetail':
	$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
	$mypost = json_decode($rws_post);
	$sql = 'select m.id,m.fromopenid,m.cont,m.tag,m.createtime,m.status,u.avatarUrl,u.nickName  from message m left join user u on u.openid = m.fromopenid where  m.ershou="' . $mypost->id . '" order by m.createtime ';
	$res = $db->dql($sql);
	$data = array();
	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		array_push($data, $row);
	}
	echo json_encode($data);
	break;

//更新评论数量
case "updateMessageCount":
	$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
	$mypost = json_decode($rws_post);
	$sql = 'update ershou set message ="' . $mypost->message . '" where id="' . $mypost->id . '" ';
	$res = mysql_fetch_array($db->dql($sql), MYSQL_ASSOC);
	if ($res) {
		$res['code'] = 200;
		$res['sql'] = $sql;
		$res['msg'] = '更新成功';
	} else {
		$res['code'] = 201;
		$res['sql'] = $sql;
		$res['msg'] = '失败';
	}
	echo json_encode($res);
	break;

// 获取二手的评论
case 'getMessage':
	$sql = 'select m.fromopenid,m.cont,m.tag,m.createtime,u.avatarUrl,u.nickName  from message m left join user u on u.openid = m.fromopenid where m.ershou="' . $_GET['id'] . '" order by m.createtime desc';
	$res = $db->dql($sql);
	$data = array();
	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		array_push($data, $row);
	}
	echo json_encode($data);
	break;

//发表消息
case 'message':
	$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
	$mypost = json_decode($rws_post);
	$sql = 'insert into message(ershou,toopenid,fromopenid,cont,tag,createtime) value("' . $mypost->id . '","' . $mypost->toopenid . '","' . $mypost->fromopenid . '","' . $mypost->cont . '","' . $mypost->tag . '","' . time() . '")';
	$result = $db->dql($sql);

	if ($result) {
		$res['code'] = 200;
		$res['sql'] = $sql;
		$res['msg'] = '留言成功';
	} else {
		$res['code'] = 201;
		$res['sql'] = $sql;
		$res['msg'] = '留言失败';
	}

	$comment_sql = $db->dql('select LAST_INSERT_ID() as id');
	$ret = mysql_fetch_array($comment_sql, MYSQL_ASSOC);
	$comment_id = !empty($ret['id']) ? $ret['id'] : 0;
	$goods_id = !empty($mypost->id) ? $mypost->id : 0;

	if (!empty($comment_id) && !empty($goods_id)) {
		//添加评论数redis
		$redis = new GoodsRedis();
		$redis->newAddCommentNum($goods_id, $comment_id, $mypost);
	}

	echo json_encode($res);
	break;

//请求二手信息一次，每次请求之后，被请求数据+1
case 'getDetail':

	$sql = 'select e.id,e.openid,e.title,e.cont,e.college,e.imgs,e.symbol,e.price,e.old_price,e.address,e.wechat,e.nation,e.city,e.is_new,e.level,e.type,e.status,u.nickName,u.avatarUrl from ershou e left join  user u on u.openid = e.openid where e.id="' . $_GET['id'] . '"';
	$res = mysql_fetch_array($db->dql($sql), MYSQL_ASSOC);
	$res['imgs'] = unserialize($res['imgs']);
	echo json_encode($res);
	$sql = 'update ershou set views=views+1 where id = "' . $_GET['id'] . '"';
	$db->dql($sql);
	break;

// 获取二手信息列表
case 'getList':
	$sql = '';
	$page = (int) $_GET['page'] * 20;

	if ($_GET['type'] != '') {
		$sql = 'select e.id,e.title,e.cont,e.imgs,e.symbol,e.price,e.old_price,e.status,e.city,e.address,e.phone,e.level,e.type,e.message,e.views,e.liked,e.createtime,u.nickName,u.avatarUrl from ershou as e  left join user as u on e.openid = u.openid where e.college="' . $_GET['college'] . '" and e.status!=0 and e.type="' . $_GET['type'] . '"  order by updatetime desc limit ' . $page . ',20 ';
		// echo $sql;
	} else {
		$sql = 'select e.id,e.title,e.cont,e.imgs,e.symbol,e.price,e.old_price,e.status,e.city,e.address,e.phone,e.level,e.type,e.message,e.views,e.liked,e.createtime,u.nickName,u.avatarUrl from ershou as e  left join user as u on e.openid = u.openid where e.college="' . $_GET['college'] . '" and e.status!=0  order by updatetime desc limit ' . $page . ',20 ';

	}

	$res = $db->dql($sql);
	$data = array();
	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		$row['imgs'] = unserialize($row['imgs']);
		array_push($data, $row);
	}
	echo json_encode($data);

	break;

// 获取指定用户的列表
case 'getMyList':
	$page = (int) $_GET['page'] * 10;
	$sql = 'select e.id,e.title,e.cont,e.imgs,e.symbol,e.price,e.old_price,e.status,e.city,e.college,e.address,e.phone,e.level,e.type,e.message,e.views,e.createtime,u.nickName,u.avatarUrl from ershou as e  left join user as u on e.openid = u.openid where e.openid="' . $_GET['openid'] . '" and e.status!=0  order by updatetime desc limit ' . $page . ',10 ';

	$res = $db->dql($sql);
	$data = array();
	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		$row['imgs'] = unserialize($row['imgs']);
		array_push($data, $row);
	}
	echo json_encode($data);
	break;

// 管理后台获取二手列表
case 'adminList':
	$sql = 'select e.id,e.title,e.cont,e.imgs,e.symbol,e.price,e.old_price,e.city,e.address,e.phone,e.level,e.type,e.message,e.views,e.createtime,e.status,u.nickName,u.avatarUrl from ershou as e  left join user as u on e.openid = u.openid  order by createtime desc ';

	$res = $db->dql($sql);
	$data = array();
	while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
		$row['imgs'] = unserialize($row['imgs']);
		array_push($data, $row);
	}
	echo json_encode($data);
	break;

// 编辑用户信息
// 用户所属大学变更，更新college表中的信息
case 'editUser':
	$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
	$mypost = json_decode($rws_post);

	$sql = 'insert into user(openid,avatarUrl,nickName,ad,createtime) value("' . $mypost->openid . '","' . $mypost->avatarUrl . '","' . $mypost->nickName . '","' . $mypost->ad . '","' . time() * 1000 . '") ON DUPLICATE KEY UPDATE avatarUrl="' . $mypost->avatarUrl . '",nickName="' . $mypost->nickName . '",ad="' . $mypost->ad . '"';
	mysql_fetch_array($db->dql($sql), MYSQL_ASSOC);

	$res['code'] = 200;
	$res['msg'] = '更新';

	echo json_encode($res);
	break;

// 删除指定用户
case 'delUser':
	$sql = 'delete from user where id="' . $_GET['id'] . '"';
	$res = $db->dql($sql);
	if ($res) {
		$res['code'] = 200;
		$res['sql'] = '';
		$res['msg'] = '';
		echo json_encode($res);
	}

	break;

// 获取用户信息
case 'getInfo':
	$res = '';
	$data = array();
	if (isset($_GET['openid'])) {
		$sql = 'select * from user where openid ="' . $_GET['openid'] . '"';
		$res = mysql_fetch_array($db->dql($sql), MYSQL_ASSOC);
		echo json_encode($res);
	} else {
		$sql = 'select * from user limit 0,100';
		$res = $db->dql($sql);
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			array_push($data, $row);
		}
		echo json_encode($data);
	}

	break;

// 用户列表

// 用户发布二手
// 每次发布二手，学校的发布数量加1
// 发布之前检查用户状态，如果用户状态是2，则表示被禁，
// 0 删除  1 正常  2被禁
case 'push':
	$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
	$mypost = json_decode($rws_post);

	$sql = 'select status from user where openid="' . $mypost->openid . '" ';
	$res = mysql_fetch_array($db->dql($sql), MYSQL_ASSOC);
	if ($res['status'] != '1') {
		return false;
	}

	$data = array();
	$id = '';
	if (isset($mypost->id)) {
		$data['id'] = $mypost->id;
		$sql = "update ershou set title='" . $mypost->title . "',cont='" . $mypost->cont . "',imgs='" . serialize($mypost->imgs) . "',symbol='" . $mypost->symbol . "',updatetime='" . time() . "',college='" . $mypost->college . "',address='" . $mypost->address . "',openid='" . $mypost->openid . "',wechat='" . $mypost->wechat . "',price='" . $mypost->price . "',is_new='" . $mypost->is_new . "',level='" . $mypost->level . "',type='" . $mypost->type . "',status='" . $mypost->status . "' where id='" . $mypost->id . "'";
		$data['msg'] = '更新成功';
	} else {
		$sql = "insert into ershou(openid,cont,imgs,symbol,college,address,wechat,price,type,createtime,updatetime) value('" . $mypost->openid . "','" . $mypost->cont . "','" . serialize($mypost->imgs) . "','" . $mypost->symbol . "','" . $mypost->college . "','" . $mypost->address . "','" . $mypost->wechat . "','" . $mypost->price . "','" . $mypost->type . "','" . time() . "','" . time() . "')";
		$id = mysql_insert_id();
		$data['msg'] = '发布成功';
	}
	$res = $db->dql($sql);
	$data['code'] = 200;

	if ($res) {
		if (!isset($mypost->id)) {
			$sql = 'select LAST_INSERT_ID()';
			$res = mysql_fetch_array($db->dql($sql), MYSQL_ASSOC);
			$data['id'] = $res['LAST_INSERT_ID()'];
		}

	} else {
		$data['code'] = 201;
		$data['msg'] = 'sql异常';
	}

	// update college set num = (select count(*) from ershou where college='北京大学') where uName="北京大学"
	$sql = 'update college set num = (select count(*) from ershou where college="' . $mypost->college . '") where uName="' . $mypost->college . '" ';
	$db->dql($sql);

	echo json_encode($data);

	break;

case 'errorUpload':
	$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
	$mypost = json_decode($rws_post);
	$date = date('Y-m-d');
	$time = date('H:i:s');
	$sql = 'insert into info(info,date,time) value("' . $mypost->info . '","' . $date . '","' . $time . '")';
	mysql_fetch_array($db->dql($sql), MYSQL_ASSOC);
	$res = array();
	$res['sql'] = $sql;
	echo json_encode($res);
	break;

case 'test':

	// $log->info($_POST);
	$sql = 'select status from user where openid="' . $_GET['openid'] . '" ';
	$res = mysql_fetch_array($db->dql($sql), MYSQL_ASSOC);
	var_dump($res);
	break;

}
