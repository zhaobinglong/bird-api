<?php
class UPLOAD
// 类名到底是大写还是小写好呢？
{
	var $FormName; //文件域名称
	var $Directroy; //上传至目录
	var $MaxSize; //最大上传大小
	var $CanUpload; //是否可以上传
	var $doUpFile; //上传的文件名
	var $sm_File; //缩略图名称
	var $Error; //错误参数

	public function __construct() {
		// var_dump('ok');
	}

	// 上传文件
	// $from  前端同步过来的数据
	public function startUpload($file, $uploadPath, $from) {
		// pic应该是前段设置的数组名字
		//  for ($i=0; $i < count($file["pic"]["tmp_name"]); $i++) {
		// $file是一个数组，保存了所有图片的信息
		$fileName = $file['file']["tmp_name"];
		// '临时文件路径+文件名','服务器的根路径+新的文件名'
		$name = $this->newName($file['file']["name"]);
		// echo $name;
		$res = move_uploaded_file($fileName, $uploadPath . $name);
		// var_dump($res);
		//  }
		$obj['name'] = $name;
		return json_encode($obj);

	}

	// 给图片重新命名  当前时间戳+一个长度的随机字符串
	public function newName($name) {
		date_default_timezone_set('prc');
		$str = date('YmdHis', time()) . $this->randFive();
		return $str . strrchr($name, '.');
	}

	// 生成指定长度的随机字符串
	public function randFive() {
		return rand(10000, 99999);
	}

}
