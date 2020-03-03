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

include 'http.class.php';
require_once __ROOT__ . '/core/common/goods_redis.php';

class bird
{

  // 数据库句柄
  private $db;
  private $http;
  public $user = 'GC001';
  public $password = '123';
  public $sender = '002';
  public $server_version = '00000000';
  public $agencyCode = '000041100188';
  public $uuid;
  public $flowintime;
  public $check_tpl = "<?xml version='1.0' encoding='utf-8'?>
							<ApplyInfo>
							  <requesthead>
							    <user>%s</user>
							    <password>%s</password>
							    <server_version>%s</server_version>
							    <sender>%s</sender>
							    <uuid>%s</uuid>
							    <flowintime>%s</flowintime>
							  </requesthead>
							  <BODY>
							    <exchangeno>%s</exchangeno>
							  </BODY>
							</ApplyInfo>";

  // 构造函数，将pdo句柄传递给类
  public function __construct($db)
  {
    $this->db = $db;
    $this->http = new http();
    $this->uuid = $this->createUuid(); // 随机字符串，每次都不一样 bc764b51-118b-44cf-ae28-812b9a221926
    $this->flowintime = $this->getMsecTime();
  }

  // 通过手机号码获取二维码，如果有多个，只返回一个
  public function sellerRegister()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);

    $sql = "select * from bird_seller where phone_number='" . $mypost->phone_number . "' and user_status!='0' order by id desc limit 1";
    $res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
    $this->sendData($res, $sql);
  }

  // 通过HR号码获取正式HR销售的信息
  public function getSellerByCode()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);

    $sql = "select * from bird_seller where user_code='" . $mypost->user_code . "' and user_classify='HR销售人员' and user_status!='0' limit 1";
    $res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);
    $this->sendData($res);
  }

  // 获取所有的注册用户
  public function getSeller()
  {
    $sql = "select * from bird_seller where user_status!='0'";
    $res = $this->db->dql($sql);
    $data = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      array_push($data, $row);
    }
    $this->sendData($data);
  }

  // 获取全部的公司信息
  public function getCompanys()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);

    $sql = "select * from bird_company";

    $res = $this->db->dql($sql);
    $data = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      array_push($data, $row);
    }
    $this->sendData($data);
  }

  // 销售人员信息更新（以手机号码为准）
  // 目前手机号码不是唯一列，不能使用sql直接完成更新和插入
  public function updateSeller()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);

    $sql = "select phone_number from bird_seller where phone_number='" . $mypost->phone_number . "'";
    $res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);

    $sql2 = '';
    if (isset($res['phone_number'])) {
      $sql2 = "update bird_seller set bank_code='" . $mypost->bank_code . "',bank_name='" . $mypost->bank_name . "',company_name='" . $mypost->company_name . "',company_code='" . $mypost->company_code . "',sub_company_code='" . $mypost->sub_company_code . "',sub_company_name='" . $mypost->sub_company_name . "',team_code='" . $mypost->team_code . "',team_name='" . $mypost->team_name . "',user_code='" . $mypost->user_code . "', user_name='" . $mypost->user_name . "', identify_number='" . $mypost->identify_number . "', user_classify='" . $mypost->user_classify . "' where phone_number='" . $mypost->phone_number . "'";
    } else {
      $sql2 = "insert into bird_seller(bank_code, bank_name, company_name, company_code, sub_company_code, sub_company_name, team_code, team_name, user_code, user_name, identify_number, phone_number, user_classify, user_from) value('" . $mypost->bank_code . "','" . $mypost->bank_name . "','" . $mypost->company_name . "','" . $mypost->company_code . "','" . $mypost->sub_company_code . "','" . $mypost->sub_company_name . "','" . $mypost->team_code . "','" . $mypost->team_name . "','" . $mypost->user_code . "', '" . $mypost->user_name . "','" . $mypost->identify_number . "','" . $mypost->phone_number . "','" . $mypost->user_classify . "','" . $mypost->user_from . "')";
    }

    $res2 = $this->db->dql($sql2);
    $this->sendData($res2);
  }

  // 用户提交注册信息
  // $mypost->user_status = 0 用户被删除
  public function sellerApply()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);

    $sql = '';
    if (isset($mypost->id)) {
      $sql = "update bird_seller set bank_code='" . $mypost->bank_code . "',bank_name='" . $mypost->bank_name . "',company_name='" . $mypost->company_name . "',company_code='" . $mypost->company_code . "',sub_company_code='" . $mypost->sub_company_code . "',sub_company_name='" . $mypost->sub_company_name . "',team_code='" . $mypost->team_code . "',team_name='" . $mypost->team_name . "',team_type='" . $mypost->team_type . "',user_code='" . $mypost->user_code . "', user_name='" . $mypost->user_name . "', identify_number='" . $mypost->identify_number . "', user_classify='" . $mypost->user_classify . "', phone_number='" . $mypost->phone_number . "', user_rules='" . $mypost->user_rules . "', user_status='" . $mypost->user_status . "' where id='" . $mypost->id . "'";
    } else {
      // 限制手机号码，不可以重复
      $sql = "insert into bird_seller(bank_code, bank_name, company_name, company_code, sub_company_code, sub_company_name, team_code, team_name, team_type, user_code, user_trans_code, user_name, user_param, user_type, user_post, user_office, identify_number, phone_number, user_classify, user_from, user_rules) value('" . $mypost->bank_code . "','" . $mypost->bank_name . "','" . $mypost->company_name . "','" . $mypost->company_code . "','" . $mypost->sub_company_code . "','" . $mypost->sub_company_name . "','" . $mypost->team_code . "','" . $mypost->team_name . "','" . $mypost->team_type . "','" . $mypost->user_code . "','" . $mypost->user_trans_code . "','" . $mypost->user_name . "','" . $mypost->user_param . "','" . $mypost->user_type . "','" . $mypost->user_post . "','" . $mypost->user_office . "','" . $mypost->identify_number . "','" . $mypost->phone_number . "','" . $mypost->user_classify . "','" . $mypost->user_from . "','" . $mypost->user_rules . "')";
    }
    $res = $this->db->dql($sql);
    $this->sendData($res, $sql);
  }

  public function login()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);

    // 根据手机号码，获取号码对应的四位数字验证码
    $sql = "select * from sms where phone='" . $mypost->phone . "' order by createtime desc limit 1 ";
    $res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);

    if ($res['code'] == $mypost->code) {
      $this->sendData(true, $res);
    } else {
      $this->sendData(false, $res);
    }
    // $redis = new GoodsRedis();
    // $code = $redis->getPhoneCode($mypost->phone);
    // if ($code == $mypost->code) {
    // 	$this->sendData(true);
    // } else {
    // 	$this->sendData(false);
    // }
  }

  public function loginout()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);

    $this->sendData(true);
  }

  // 调用腾讯云官方接口发送短信
  public function sendSMS()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);
    $strRand = $this->str_rand(10); //URL 中的 random 字段的值
    $sign = $this->getSMSSign($mypost->phone, $strRand);
    $code = $this->str_rand(4);
    $data = array(
      "ext" => "", //用户的 session 内容，腾讯 server 回包中会原样返回，可选字段，不需要就填空
      "extend" => "",
      'params' => array($code), // 短信中的参数
      'sig' => $sign, // 计算出来的密钥
      "sign" => "归巢科技", // 短信一开始的签名字符串
      'tel' => array('mobile' => $mypost->phone, 'nationcode' => '86'),
      'time' => time(),
      'tpl_id' => 350202,
    );
    $url = 'https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid=1400219769&random=' . $strRand;
    // $res = $this->http->tencentHttpsPost($url, json_encode($data));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $httpheader[] = "Accept:application/json";
    $httpheader[] = "Accept-Encoding:gzip,deflate,sdch";
    $httpheader[] = "Accept-Language:zh-CN,zh;q=0.8";
    $httpheader[] = "Connection:close";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_ENCODING, "gzip");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $ret = curl_exec($ch);
    curl_close($ch);

    // $redis = new GoodsRedis();
    // $redis->setPhoneCode($mypost->phone, $code);
    $sql = "insert into sms(phone, code, createtime) value('" . $mypost->phone . "','" . $code . "','" . time() . "' )";
    $res = $this->db->dql($sql);
    $this->sendData(json_decode($ret), $res);
  }

  // 获取短信发送的签名密钥
  public function getSMSSign($phone, $strRand)
  {
    $strMobile = $phone; //tel 的 mobile 字段的内容
    $strAppKey = "ff080d03bd607502f4fed7e1a1752beb"; //sdkappid 对应的 appkey，需要业务方高度保密
    $strTime = time(); //UNIX 时间戳
    $sign = hash('sha256', 'appkey=' . $strAppKey . '&random=' . $strRand . '&time=' . $strTime . '&mobile=' . $strMobile);
    return $sign;
  }

  /**
   * $length    要输出的字符串长度
   * $char       随机字符串数组，要是生成中文的，可以在这里放入汉字
   */

  public function str_rand($length = 5, $char = '0123456789')
  {
    if (!is_int($length) || $length < 0) {
      return false;
    }
    $string = '';
    for ($i = $length; $i > 0; $i--) {
      $string .= $char[mt_rand(0, strlen($char) - 1)];
    }
    return $string;
  }

  // 根据关键词检索百度地图获取地址列表
  public function getAddressList()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);
    $url = 'https://api.map.baidu.com/place/v2/suggestion?query=' . $mypost->key . '&region=' . $mypost->city . '&city_limit=true&output=json&ak=1FPL3MBp6Np1aNnewZYRLrYDgwk6PZwt';
    $res = $this->http->httpGet($url);

    header('Content-type: application/json');
    $this->sendData(json_decode($res), $url);
  }

  // 插入数据
  public function push()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);
    $sql = '';
    if (isset($mypost->id)) {
      $sql = "update bird set cover_img='" . $mypost->cover_img . "',police_receipt='" . $mypost->police_receipt . "',name='" . $mypost->name . "',height='" . $mypost->height . "',birthday='" . $mypost->birthday . "',info='" . $mypost->info . "',lost_date='" . $mypost->lost_date . "',address='" . $mypost->address . "',remark='" . $mypost->remark . "',phone='" . $mypost->phone . "', status='" . $mypost->status . "' where id='" . $mypost->id . "'";
    } else {
      $sql = "insert into bird(cover_img,police_receipt,name,height,birthday,info,lost_date,address,phone,remark,create_time) value('" . $mypost->cover_img . "','" . $mypost->police_receipt . "','" . $mypost->name . "','" . $mypost->height . "','" . $mypost->birthday . "','" . $mypost->info . "','" . $mypost->lost_date . "','" . $mypost->address . "','" . $mypost->phone . "','" . $mypost->remark . "','" . time() . "')";
    }

    $res = $this->db->dql($sql);
    $this->sendData($res, $sql);
    $this->shortNote();
  }
  // 调用腾讯云官方接口发送短信给祝超
  public function shortNote()
  {

    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);
    $strRand = $this->str_rand(10); //URL 中的 random 字段的值
    $sign = $this->getSMSSign('18965127265', $strRand);
    $code = $this->str_rand(4);
    $data = array(
      "ext" => "", //用户的 session 内容，腾讯 server 回包中会原样返回，可选字段，不需要就填空
      "extend" => "",
      'params' => '发布走失', // 短信中的参数
      'sig' => $sign, // 计算出来的密钥
      "sign" => "归巢科技", // 短信一开始的签名字符串
      'tel' => array('mobile' => '18965127265', 'nationcode' => '86'),
      'time' => time(),
      'tpl_id' => 350202,
    );
    $url = 'https://yun.tim.qq.com/v5/tlssmssvr/sendsms?sdkappid=1400219769&random=' . $strRand;
    // $res = $this->http->tencentHttpsPost($url, json_encode($data));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $httpheader[] = "Accept:application/json";
    $httpheader[] = "Accept-Encoding:gzip,deflate,sdch";
    $httpheader[] = "Accept-Language:zh-CN,zh;q=0.8";
    $httpheader[] = "Connection:close";
    curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_ENCODING, "gzip");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $ret = curl_exec($ch);
    curl_close($ch);
  }



  // 提交线索
  public function clue()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);
    $sql = "insert into clue(main_id,phone,cover_img,info,address,create_time) value('" . $mypost->main_id . "','" . $mypost->phone . "','" . $mypost->cover_img . "','" . $mypost->info . "','" . json_encode($mypost->address, JSON_UNESCAPED_UNICODE) . "','" . time() . "')";

    $res = $this->db->dql($sql);
    $this->sendData($res, $sql);
  }

  // 添加儿童档案
  public function addChild()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);
    $sql = '';
    if (isset($mypost->id)) {
      $sql = "update child set cover_img='" . $mypost->cover_img . "',name='" . $mypost->name . "',gender='" . $mypost->gender . "',code='" . $mypost->code . "' where id='" . $mypost->id . "'";
    } else {
      $sql = "insert into child(cover_img,name,gender,code,parent) value('" . $mypost->cover_img . "','" . $mypost->name . "','" . $mypost->gender . "','" . $mypost->code . "','" . $mypost->parent . "')";
    }

    $res = $this->db->dql($sql);
    $this->sendData($res, $sql);
  }

  // 获取我的儿童档案
  public function getChild()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);
    $sql = 'select * from child where parent="' . $mypost->phone . '"';

    $res = $this->db->dql($sql);
    $data = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      array_push($data, $row);
    }
    $this->sendData($data, $sql);
  }

  // 获取走失儿童列表
  public function getList()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);
    $sql = 'select * from bird where (phone="' . $mypost->phone . '" or "' . $mypost->phone . '" = "") and status!="0" order by create_time desc';

    $res = $this->db->dql($sql);
    $data = array();

    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      array_push($data, $row);
    }

    $this->sendData($data, $sql);
  }

  // 获取线索列表
  public function getClue()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);
    $sql = 'select * from clue where phone="' . $mypost->phone . '" order by create_time desc';

    $res = $this->db->dql($sql);
    $data = array();

    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $row['address'] = json_decode($row['address']);
      array_push($data, $row);
    }

    $this->sendData($data, $sql);
  }

  // 获取走失儿童详情
  // 详情中同步获取线索
  public function getDetail()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);
    $sql = 'select * from bird where id="' . $mypost->id . '" limit 1';
    $res = mysql_fetch_array($this->db->dql($sql), MYSQL_ASSOC);

    $clue_sql = 'select * from clue where main_id="' . $mypost->id . '" order by create_time desc';
    $clues = $this->db->dql($clue_sql);
    $data = array();
    while ($row = mysql_fetch_array($clues, MYSQL_ASSOC)) {
      $row['address'] = json_decode($row['address']);
      array_push($data, $row);
    }

    $res['clues'] = $data;
    $this->sendData($res, $sql);
  }

  // 参数1：sql执行成功还是失败
  public function sendData($res, $sql = '')
  {
    $data['data'] = $res;
    $data['sql'] = $sql;
    $data['code'] = 200;
    echo json_encode($data);
  }

  public function throwError()
  {
    throw new Exception('openid已经存在', '0');
  }

  // 类私有函数，检查用户是否已经存在，私有方法
  private function _check()
  {
  }

  // 测试订单插入
  public function test()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);

    $res = $this->saveOrder($mypost);
    echo $res;
  }

  /**
   * 获取毫秒级别的时间戳
   */
  public function getMsecTime()
  {
    list($msec, $sec) = explode(' ', microtime());
    $msectime = (float) sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
    return $msectime;
  }

  /**
   * 生成uuid，格式：bc764b51-118b-44cf-ae28-812b9a221926
   */
  public function createUuid()
  {
    return $this->get_rand(8) . '-' . $this->get_rand() . '-' . $this->get_rand() . '-' . $this->get_rand() . '-' . $this->get_rand(12);
  }

  //生成指定长度随机字符串
  public function get_rand($len = 4)
  {
    $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
    mt_srand(10000000 * (float) microtime());
    for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $len; $i++) {
      $str .= $chars[mt_rand(0, $lc)];
    }
    return $str;
  }

  // 检查订单状态
  public function checkOrder()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);
    $textTpl = "<?xml version='1.0' encoding='utf-8'?>
				<ApplyInfo>
				  <requesthead>
				    <user>%s</user>
				    <password>%s</password>
				    <server_version>%s</server_version>
				    <sender>%s</sender>
				    <uuid>%s</uuid>
				    <flowintime>%s</flowintime>
				  </requesthead>
				  <BODY>
				    <exchangeno>%s</exchangeno>
				  </BODY>
				</ApplyInfo>";
    $xml = sprintf($textTpl, $this->user, $this->password, $this->server_version, $this->sender, $this->uuid, $this->getMsecTime(), $mypost->exchangeno);
    $url = 'http://113.12.195.135:8088/picc-sinosoft-consumer-gc/Picc/Cbc';
    $curl = curl_init();
    $header[] = "Content-type: text/xml";
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
    $res = curl_exec($curl);
    curl_close($curl);
    if ($res == '进入熔断器了') {
      $this->sendData('进入熔断器了');
    } else {
      $postObj = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
      $this->savePolicyno($postObj);
      $this->sendData($postObj);
    }
  }

  // 每次查询后，保存保单支付结果
  public function savePolicyno($postObj)
  {
    if ($postObj->responsehead->error_message == 'Success') {
      $sql = "update bird_order set policyno='" . $postObj->BODY->policynodatalist->policydata->policyno . "' where exchangeNo='" . $postObj->BODY->exchangeno . "'";
      $this->db->dql($sql);
    }
  }

  public function createrOrder()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);

    $this->saveOrder($mypost);

    $user = 'GC001';
    $password = '123';
    $sender = '002';
    $server_version = '00000000';

    $comCode = $mypost->comCode; // 归属机构代码
    $makeCode = $mypost->makeCode; // 出单机构代码, 归属机构和出单机构可以相同

    $handler1Code = $mypost->handler1Code; // 归属业务员代码，归属业务员-经办人-操作员可以相同
    $handlerCode = $mypost->handlerCode; // 经办人业务代码，归属业务员-经办人-操作员可以相同
    $operatorCode = $mypost->operatorCode; // 操作员业务代码，归属业务员-经办人-操作员可以相同

    $agencyCode = '000041100188'; // 北京经济公司渠道码，这是固定的，写死了
    $planCode = $mypost->planCode; // 保险种类，目前只有两种

    $insuredType = '1'; // 1投保人2被保险人

    $textTpl = "<?xml version='1.0' encoding='utf-8'?>
					<ApplyInfo>
					  <requesthead>
					    <user>%s</user>
					    <password>%s</password>
					    <server_version>%s</server_version>
					    <sender>%s</sender>
					    <uuid>%s</uuid>
					    <flowintime>%s</flowintime>
					  </requesthead>
					  <policyibofo>
					    <planCode>%s</planCode>
					    <comCode>%s</comCode>
					    <handler1Code>%s</handler1Code>
					    <makeCode>%s</makeCode>
					    <agencyCode>000041100188</agencyCode>
					    <handlerCode>%s</handlerCode>
					    <operatorCode>%s</operatorCode>
					    <insuredInfos>
					      <InsuredInfo>
					        <insureType>1</insureType>
					        <insuredName>%s</insuredName>
					        <identifyType>%s</identifyType>
					        <identifyNumber>%s</identifyNumber>
					        <phoneNumber>%s</phoneNumber>
					        <postAddress>%s</postAddress>
					        <banjiName></banjiName>
					      </InsuredInfo>
					      <InsuredInfo>
					        <insureType>2</insureType>
					        <insuredName>%s</insuredName>
					        <identifyType>%s</identifyType>
					        <identifyNumber>%s</identifyNumber>
					        <phoneNumber>%s</phoneNumber>
					        <postAddress>%s</postAddress>
					        <banjiName>%s</banjiName>
					      </InsuredInfo>
					    </insuredInfos>
					  </policyibofo>
					</ApplyInfo>";
    $xml = sprintf($textTpl, $user, $password, $server_version, $sender, $this->uuid, $this->flowintime, $planCode, $comCode, $handler1Code, $makeCode, $handlerCode, $operatorCode, $mypost->parent->insuredName, $mypost->parent->identifyType, $mypost->parent->identifyNumber, $mypost->parent->phoneNumber, $mypost->parent->postAddress, $mypost->child->insuredName, $mypost->child->identifyType, $mypost->child->identifyNumber, $mypost->child->phoneNumber, $mypost->child->postAddress, '');

    // 测试端口18088 正式端口8088
    $url = 'http://113.12.195.135:8088/picc-sinosoft-consumer-gc/Picc/Gc';

    $curl = curl_init();
    $header[] = "Content-type: text/xml";
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
    $res = curl_exec($curl);
    curl_close($curl);
    if ($res == '进入熔断器了') {
      $this->sendData($res);
    } else {
      try {
        $postObj = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_ERR_NONE);
        // 下单成功后才开始保存订单信息
        if ($postObj->responsehead->error_message == 'Success') {
          $this->updateOrder($this->uuid, $postObj);
        }
        $this->sendData($postObj);
      } catch (Exception $e) {
        $this->sendData($e);
      }
    }
  }

  public function saveOrder($data)
  {
    $insuredInfos = array(
      $data->child, $data->parent,
    );
    $sql = "insert into bird_order(user, password, server_version, sender, uuid, flowintime, planCode, comCode, makeCode, agencyCode, handlerCode, handler1Code, operatorCode, insuredInfos, phone, channel) value('" . $this->user . "','" . $this->password . "','" . $this->server_version . "','" . $this->sender . "','" . $this->uuid . "','" . $this->flowintime . "','" . $data->planCode . "','" . $data->comCode . "','" . $data->makeCode . "','" . $this->agencyCode . "','" . $data->handlerCode . "','" . $data->handler1Code . "','" . $data->operatorCode . "','" . serialize($insuredInfos) . "','" . $data->parent->phoneNumber . "', '" . $data->channel . "')";
    $this->db->dql($sql);
    return $sql;
  }

  // 保存下单编号和支付编号，这里还没有保单号，因为保单号必须付款才有
  public function updateOrder($uuid, $data)
  {
    $sql = "update bird_order set exchangeNo='" . $data->responsebody->infos->exchangeno . "', proposalNo='" . $data->responsebody->infos->info->proposalNo . "' where uuid='" . $uuid . "'";
    $this->db->dql($sql);
  }

  // 后台管理员管理订单
  public function manageOrder()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);
    $sql = "update bird_order set status='" . $mypost->status . "' where id='" . $mypost->id . "'";
    $res = $this->db->dql($sql);
    $this->sendData($res);
  }

  public function getOrders()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);
    $sql = "select * from bird_order where phone='" . $mypost->phone . "' and exchangeNo!='' order by flowintime desc";
    $res = $this->db->dql($sql);
    $data = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $row['insuredInfos'] = unserialize($row['insuredInfos']);
      array_push($data, $row);
    }
    $this->sendData($data);
  }

  // 获取已付款的订单
  public function getAllOrders()
  {
    $rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
    $mypost = json_decode($rws_post);
    $sql = "select o.id, o.planCode,o.handlerCode,o.insuredInfos,o.policyno,o.phone,o.channel,o.
flowintime,o.status,s.user_name,s.user_code,s.team_name,s.bank_code,s.bank_name,s.company_name,s.sub_company_name from bird_order as o left join bird_seller as s on o.channel=s.identify_number and s.user_status!='0'  where o.policyno!='' order by o.flowintime desc";
    $res = $this->db->dql($sql);
    $data = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      $row['insuredInfos'] = unserialize($row['insuredInfos']);
      array_push($data, $row);
    }
    $this->sendData($data);
  }

  // 每个小时的整点查询保单状态
  public function crontab()
  {
    $msectime_now = $this->getMsecTime();

    $msectime_before = $msectime_now - 60 * 60 * 1000;

    $sql = 'select * from bird_order where 	flowintime >' . $msectime_before;
    $res = $this->db->dql($sql);

    $data = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
      array_push($data, $row);
    }

    for ($i = 0; $i < count($data); $i++) {
      if (empty($data[$i]['exchangeNo'])) {
        continue;
      }
      $xml = sprintf($this->check_tpl, $this->user, $this->password, $this->server_version, $this->sender, $this->uuid, $this->getMsecTime(), $data[$i]['exchangeNo']);

      $url = 'http://113.12.195.135:8088/picc-sinosoft-consumer-gc/Picc/Cbc';
      $curl = curl_init();
      $header[] = "Content-type: text/xml";
      curl_setopt($curl, CURLOPT_URL, $url);
      curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
      curl_setopt($curl, CURLOPT_POST, true);
      curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
      curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
      $res = curl_exec($curl);
      curl_close($curl);
      if ($res == '进入熔断器了') {
        var_dump($res);
      } else {
        $postObj = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
        $this->savePolicyno($postObj);
        print_r($postObj);
        echo "<br/>";
      }
    }
  }

  // 获取分享图
  public function getShareImg($params)
  {

    $data = [
      'bk' => 'http://guichaokeji.com/bird-api/doc/qrcode_bk_1.jpeg',
      'param1' => 'xx',
      'phone' => $params['phone'],

    ];

    $url = urlencode('http://guichaokeji.com/bird/index.html#/buy/' . $params['phone']);
    $qrcode = 'http://qr.liantu.com/api.php?&w=200&text=' . $url;

    $newImg = $this->createImg($data, $qrcode);
    $this->sendData($newImg);
  }

  public function createImg($data, $codeImg)
  {
    $backImg = $data['bk'];
    // 添加二维码
    // 背景图宽度1037， /2=519
    // 二维码宽度200。 /2=100   519-100=419

    // 背景图高度1980
    // 背景图中二维码距离顶部200
    return $this->addPic($backImg, $codeImg, 200, 200, 445, 290, $data['phone']);
  }

  public function addPic($path_base, $path_logo, $imgWidth, $imgHeight, $dst_x, $dst_y, $new)
  {

    $image_base = $this->ImgInfo($path_base);
    $image_logo = $this->ImgInfo($path_logo);

    // 将image_base和image_logo合并在一起
    imagecopyresampled($image_base, $image_logo, $dst_x, $dst_y, 0, 0, $imgWidth, $imgHeight, imagesx($image_logo), imagesy($image_logo));

    // 生成一个合并后的新图$new
    $path = __ROOT__ . '/images/v2/' . $new . '.jpeg';
    imagejpeg($image_base, $path);
    return 'http://guichaokeji.com/bird-api/images/v2/' . $new . '.jpeg';
  }

  // 获取图片信息
  public function ImgInfo($img)
  {
    if (preg_match('/http(s)?:\/\//', $img)) {
      $fileSuffix = $this->getNetworkImgType($img);
    } else {
      $fileSuffix = pathinfo($img, PATHINFO_EXTENSION);
    }

    if (!$fileSuffix) {
      return false;
    }

    switch ($fileSuffix) {
      case 'jpeg':
        $theImage = @imagecreatefromjpeg($img);
        break;
      case 'jpg':
        $theImage = @imagecreatefromjpeg($img);
        break;
      case 'png':
        $theImage = @imagecreatefrompng($img);
        break;
      case 'gif':
        $theImage = @imagecreatefromgif($img);
        break;
      default:
        $theImage = @imagecreatefromstring(file_get_contents($img));
        break;
    }

    return $theImage;
  }

  /**
   * 获取网络图片类型
   * @param $url  网络图片url,支持不带后缀名url
   * @return bool
   */
  public function getNetworkImgType($url)
  {
    $ch = curl_init(); //初始化curl
    curl_setopt($ch, CURLOPT_URL, $url); //设置需要获取的URL
    curl_setopt($ch, CURLOPT_NOBODY, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3); //设置超时
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01; Windows NT 5.0)');
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); //支持https
    curl_exec($ch); //执行curl会话
    $http_code = curl_getinfo($ch); //获取curl连接资源句柄信息
    curl_close($ch); //关闭资源连接
    if ($http_code['http_code'] == 200) {
      $theImgType = explode('/', $http_code['content_type']);

      if ($theImgType[0] == 'image') {
        return $theImgType[1];
      } else {
        return false;
      }
    } else {
      return false;
    }
  }

  // 查找今天所有发布走失者
  public function FindIDcare()
  {
    // 查找发布走失的人名字
    $birdSql = 'select name,create_time from bird where status = ""';
    $birdSql_a =  $this->db->dql($birdSql);
    $birdNameArray = array();
    // 获取当天凌晨时间戳
    // print_r(strtotime(date("Y-m-d"), time()));

    while ($birdRow = mysql_fetch_array($birdSql_a, MYSQL_NUM)) {
      if (date('Y-m-d') == date("Y-m-d", $birdRow[1])) {
        array_push($birdNameArray, $birdRow);
      }
    }
    if (!empty($birdNameArray)) {
      $birdOrderArray = array();
      $birdOrderSql = 'select insuredInfos,uuid from bird_order where policyno is not null';
      $birdOrderSql_a = $this->db->dql($birdOrderSql);
      while ($birdOrderRow = mysql_fetch_array($birdOrderSql_a, MYSQL_NUM)) {
        $birdOrderRow[0]  = unserialize($birdOrderRow[0]);
        array_push($birdOrderArray, json_decode(json_encode($birdOrderRow), true));
      }
      // $apiArray = array();
      for ($i = 0; $i < count($birdOrderArray); $i++) {
        for ($k = 0; $k < count($birdNameArray); $k++) {
          if ($birdNameArray[$k][0] == $birdOrderArray[$i][0][0]['insuredName']) {
            $this->audit($birdOrderArray[$i][1]);
            continue;
          }
        }
      }
    } else {
      print '今天没人发布';
    }
  }

  // 检查出险审核是否通过
  public function audit($uuid)
  {
    // echo $uuid;
    $damagecode = '被人流冲散';
    $damageable = '通过';
    $this->sender = '001';
    $textTpl = "
      <?xml version=1.0 encoding=GBK?>
      <PACKET TYPE=REQUEST VERSION=1.0>
        <requesthead>
          <user>%s</user>
          <password>%s</password>
          <server_version>%s</server_version>
          <sender>%s</sender>
          <uuid>%s</uuid>
          <flowintime$>%s</flowintime>
        </requesthead>
        <requestbody>
        <damagecode>%s</damagecode>
        <damageable>%s</damageable>
      </requestbody>
      </PACKET>";
    $xml = sprintf($textTpl, $this->user, $this->password, $this->server_version, $this->sender, $uuid, $this->getMsecTime(), $damagecode, $damageable);
    var_dump($xml);
    $url = 'http://113.12.195.135:8088/picc-sinosoft-consumer-gc/Picc/Gc';
    $curl = curl_init();
    $header[] = "Content-type: text/xml";
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
    $res = curl_exec($curl);
    curl_close($curl);
    if ($res == '进入熔断器了') {
      $this->sendData('进入熔断器了');
    } else {
      $postObj = simplexml_load_string($res, 'SimpleXMLElement', LIBXML_NOCDATA);
      $this->savePolicyno($postObj);
      $this->sendData($postObj);
    }
  }
}
