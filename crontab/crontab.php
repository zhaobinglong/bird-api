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


// 每过半个小时推送一次
// 查询所有设置到这个时间的用户，

$time = date('H/i',time());
echo $time;

$sql='select b.openid,b.subject,s.title,s.startDate,s.len,s.push_cont,u.push_time from buy b join subject s on b.status=1 and b.subject=s.id inner join user u on u.openid=b.openid and u.push_time="'.$time.'"';
echo $sql;
  
$res=$db->dql($sql);
$data=array();
while($row = mysql_fetch_array($res,MYSQL_ASSOC)){
   $row['push_cont']=unserialize($row['push_cont']);
   array_push($data,$row);
}


var_dump('购买的课程数量'.count($data));
for ($i=0; $i <count($data); $i++) { 

     echo '第'.$i.'次推送';
     // var_dump($data[$i]);
     $Date_1 = date("Y-m-d");
     $Date_2 = $data[$i]['startDate'];
     var_dump($Date_1.'--'.$Date_2);
     $d1 = strtotime($Date_1);
     $d2 = strtotime($Date_2);
     $days = round(($d1-$d2)/3600/24)+1; //计算相差两天，实际的是第三天的课程
     var_dump($days);
      if($data[$i]['push_cont'][$days]['title'] == ''){
         $data[$i]['push_cont'][$days]['title'] = '等待后台设置推送内容';
         $data[$i]['push_cont'][$days]['title_color'] = '#333333';
      };

      if($data[$i]['push_cont'][$days]['cont'] == ''){
         $data[$i]['push_cont'][$days]['cont'] = '等待后台设置推送内容';
         $data[$i]['push_cont'][$days]['cont_color'] = '#333333';
      };      

      $send='{
                 "touser":"'.$data[$i]['openid'].'",
                 "template_id":"L9tdWIhyHwrpE5f-Lv-CmR9XnISYBUT5-S2J86gWxsE",
                 "url":"http://examlab.cn/wechatClass/dist/#/index/home/'.$data[$i]['subject'].'/'.$Date_1.'",          
                 "data":{
                         "first": {
                             "value":"'.$data[$i]['push_cont'][$days]['title'].'",
                             "color":"'.$data[$i]['push_cont'][$days]['title_color'].'"
                         },
                         "keyword1":{
                             "value":"'.$data[$i]['push_cont'][$days]['cont'].'",
                             "color":"'.$data[$i]['push_cont'][$days]['cont_color'].'"
                         },
                         "keyword2": {
                             "value":"'.date('Y-m-d H:i:s',time()).'",
                             "color":"#333"
                         },
                         "remark":{
                             "value":"",
                             "color":"#173177"
                         }
                 }
             }' ;
       // var_dump($data)
	$jssdk->push($send);
    echo '第'.$i.'次结束';
};
	





