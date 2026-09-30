<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



require 'Classes/PHPExcel.php';
require 'Classes/PHPExcel/Writer/Excel2007.php';

// Create new PHPExcel object
$spreadsheet = new PHPExcel();
$spreadsheet->setActiveSheetIndex(0)->setCellValue('A1', 'Hello, Excel!');

// Set headers to force download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="example.xlsx"');
header('Cache-Control: max-age=0');

// Create writer and output the file
$writer = new PHPExcel_Writer_Excel2007($spreadsheet);
$writer->save('php://output');
exit;

?>