<?php

session_start();

include("../RestAPI.php");
require __DIR__ . '/../../../Libraries/PHPExcel/Classes/PHPExcel.php'; // Adjust path if needed
$Exporter = trim($_POST['company']);
$ForwardContractNo = trim($_POST['forwardContractNo']);

$api = GetForwardContractUtilisedReportDataAPI;

$bodyContent = array(
	"Exporter" => $Exporter,
	"ForwardContractNo" => $ForwardContractNo
);

$resGetForwardContractUtilisedReportDataAPI = CallAPI($api, "POST", "", $bodyContent);

$resList = $resGetForwardContractUtilisedReportDataAPI->Data->getFCUtilisedReportData;

$timestamp = date("YmdHis");
$spreadsheet = new PHPExcel();
$sheet = $spreadsheet->getActiveSheet();

// Set Font Size
$spreadsheet->getDefaultStyle()->getFont()->setSize(10);

// Set table heading bold text
$sheet->getStyle('1')->getFont()->setBold(true);

// Header Row
$headers = ['S.No', 'Forward Contract Number', 'Invoice No', 'Total Fc Value', 'Utilized Value', 'Utilized Date',
'Total Used Amount', 'Balance Amount'];


$colIndex = 'A';
foreach ($headers as $header) {
	$sheet->setCellValue($colIndex . '1', $header);
	$colIndex++;
}

// Column Widths
foreach (range('A', 'H') as $columnID) {
	$sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Align Specific Columns to Right
$columnsToAlign = ['A', 'D', 'E', 'G', 'H'];
foreach ($columnsToAlign as $col) {
	$sheet->getStyle($col)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
}

// Fill Data
$row = 2;
$CRow = $row;
$ERow = $row;
$FRow = $row;
foreach($resList as $item) {
	$sheet->setCellValue("A$row",$item->sno);
	$sheet->setCellValue("B$row", $item->ForwardContractNumber);
	$invoiceNoList = explode(',', $item->InvoiceNo);
	
	foreach($invoiceNoList as $invoiceNos) {
		$sheet->setCellValue("C$CRow",  trim(str_replace("<br>", "", $invoiceNos )));
		$CRow++;
	}
	$sheet->setCellValue("D$row", cleanNumber($item->TotalFcValue));
	$sheet->getStyle("D$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
	$utilizedValuesList = explode(', ', $item->UtilizedValue);
	
	foreach($utilizedValuesList as $utilizedValues) {
		$sheet->setCellValue("E$ERow", cleanNumber(str_replace("<br>", "", $utilizedValues )));
		$sheet->getStyle("E$ERow")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
		$ERow++;
	}
	$utilizedDatesList = explode(',', $item->UtilizedDate);
	
	foreach($utilizedDatesList as $utilizedDates) {
		$sheet->setCellValue("F$FRow", trim(str_replace("<br>", "", $utilizedDates )));
		$FRow++;
	}
	$sheet->setCellValue("G$row", cleanNumber($item->TotalUsedAmount));
	$sheet->getStyle("G$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
	$sheet->setCellValue("H$row", cleanNumber($item->BalanceAmount));
	$sheet->getStyle("H$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
	$CurrentRow = $row; // Store the current row number for merging
	$row = max($CRow, $ERow, $FRow); // Get the maximum row number for the next iteration
	if($CurrentRow != $row) {
		// Merge cells for the current row
		$MegeCellColumns = ["A","B","D","G","H"];
		foreach($MegeCellColumns as $col) {
			$sheet->mergeCells("$col$CurrentRow:$col$row");
			$sheet->getStyle("$col$CurrentRow")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		}
	}
	$CRow = $row;
	$ERow = $row;
	$FRow = $row;
	$row++;
}
	ob_clean();

$FileName = "FC-Utilized-Report";
    header("Content-type: application/vnd.ms-excel; charset=UTF-8");
    header('Content-Disposition: attachment; filename=' . $FileName . '-' . date("d-m-Y") . '.xlsx');

    // Write to Output
     $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
     $writer->save('php://output');
function cleanNumber($num) {
	return floatval(str_replace([',', "'"], '', $num)); // Remove comma & apostrophe, convert to number
}
?>