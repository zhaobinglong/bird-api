<?php

// 授权类
class log {

	public $path; //日志文件路径

	// 在这里定义微信公众号的id和secret
	// 注意  只有服务号的才可以
	public function __construct($path) {
		$this->path = $path;
	}

	// 普通信息
	public function info($cont) {
		$name = date('Y-m-d', time()) . '.log'; //日志文件以天来命名，如2017-10-01.log
		$file = $this->path . $name;
		file_put_contents($file, '[info]' . date('Y-m-d H:i:s', time()) . '--' . $cont . "\r\n", FILE_APPEND);
	}
	//  错误信息
	public function error($cont) {
		$name = date('Y-m-d', time()) . '.log'; //日志文件以天来命名，如2017-10-01.log
		$file = $this->path . $name;
		file_put_contents($file, '[error]' . date('Y-m-d H:i:s', time()) . '--' . $cont . "\r\n", FILE_APPEND);
	}
	//  成功
	public function success($cont) {
		$name = date('Y-m-d', time()) . '.log'; //日志文件以天来命名，如2017-10-01.log
		$file = $this->path . $name;
		file_put_contents($file, '[success]' . date('Y-m-d H:i:s', time()) . '--' . $cont . "\r\n", FILE_APPEND);
	}

}
?>
