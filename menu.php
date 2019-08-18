
<?php
// 自定义菜单设置类
// 运行这个脚本
// 直接设置自定义菜单

// 从SAE的memcache中取出当前最新的access_token
// 从公众号的会话界面进入授权页面
// 菜单需要设置成授权链接才可以
// 可以直接静默授权获取用户详细信息
// 静默授权,不会出现授权请求页面,直接跳转到auth.php

// state参数一定要带上,用来判断用户点击的是哪个菜单
// 授权连接
//  https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxf283b097a3fbf3d9&redirect_uri=http%3A%2F%2Fcookee.sinaapp.com%2Fauth.php&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect
// define('PATH','https://open.weixin.qq.com/connect/oauth2/authorize?appid=wxfcacdc74295aabe5&redirect_uri=http%3A%2F%2Fexamlab.cn%2FwechatClassApi%2Fauth.php&response_type=code&scope=snsapi_base&state=');
// STATE#wechat_redirect;

// jssdk用俩管理token
include 'jssdk.class.php';
// include('config.php');
$api = new JSSDK("wxfcacdc74295aabe5", "2465bb511cc5f5da62038e58841e78eb");

$menu = '{
      "button":[ 
                  {
                      "name": "我的课程", 
                      "type": "view",
                      "url":"http://examlab.cn/wechatClass/dist/#/courseList/me"
                  },
                  {
                      "name": "发现课程", 
                      "type": "view",
                      "url":"http://examlab.cn/wechatClass/dist/#/courseList/all"
                  },
                  {
                     "type":"view",
                     "name":"关于我们",
                     "url":"https://baidu.com"
                  }
               ]
 }';


var_dump($api->setMenu($menu));
