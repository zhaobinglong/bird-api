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

// 加载配置文件，配置文件应该和入口文件放在一起
require_once 'config.php';

//异常处理
require_once 'error_handler.php';

// 加载路由类
// include __DIR__ . '/core/lib/route.php';

define('DEBUG', true); //是否开启调试模式
// 引入业务类。业务类可能有多个

// 引入数据库连接句柄 数据库句柄只有一个
include __DIR__ . '/core/lib/db.class.php';

// 在new业务类的时候，需要把数据库连接的pdo实例传递进去。
// 这里要实现自动加载业务类 自动实力话
// $user = new User($pdo);

// $res = $user->login('oBZdn1skS1r-qndlwjsRtHQeO2Ww');
// $res = $user->throwError('oBZdn1skS1r-qndlwjsRtHQeO2Ww');

// // var_dump($res);

// var_dump($_SERVER);

// if (DEBUG) {
// 	$whoops = new \Whoops\Run;
// 	$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
// 	$whoops->register();
// } else {

// }

// dump(__DIR__);

// 框架入口类文件
class App {

	// 路由分发对象
	public $route;

	public $db;

	// 请求的方法
	private $_method;

	// 请求的资源名称
	private $_resource;

	// 允许请求的资源列表
	// 有多少种有业务类型，就可以有多少种资源
	private $_allowResource = ['user', 'goods'];

	// 允许请求的方法
	private $_allowMethod = ['GET', 'POST'];

	public function __construct() {
		// $this->route = new Route();
		$this->db = new db();
		// dump(__DIR__ . '/core/lib/route.php');
	}

	// 类自动加载
	// 出于性能考虑，部分情况下，加载过的类，就不要加载了
	// static public function load($class) {
	// 	$file = __DIR__ . '/core/lib/' . $class . '.php';
	// 	if (is_file($file)) {
	// 		include $file;
	// 	} else {
	// 		return false;
	// 	}

	// }

	// 入口类只有一个方法，就是启动框架
	// 根据路径和方法，实现不同的操作
	// 自动加载对应的类文件
	// 针对微信服务器验证需要单独处理一次
	public function run() {

		// 拿到类名和方法
		// $path = $_SERVER['REQUEST_URI'];
		// $arr = explode("/", $path);
		// $this->ctrl = $_GET['ctrl'];
		// $this->action = $_GET['action'];

		// 微信服务器验证
		// 我觉得服务器验证这段代码应该单独放一个文件中去
		if (isset($_GET['echostr'])) {
			include __DIR__ . '/core/lib/weixin.php';
			$class = new weixin($this->db);
			$class->valid();

			// 拥有类文件，调用类文件
		} elseif (isset($_GET['ctrl'])) {
			$ctrl = $_GET['ctrl'];

			if (isset($_GET['action'])) {
				$action = $_GET['action'];
				include __DIR__ . '/core/lib/' . $ctrl . '.php';
				$class = new $ctrl($this->db);
				$class->$action($_GET);
			} else {
				// 正常情况下，action一定存在
				// 但是crontab通过url的方式，只能识别第一个参数，无法是被第二个参数
				include __DIR__ . '/core/lib/' . $ctrl . '.php';
				$class = new $ctrl($this->db);
				$class->crontab();

			}
			// 没有类文件，默认为微信的事件推送
		} else {
			include __DIR__ . '/core/lib/weixin.php';
			$class = new weixin($this->db);
			$class->responseMsg();
		}

	}
}

$app = new App();
$app->run();

// echo 'ok';