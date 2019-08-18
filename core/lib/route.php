<?php

// namespace core\lib;

// 路由类
class Route {
	public $ctrl;
	public $action;
	public $params;

	public function __construct() {
		// api路由
		// api.com/user/login => api.com/index.php/user/login

		// 拿到类名和方法
		$path = $_SERVER['REQUEST_URI'];
		$arr = explode("/", $path);
		$this->ctrl = $arr[1];
		$this->action = $arr[2];

		// 拿到多余出来的参数，这将是get过来的参数

		$count = count($arr);
		$i = 3;
		while ($i < $count) {
			$_GET[$arr[$i]] = $arr[$i + 1];
			$i = $i + 2;
		}

		$this->params = $_GET;
	}

}