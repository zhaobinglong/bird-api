<?php

require_once __DIR__ . '/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';

include __DIR__ . '/core/lib/db.class.php';
$db = new db();

$filename = __DIR__ . '/doc/2.xlsx';
$objPHPExcelReader = PHPExcel_IOFactory::load($filename);

$sheet = $objPHPExcelReader->getSheet(0); // 读取第一个工作表(编号从 0 开始)
$highestRow = $sheet->getHighestRow(); // 取得总行数
$highestColumn = $sheet->getHighestColumn(); // 取得总列数

$arr = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
// 一次读取一列
$res_arr = array();
for ($row = 2; $row <= $highestRow; $row++) {
	$row_arr = array();
	for ($column = 0; $arr[$column] != 'E'; $column++) {
		$val = $sheet->getCellByColumnAndRow($column, $row)->getValue();
		$row_arr[] = $val;
	}

	$sql = "insert into bird_company(`company_code`, `company_name`, `sub_company_code`, `sub_company_name`) value('" . $row_arr[0] . "','" . $row_arr[1] . "','" . $row_arr[2] . "','" . $row_arr[3] . "')";
	// var_dump($sql);
	$db->dql($sql);
	$res_arr[] = $row_arr;
}
