<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);

require_once "../lib/WxPay.Api.php";
require_once '../lib/WxPay.Notify.php';
require_once 'log.php';


require_once '../../db.class.php';
// $db = new DB();

		$sql = 'update buy set status=1 where out_trade_no="148456670220170806134655"';
		// Log::DEBUG("sql:".$sql );
		echo $sql;
				DB::d($sql);

	