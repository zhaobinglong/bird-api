<?php

// include('wxlittle.class.php');
// $wxlittle = new wxlittle();
// $wxlittle->getLittleImg('222','detail');

// 测试redis
echo 'start:';

$redis = new redis();  
$redis->connect('127.0.0.1', 6379);  
// var_dump($redis -> get('zbl'));
echo "Server is running: " . $redis->ping();


?>
