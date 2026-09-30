<?php
// session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
require __DIR__ . '/../../../Libraries/PHPExcel/Classes/PHPExcel.php'; // Adjust path if needed

$Plant = trim($_GET["Plant"]);
$PlantName = trim($_GET["PlantName"]);
$Center = trim($_GET["Center"]);
$CenterName = trim($_GET["CenterName"]);
$StartDate = trim($_GET["StartDate"]);
$EndDate = trim($_GET["EndDate"]);
$SupplierID = trim($_GET["Supplier"]);
$SupplierName = trim($_GET["SupplierName"]);
$AgentID = trim($_GET["AgentID"]);
$AgentName = trim($_GET["AgentName"]);
$BillerType = trim($_GET["BillerType"]);

// if(isset($_GET["StartDate"]) && !empty($_GET["StartDate"])) {
// $StartDate = trim($_GET["StartDate"]); 
// } else {
// $StartDate = "";
// }

$api = API_SUPPLIER_WISE_LEDGER_REPORT;

$bodyContent = array(
    'Plant' => $Plant,
    'Center' => $Center,
    'StartDate' => $StartDate,
    'EndDate' => $EndDate,
    'SupplierID' => $SupplierID,
    'BillerType' => $BillerType,
    'AgentID' => $AgentID
);


$resList = CallAPI($api, "GET", "", $bodyContent);
// echo ($CenterName);
// echo ($Center);
// exit;
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
$error = $resList->Error;

$FileName = "Supplier-Wise-Ledger-Report";

header("Content-type: application/vnd.ms-excel; charset=UTF-8");
header('Content-Disposition: attachment; filename=' . $FileName . '-' . date("d-m-Y") . '.xlsx');

