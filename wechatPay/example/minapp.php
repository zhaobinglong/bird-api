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

ini_set('date.timezone', 'Asia/Shanghai');
//error_reporting(E_ERROR);
require_once "../lib/WxPay.Api.php";
require_once "../../db.class.php";
require_once "WxPay.JsApiPay.php";
require_once 'log.php';

//初始化日志
$logHandler = new CLogFileHandler("../logs/" . date('Y-m-d') . '.log');
$log = Log::Init($logHandler, 15);

// 实例化数据库
$db = new DB();

$rws_post = $GLOBALS['HTTP_RAW_POST_DATA'];
$data = json_decode($rws_post);

//①、获取用户openid
$tools = new JsApiPay();

// 在进入业务逻辑前，openid已经保存在cookie中
$openId = $data->openid;
// echo $openId;
// return false;
//②、统一下单
// printf_info($openId);
// return false;
$input = new WxPayUnifiedOrder();

// var_dump($openId);
// 商品描述 必须
$input->SetBody($data->desc);
$subject = 'test';

// 附加数据，可不填
// $input->SetAttach("test");

// 商户系统内部订单号码  这个号码非常重要，必须有
$out_trade_no = WxPayConfig::MCHID . date("YmdHis");
$input->SetOut_trade_no($out_trade_no);

// 订单金额，整形 单位为分 必须有
$input->SetTotal_fee($data->price);

// 订单生成时间  可不填
// $input->SetTime_start(date("YmdHis"));

// 订单有效时间  可不填
// $input->SetTime_expire(date("YmdHis", time() + 600));

// 订单优惠标记， 可不填
// $input->SetGoods_tag("test");

// 异步接收微信支付结果通知的回调地址，通知url必须为外网可访问的url，不能携带参数  必须有
$input->SetNotify_url("https://examlab.cn/uniapi/wechatPay/example/notify_ztd.php");

// 支付类型 必须有
$input->SetTrade_type("JSAPI");

// 用户openid
$input->SetOpenid($openId);

// 调用统一下单接口
$order = WxPayApi::unifiedOrder($input);

$jsApiParameters = $tools->GetJsApiParameters($order);

$obj = json_decode($jsApiParameters, TRUE);
$obj['out_trade_no'] = $out_trade_no;
echo json_encode($obj);

// 在系统中保存订单信息
// $sql = 'insert into buy(openid,subject,out_trade_no,date) value("' . $openId . '","' . $subject . '","' . $out_trade_no . '","' . strtotime("today") . '")';
// $db->dql($sql);

//③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
/**
 * 注意：
 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
 */
