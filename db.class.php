<?php

//db.class.php
//include('config.php');

class DB
{

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
  //	public $pwd = '123456'; //密码(secret key)
  public $conn;
  public $port = 3306;

  public function __construct()
  {
    $this->conn = @mysql_connect($this->host . ':' . $this->port, $this->user, $this->pwd, true);
    mysql_select_db($this->dbname, $this->conn);
  }

  //查询
  public function dql($sql)
  {
    // 这句代码很重要,一定要设置,否则数据库不能存中文
    mysql_query("set names utf8mb4");
    $res = mysql_query($sql, $this->conn);
    return $res;
  }

  // 静态方法类  给::调用
  public static function d($sql)
  {
    // 这句代码很重要,一定要设置,否则数据库不能存中文
    mysql_query("set names utf8mb4");
    $res = mysql_query($sql);
    return $res;
  }

  public function __destruct()
  {
    mysql_close($this->conn);
  }
}