if (!isset($error) || empty($error)) {

    // $Totals = $resList->Data->Totals;
    // $List = $resList->Data->PaymentCenterwiseReportData;

    function getFinancialYearDates($date)
    {
        $timestamp = strtotime($date);
        $year = date('Y', $timestamp);
        $month = date('m', $timestamp);

        if ($month >= 4) { // If April or later, current year is the start year
            $startYear = $year;
            $endYear = $year + 1;
        } else { // If before April, previous year is the start year
            $startYear = $year - 1;
            $endYear = $year;
        }

        $startDate = "01/04/$startYear"; // 1st April of the start year
        $endDate = "31/03/$endYear"; // 31st March of the next year

        return [
            'start' => $startYear,
            'end' => $endYear
        ];
    }

    // Example Usage
    $financialYear = getFinancialYearDates($StartDate);

    $Totals = $resList->Data->Totals;
    $List = $resList->Data->PaymentCenterwiseReportData;

    // Create PHPExcel object
    $spreadsheet = new PHPExcel();
    $sheet = $spreadsheet->getActiveSheet();

    // Set Font Size
    $spreadsheet->getDefaultStyle()->getFont()->setSize(10);

    // Header Row
    $headers = ['Date', 'Paid Through', 'Particulars', 'Debit', 'TDS Deduction', 'Credit'];

    // Add Header Row to Excel
    $colIndex = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($colIndex . '4', $header);
        $colIndex++;
    }

    // Column Widths
    foreach (range('A', 'F') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    // Align Specific Columns to Right
    $columnsToAlign = ['D', 'E', 'F'];
    foreach ($columnsToAlign as $col) {
        $sheet->getStyle($col)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    }

    $sheet->setCellValue("A1", "Devi Fisheries Limited (" . $financialYear["start"] . " - " . $financialYear["end"] . ")");
    $sheet->setCellValue("A2", "LEDGER FOR RAW MATERIAL SUPPLIER FOR THE PERIOD ENDED");
    if ($BillerType == 1) {
        $sheet->setCellValue("A3", "NAME :- " . $AgentName . ", " . $CenterName . ". ");
    } else {
        $sheet->setCellValue("A3", "NAME :- " . $SupplierName . ", " . $CenterName . ". ");
    }
    $sheet->mergeCells("A1:F1");
    $sheet->mergeCells("A2:F2");
    $sheet->mergeCells("A3:F3");
    $sheet->getStyle("A1:F4")->getFont()->setBold(true);
    $sheet->getStyle("A1:F3")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

    // Fill Data
    $row = 5;
    foreach ($List as $item) {
        if ($item->SNO != "") {
            $sheet->setCellValue("A$row", $item->Date);
            $sheet->setCellValue("B$row", $item->PaidThrough);
            $sheet->setCellValue("C$row", $item->Particulars);

            // Debit
            $debit = ($item->Debit == '') ? '' : cleanNumber($item->Debit);
            $sheet->setCellValue("D$row", $debit);
            if ($debit !== '') {
                $sheet->getStyle("D$row")->getNumberFormat()->setFormatCode(
                    PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
                );
            }

            // TDS Deduction
            $tds = ($item->TDSDeduction == '') ? '' : cleanNumber($item->TDSDeduction);
            $sheet->setCellValue("E$row", $tds);
            if ($tds !== '') {
                $sheet->getStyle("E$row")->getNumberFormat()->setFormatCode(
                    PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
                );
            }

            // Credit
            $credit = ($item->Credit == '') ? '' : cleanNumber($item->Credit);
            $sheet->setCellValue("F$row", $credit);
            if ($credit !== '') {
                $sheet->getStyle("F$row")->getNumberFormat()->setFormatCode(
                    PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
                );
            }

        } else {
            // Blank SNO row (Subtotal row)
            $sheet->setCellValue("A$row", "");
            $sheet->setCellValue("B$row", "");
            $sheet->setCellValue("C$row", $item->Particulars);

            // SubTotal in D column
            $subTotal = ($item->SubTotal == '') ? '' : cleanNumber($item->SubTotal);
            $sheet->setCellValue("D$row", $subTotal);
            if ($subTotal !== '') {
                $sheet->getStyle("D$row")->getNumberFormat()->setFormatCode(
                    PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
                );
            }

            $sheet->setCellValue("E$row", "");
            $sheet->setCellValue("F$row", "");
        }

        $row++;
    }


    // Footer Totals Row
    if ($Totals) {
        // === Total Row ===
        $sheet->setCellValue("C$row", "Total");
        $sheet->getStyle("C$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

        // Total Debit
        $totalDebit = $Totals->TotalDebitAmount == '' ? '' : cleanNumber($Totals->TotalDebitAmount);
        $sheet->setCellValue("D$row", $totalDebit);
        if ($totalDebit !== '') {
            $sheet->getStyle("D$row")->getNumberFormat()->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
            );
        }

        // Total TDS
        $totalTDS = $Totals->TotalTDSDeduction == '' ? '' : cleanNumber($Totals->TotalTDSDeduction);
        $sheet->setCellValue("E$row", $totalTDS);
        if ($totalTDS !== '') {
            $sheet->getStyle("E$row")->getNumberFormat()->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
            );
        }

        // Total OS
        $totalOS = $Totals->TotalOSAmount == '' ? '' : cleanNumber($Totals->TotalOSAmount);
        $sheet->setCellValue("F$row", $totalOS);
        if ($totalOS !== '') {
            $sheet->getStyle("F$row")->getNumberFormat()->setFormatCode(
                PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1
            );
        }

        // Bold styling for total row
        foreach (['C', 'D', 'E', 'F'] as $col) {
            $sheet->getStyle("{$col}{$row}")->getFont()->setBold(true);
        }

        // === Balance Amount Row ===
        $row++;
        $sheet->setCellValue("C$row", "Balance Amount");
        $sheet->getStyle("C$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
        $balance = $Totals->BalanceAmount == '' ? '' : cleanNumber($Totals->BalanceAmount);
        $sheet->setCellValue("D$row", $balance);
        $sheet->getStyle("C$row")->getFont()->setBold(true);
        $sheet->getStyle("D$row")->getFont()->setBold(true);

        // === Entry Description Rows ===
        $row++;
        $entryDescriptionText = str_ireplace('<br>', '-', $Totals->EntryDescription);
        $entryDescriptionList = array_filter(array_map('trim', explode('-', $entryDescriptionText)));

        foreach ($entryDescriptionList as $line) {
            $sheet->setCellValue("C$row", $line);
            $sheet->getStyle("C$row")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C$row")->getFont()->setBold(true);
            $row++;
        }
    }


    ob_clean();

    // Write to Output
    $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
    $writer->save('php://output');
} else {
    echo "Note :" . $error;
}
function cleanNumber($num)
{
    return floatval(str_replace([',', "'"], '', $num)); // Remove comma & apostrophe, convert to number
}