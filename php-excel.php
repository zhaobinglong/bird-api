<?php

require_once __DIR__ . '/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';

include __DIR__ . '/core/lib/db.class.php';
$db = new db();

$filename = __DIR__ . '/doc/3.xlsx';
$objPHPExcelReader = PHPExcel_IOFactory::load($filename);

$sheet = $objPHPExcelReader->getSheet(1); // 读取第一个工作表(编号从 0 开始)
$highestRow = $sheet->getHighestRow(); // 取得总行数
$highestColumn = $sheet->getHighestColumn(); // 取得总列数

$arr = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
// 一次读取一列
$res_arr = array();
for ($row = 4; $row <= $highestRow; $row++) {
	$row_arr = array();
	for ($column = 0; $arr[$column] != 'N'; $column++) {
		$val = $sheet->getCellByColumnAndRow($column, $row)->getValue();
		$row_arr[] = $val;
	}
	var_dump($row_arr);
	// 插入HR销售人员
	// $sql = "insert into bird_seller(`user_name`,`user_code`,`company_code`, `company_name`, `sub_company_code`, `sub_company_name`, `team_code`, `team_name`, `identify_number`,  `bank_code`, `bank_name`, `phone_number`, `user_classify`) value('" . $row_arr[1] . "','" . $row_arr[2] . "','" . $row_arr[3] . "','" . $row_arr[4] . "','" . $row_arr[5] . "','" . $row_arr[6] . "','" . $row_arr[7] . "','" . $row_arr[8] . "','" . $row_arr[9] . "','" . $row_arr[10] . "','" . $row_arr[11] . "','" . $row_arr[12] . "', 'HR销售人员')";

	// $sql = "insert into bird_company(``user_name`, `user_code`,`company_code`, `company_name`, `sub_company_code`, `sub_company_name`,``) value('" . $row_arr[0] . "','" . $row_arr[1] . "','" . $row_arr[2] . "','" . $row_arr[3] . "')";
	// var_dump($sql);
	// $db->dql($sql);
	$res_arr[] = $row_arr;
}
