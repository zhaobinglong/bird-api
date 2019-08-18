<?php

require_once __DIR__ . '/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';

include __DIR__ . '/core/lib/db.class.php';
$db = new db();

$filename = __DIR__ . '/doc/1.xlsx';
$objPHPExcelReader = PHPExcel_IOFactory::load($filename);

$sheet = $objPHPExcelReader->getSheet(0); // 读取第一个工作表(编号从 0 开始)
$highestRow = $sheet->getHighestRow(); // 取得总行数
$highestColumn = $sheet->getHighestColumn(); // 取得总列数

$arr = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
// 一次读取一列
$res_arr = array();
for ($row = 2; $row <= $highestRow; $row++) {
	$row_arr = array();
	for ($column = 0; $arr[$column] != 'O'; $column++) {
		$val = $sheet->getCellByColumnAndRow($column, $row)->getValue();
		$row_arr[] = $val;
	}

	$sql = "insert into bird_seller(`company_code`, `company_name`, `sub_company_code`, `sub_company_name`, `team_code`, `team_name`, `team_type`, `user_code`, `user_trans_code`, `user_name`, `user_param`, `user_type`, `user_post`, `user_office`) value('" . $row_arr[0] . "','" . $row_arr[1] . "','" . $row_arr[2] . "','" . $row_arr[3] . "','" . $row_arr[4] . "','" . $row_arr[5] . "','" . $row_arr[6] . "','" . $row_arr[7] . "','" . $row_arr[8] . "','" . $row_arr[9] . "','" . $row_arr[10] . "','" . $row_arr[11] . "','" . $row_arr[12] . "','" . $row_arr[13] . "')";
	var_dump($sql);
	$db->dql($sql);
	$res_arr[] = $row_arr;
}
