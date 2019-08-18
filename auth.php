<?php

   // 授权页面，专门用处理网页授权获取用户信息
   // 获取成功,就把用户的信息存入数据库
   //设置时区为东八区 网页编码utf8

   /**
    * 参数说明
    * code 授权成功后，微信跳转会带上code，用code可以换取用户信息
    * state 自定义的参数值  可以放一些来源信息
    */
   

   date_default_timezone_set('prc');
   header("Content-type: text/html; charset=utf-8");


   include 'db.class.php';
   include 'auth.class.php';
   // include 'log.class.php';


       $auth=new auth('wxfcacdc74295aabe5','2465bb511cc5f5da62038e58841e78eb');
       $db=new DB();

       // 用户必须在微信里面点击确认授权按钮才可以跳转
       // 授权成功后跳转到授权之前的页面
       // 拿到用户信息json字符串
       // 通过url中的code去换取用户的详细信息
       // 方便测试阶段  即使code不存在，程序正常运行
        $res='';
        if(isset($_GET['code'])){
           $res=$auth->getUserInfo($_GET['code']);
           setcookie('openid',$res->openid,time()+3600*12,"/");
        }


       // 过滤昵称中的特殊字符串，用空格替代
       // 然后再把首尾空格过滤
       $nickname=trim(preg_replace('/[\xF0-\xF7].../s','', $res->nickname)) ;


       //这里要先判断一下用户的信息是不是已经存在，
       $sql='select count(openid) from user where openid="'.$res->openid.'" ';
       $arr=mysql_fetch_array($db->dql($sql),MYSQL_ASSOC);


       if($arr['count(openid)']>0){
          // 用户已经存在,更新
           $sql='update user set headimgurl="'.$res->headimgurl.'",nickname="'.$nickname.'" where openid="'.$res->openid.'"';
           $db->dql($sql);
       }else{
          // 用户不存在,插入
           $sql='insert into user(openid,nickname,headimgurl,subscribe_date) values("'.$res->openid.'","'.$nickname.'","'.$res->headimgurl.'",'.time().' )';
           $resout=$db->dql($sql);

       }

       header('location:'.$_COOKIE["redirect_url"]);






