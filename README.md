## 配置文件夹
- api
- img
- log

## 请在微信公众号后台添加授权回调链接
xx.com

## 微信授权跳转路径 只获取基本信息
https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxfcacdc74295aabe5&redirect_uri=http%3A%2F%2Fexamlab.cn%2FwechatClassApi%2Fauth.php&response_type=code&scope=snsapi_base&state=STATE#wechat_redirect;

## 授权链接高能预警
- 

## 回调地址，这个地址要做一次编码，写在授权链接中
https%3A%2F%2Fexamlab.cn%2Fapi%2Fauth.php

## 服务器ip地址
118.89.243.55

## 在调用任意一个jsapi之前需要做的事情

- 登录微信公众平台进入“公众号设置”的“功能设置”里填写“JS接口安全域名”
- 在需要调用JS接口的页面引入如下JS文件，（支持https）：http://res.wx.qq.com/open/js/jweixin-1.2.0.js
- 通过config接口注入权限验证配置，也就是说，你要有一个后台的api，是提供签名的
- 

## 关于微信支付

- 在支付后台绑定目录，支付目录必须精确到最后执行的文件


## 微信支付回调
{"appid":"wxfcacdc74295aabe5","bank_type":"CMB_CREDIT","cash_fee":"1","fee_type":"CNY","is_subscribe":"Y","mch_id":"1484566702","nonce_str":"gnyle0ul0lkumkpjvnaa8rygen6op4ld","openid":"oBZdn1iFVTjQQx3pEpouMgslYt50","out_trade_no":"148456670220170806121527","result_code":"SUCCESS","return_code":"SUCCESS","sign":"DCE58C8F73EF70131792CF647983F838","time_end":"20170806121534","total_fee":"1","trade_type":"JSAPI","transaction_id":"4008722001201708064772496531"}


## omIsn41XFxxAeVaKUSP0N_NixdSY

oq7MT0bj03T-OJIK3HUPX04EB-gY

## 测试hook有没有用


