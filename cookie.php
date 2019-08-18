<?php

$userInfo='{"openid":"op_Gstw_0x_IpVtt30CacptO27QY","nickname":"laputa","sex":1,"language":"zh_CN","city":"厦门","province":"福建","country":"中国","headimgurl":"http:\/\/wx.qlogo.cn\/mmopen\/50HcP4UOeLVzCqDqdzEuCBZrCO27zLVCKZ1a3Xm8z4iaa4ZSia0osGKHmIu0CEmKoVNIN4D0QdaQD13DgOMu0pXQ\/0","privilege":[],"unionid":"ozXOnuE9VEg9Dp1OpTXX4kcDN20A"}';

// $openid='op_Gstw_0x_IpVtt30CacptO27QY';

setcookie('user',$_GET['openid'],time()+86400*30,"/");
// setcookie('user',$openid,time()+86400*30,"/");
 ?>
