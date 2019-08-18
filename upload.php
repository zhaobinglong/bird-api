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
// include和include_once  如何取舍
include('upload.class.php');


$upload=new UPLOAD();


// var_dump($_FILES);
// var_dump($_POST);
// // var_dump($_FILES);
// // var_dump($_FILES['file']);
// var_dump($_SERVER['DOCUMENT_ROOT']);
// $_FILE  文件数据
// $_POST['key']  文件上传时同步过来的数据


if(isset($_POST['key'])){
	echo $upload->startUpload($_FILES,$_SERVER['DOCUMENT_ROOT']."/img/",$_POST['key']);
}else{
	echo $upload->startUpload($_FILES,$_SERVER['DOCUMENT_ROOT']."/img/",'');
}


// '临时文件名','服务器的根路径'
// $uploaddir = $_SERVER['DOCUMENT_ROOT']."carmen/upload/";
//
// var_dump($uploaddir);
//
// $name=time().strrchr($_FILES["pic"]["name"],'.');
//
// var_dump($name);
//
// $res=move_uploaded_file($_FILES["pic"]["tmp_name"],$uploaddir.'c.jpg');


// var_dump($upload);
// echo $upload->getName();
