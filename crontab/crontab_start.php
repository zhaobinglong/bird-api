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

include('db.class.php');
include('jssdk.class.php');


$db=new DB();
$jssdk = new JSSDK("wxfcacdc74295aabe5", "2465bb511cc5f5da62038e58841e78eb");



// 开课提醒。每天循环一次

$time = date("Y-m-d",strtotime("+1 day"));

$sql='select b.openid,b.subject,s.startDate,s.title,u.nickname from buy b right join subject s on b.status = 1 and b.subject = s.id join user u on u.openid=b.openid where s.startDate = "'.$time.'"';

$res=$db->dql($sql);
$data=array();
while($row = mysql_fetch_array($res,MYSQL_ASSOC)){
   array_push($data,$row);
}


for ($i=0; $i <count($data); $i++) { 

      $send='{
                 "touser":"'.$data[$i]['openid'].'",
                 "template_id":"DA7RB8ScievgE8bwCzjbXFqYcffGyVlknq6kjISdBNY",
                 "url":"http://examlab.cn/wechatClass/dist/#/courseList/me",          
                 "data":{
                         "userName": {
                             "value":"'.$data[$i]['nickname'].'",
                             "color":"#333"
                         },
                         "courseName":{
                             "value":"'.$data[$i]['title'].'",
                             "color":"#333"
                         },
                         "date": {
                             "value":"'.$time.'",
                             "color":"#333"
                         },
                         "remark":{
                             "value":"敬请关注",
                             "color":"#173177"
                         }
                 }
             }' ;

	$jssdk->push($send);

};
	





