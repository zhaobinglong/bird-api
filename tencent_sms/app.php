<?php

require "./src/index.php";

use Qcloud\Sms\SmsSingleSender;

// 短信应用SDK AppID
$appid = 1400219769;

// 短信应用SDK AppKey
$appkey = "ff080d03bd607502f4fed7e1a1752beb";

// 需要发送短信的手机号码
$phoneNumbers = ['13618516602'];

// 短信模板ID，需要在短信应用中申请
$templateId = 350202;

// 签名
$smsSign = "归巢科技";

// 指定模板ID单发短信
try {
	$ssender = new SmsSingleSender($appid, $appkey);
	$params = ["5678"];
	$result = $ssender->sendWithParam("86", $phoneNumbers[0], $templateId,
		$params, $smsSign, "", ""); // 签名参数未提供或者为空时，会使用默认签名发送短信
	$rsp = json_decode($result);
	echo $result;
} catch (\Exception $e) {
	echo var_dump($e);
}
