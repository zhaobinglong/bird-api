<?php

require_once __DIR__ . '/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';

include __DIR__ . '/core/lib/db.class.php';
$db = new db();

$excel = $_GET['page'];
$sheet = $_GET['sheet'];
$action = $_GET['action'];

$filename = __DIR__ . '/doc/' . $excel . '.xlsx';
$objPHPExcelReader = PHPExcel_IOFactory::load($filename);

$sheet = $objPHPExcelReader->getSheet($sheet); // 读取第一个工作表(编号从 0 开始)
$highestRow = $sheet->getHighestRow(); // 取得总行数
$highestColumn = $sheet->getHighestColumn(); // 取得总列数

$arr = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
// 一次读取一列
$res_arr = array();
for ($row = 5; $row <= $highestRow; $row++) {
	$row_arr = array();
	for ($column = 0; $arr[$column] != 'Q'; $column++) {
		$val = $sheet->getCellByColumnAndRow($column, $row)->getValue();

		// 将object格式转换为字符串格式
		if (is_object($val)) {
			$val = $val->__toString();
		}

		$row_arr[] = $val;
	}
	// if (empty($row_arr[1])) {
	// 	continue;
	// }

	if ($action == '1') {
		print_r($row_arr);
		echo "<br/>";
	} elseif ($action == '2') {
		// 插入HR销售人员
		$sql = "insert into bird_seller(`user_name`,`user_code`,`company_code`, `company_name`, `sub_company_code`, `sub_company_name`, `team_code`, `team_name`, `identify_number`,  `bank_code`, `bank_name`, `phone_number`, `user_classify`) value('" . $row_arr[1] . "','" . $row_arr[2] . "','" . $row_arr[3] . "','" . $row_arr[4] . "','" . $row_arr[5] . "','" . $row_arr[6] . "','" . $row_arr[7] . "','" . $row_arr[8] . "','" . $row_arr[9] . "','" . $row_arr[10] . "','" . $row_arr[11] . "','" . $row_arr[12] . "', 'HR销售人员')";
		$res = $db->dql($sql);
		var_dump($res);
	} elseif ($action == '3') {
		// 插入非HR销售人员
		$sql = "insert into bird_seller(`user_code`,`user_name`, `identify_number`, `bank_code`, `bank_name`, `phone_number`,`user_classify`) value('" . $row_arr[2] . "','" . $row_arr[11] . "','" . $row_arr[12] . "','" . $row_arr[13] . "','" . $row_arr[14] . "','" . $row_arr[15] . "','非HR销售人员')";
		$res = $db->dql($sql);
		var_dump($res);
		echo "<br/>";
	} elseif ($action == '4') {
		// 通过手机号码更改销售人员的归属代码
		$sql = "update bird_seller set team_code='45010814' where phone_number='" . $row_arr[15] . "'";
		$res = $db->dql($sql);
		var_dump($res);
		echo "<br/>";
	} else {
		echo '未知的action';
	}
	// $res_arr[] = $row_arr;
}
