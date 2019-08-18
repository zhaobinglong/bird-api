<?php

// 全局配置文件

//图片根目录
defined('BASE_IMG') or define('BASE_IMG', 'http://examlab.cn/img/');

//日志存放目录
defined('LOG_PATH') or define('LOG_PATH', '../log/');

//二维码图片存放目录
defined('QRCODE_PATH') or define('QRCODE_PATH', '../img/');

// UNIBBS
defined('APPID') or define('APPID', 'wxb518954af3e70b39');
defined('APPSECRET') or define('APPSECRET', '0e7ee5f0b1a5748e5ad7d5618ee01a29');

//出个二手小程序的私密信息
// define('APPID', 'wx4dedf206966c7f2d');
// define('APPSECRET', '36934ee01de40488294f37d8dbbefb63');

// 赛博科技服务号的私密信息
defined('SAIBO_APPID') or define('SAIBO_APPID', 'wxbeb454d1e270db32');
defined('SAIBO_APPSECRET') or define('SAIBO_APPSECRET', '17bd477b669a9b976c450930fbddc46d');
defined('SAIBO_AES') or define('SAIBO_AES', 'QTr8wGFws55ZB4Yl5u2vQ3j6rNj41slXL5Bq0LEDXEQ');
defined('SAIBO_TOKEN') or define('SAIBO_TOKEN', 'unigoods');

//define('SAIBO_APPID', 'wxbeb454d1e270db32');
//define('SAIBO_APPSECRET', '17bd477b669a9b976c450930fbddc46d');

// 中天达智能历史账号
// defined('ZTD_APPID') or  define('ZTD_APPID', 'wxecfb3da15240cc8c');
// defined('ZTD_APPSECRET') or  define('ZTD_APPSECRET', '20b53a227c560df5cdaa0a374171e0ac');

// 中天达智能新版账号
defined('ZTD_APPID') or define('ZTD_APPID', 'wx27b965480e29803f');
defined('ZTD_APPSECRET') or define('ZTD_APPSECRET', 'da7ae43fff8157451b821a13ac679355');

// 腾讯地图KEY
defined('MAPKEY') or define('MAPKEY', 'ZT2BZ-C7FWP-DUMD2-VASMB-EUKXJ-ADF7N');

defined('__ROOT__') or define('__ROOT__', realpath(__DIR__));

// 数据库配置相关
defined('MYSQL_NAME') or define('MYSQL_NAME', 'ershou');
defined('MYSQL_HOST') or define('MYSQL_HOST', 'localhost');
defined('MYSQL_PORT') or define('MYSQL_PORT', '');
defined('MYSQL_USER') or define('MYSQL_USER', 'root');
defined('MYSQL_PASS') or define('MYSQL_PASS', 'root');
defined('MYSQL_CONN') or define('MYSQL_CONN', '');

defined('REDIS_HOST') or define('REDIS_HOST', '127.0.0.1');
defined('REDIS_PSW') or define('REDIS_PSW', '');
defined('REDIS_PORT') or define('REDIS_PORT', '6379');
defined('REDIS_DB') or define('REDIS_DB', '0');

?>
