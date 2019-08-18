<?php

// 返回数据库连接句柄

// $db = new PDO('mysql:host=localhost;dbname=uniapi', 'root', 'root');

// // 设置pdo查询出来的数据格式和数据库数据格式保持一致
// $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

// return $db;

// $dbms='mysql';     //数据库类型
// $host=''; //数据库主机名
// $dbName='fangtai';    //使用的数据库
// $user='root';      //数据库连接用户名
// $pass='root';          //对应的密码
// $dsn="$dbms:host=$host;dbname=$dbName";

// try {
//     $dbh = new PDO($dsn, $user, $pass); //初始化一个PDO对象
//     echo "连接成功<br/>";
//     /*你还可以进行一次搜索操作
//     foreach ($dbh->query('SELECT * from FOO') as $row) {
//         print_r($row); //你可以用 echo($GLOBAL); 来看到这些值
//     }
//     */
//     $dbh = null;
// } catch (PDOException $e) {
//     die ("Error!: " . $e->getMessage() . "<br/>");
// }

class db {

	//SAE服务器端测试
	// public $dbname = MYSQL_NAME;
	// public $host = MYSQL_HOST;
	// public $port = MYSQL_PORT;
	// public $user = MYSQL_USER;//用户名(api key)
	// public $pwd = MYSQL_PASS;//密码(secret key)
	// public $conn;

	//  本地测试通过
	public $dbname = 'bird';
	public $host = 'localhost';
	public $user = 'root'; //用户名(api key)
	public $pwd = '5#KM1DE&Wr'; //密码(secret key)
	public $conn;
	public $port = 3306;

	public function __construct() {
		$this->conn = @mysql_connect($this->host . ':' . $this->port, $this->user, $this->pwd, true);
		mysql_select_db($this->dbname, $this->conn);
	}

	//查询
	public function dql($sql) {
		// 这句代码很重要,一定要设置,否则数据库不能存中文
		mysql_query("set names utf8mb4");
		$res = mysql_query($sql, $this->conn);
		return $res;
	}

	// 静态方法类  给::调用
	public static function d($sql) {
		// 这句代码很重要,一定要设置,否则数据库不能存中文
		mysql_query("set names utf8mb4");
		$res = mysql_query($sql);
		return $res;
	}

	public function __destruct() {
		mysql_close($this->conn);
	}

}
