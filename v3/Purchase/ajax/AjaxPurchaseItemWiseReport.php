<?php
if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    include("AjaxPurchaseitemWisecsvdownload.php");
    exit;
}
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
require __DIR__ . '/../../../Libraries/PHPExcel/Classes/PHPExcel.php'; // Adjust path if needed

$api = API_PURCHASE_REPORT_ITEM_WISE;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $plant = trim($_GET["Plant"]);
    $center = trim($_GET["Center"]);
    $supplier = trim($_GET["Supplier"]);
    $startDate = trim($_GET["StartDate"]);
    $endDate = trim($_GET["EndDate"]);

    $border = "border='1'";
} else {
    $plant = trim($_POST["Plant"]);
    $center = trim($_POST["Center"]);
    $supplier = trim($_POST["Supplier"]);
    $startDate = trim($_POST["StartDate"]);
    $endDate = trim($_POST["EndDate"]);
    $border = "";
}

$bodyContent = array(
    'Plant' => $plant,
    'CenterID' => $center,
    'SupplierID' => $supplier,
    'StartDate' => $startDate,
    'EndDate' => $endDate
);
// echo json_encode($bodyContent,JSON_PRETTY_PRINT);
// exit;
$resList = CallAPI($api, "GET", "", $bodyContent);

$Totals = $resList->Data->Totals;

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->Data->PurchaseReportData;

        $content = '
            <table id="tblDataTable" ' . $border . ' class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>S.No</th>
                        <th style="min-width: 120px;">Bill No </th>
                        <th style="min-width: 120px;">Arrival Plant</th>
                        <th style="min-width: 100px;">Bill Date </th>
                        <th style="min-width: 100px;">Arrival Date </th>
                        <th style="min-width: 160px;">Supplier </th>
                        <th style="min-width: 120px;">Center Name </th>
                        <th style="min-width: 130px;">Production Plant </th>
                        <th style="min-width: 100px;">Variety </th>
                        <th style="min-width: 160px;">Count </th>
                        <th style="min-width: 160px;">Vehicle Number </th>
                        <th style="min-width: 160px;">RM Production Plant </th>
                        <th style="min-width: 150px;">Final Quantity </th>
                        <th style="min-width: 100px;">Rate </th>
                        <th style="min-width: 100px;">Amount </th>
                        <th style="min-width: 120px;">Harvest </th>
                        <th style="min-width: 120px;">Harvest Amount </th>
                        <th style="min-width: 100px;">Final Amount</th>
                    </tr>
                </thead>
                <tbody>';
        foreach ($List as $item) {
            $content .= '
                        <tr>
                            <td>' . $item->SNO . '</td>
                            <td>' . $item->BillNo . '</td>
                            <td>' . $item->ArrivalPlant . '</td>
                            <td>' . $item->Date . '</td> 
                            <td>' . $item->ArrivalDate . '</td> 
                            <td>' . $item->Supplier . '</td>
                            <td>' . $item->Center . '</td>
                            <td>' . $item->ProductionPlant . '</td>            
                            <td>' . $item->Variety . '</td>   
                            <td>' . $item->Count . '</td>
                            <td>' . $item->VehicalNumber . '</td>
                            <td>' . $item->ProductionPlant . '</td>
                            <td>' . $item->Qty . '</td>
                            <td>' . $item->Rate . '</td>
                            <td>' . $item->Amount . '</td>
                            <td>' . $item->Harvest . '</td>
                            <td>' . $item->HarvestAmount . '</td>
                            <td>' . $item->FinalAmount . '</td>
                        </tr>';
        }
        $content .= '
                </tbody>
                <tfoot>
                    <tr> 
                        <th  align="center" >Total</th>              
                        <th  align="center" ></th>               
                        <th  align="center" ></th>               
                        <th  align="center" ></th>               
                        <th  align="center" ></th>               
                        <th  align="center" ></th>               
                        <th  align="center" ></th>                                               
                        <th  align="center" ></th>                                               
                        <th  align="center" > </th>
                        <th  align="center" > </th>
                        <th  align="center" > </th>
                        <th  align="center" > </th> 
                        <th  align="center" > ' . $Totals->TotalQuantity . '</th>
                        <th  align="center" > </th>
                        <th  align="center" > ' . $Totals->TotalValue . '</th> 
                        <th  align="center" > </th>                          
                        <th  align="center" >  ' . $Totals->TotalHarvestAmount . '</th>
                        <th  align="center" >  ' . $Totals->TotalAmountt . '</th>
                    </tr>
                </tfoot>
            </table>';
    } else {
        echo "Note : " . $error;
    }
}

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

    $timestamp = date("YmdHis");
    $List = $resList->Data->PurchaseReportData;
    // Create PHPExcel object
    PHPExcel_Settings::setCacheStorageMethod(
        PHPExcel_CachedObjectStorageFactory::cache_to_discISAM
    );
    $spreadsheet = new PHPExcel();
    $sheet = $spreadsheet->getActiveSheet();

    // Set Font Size
    $spreadsheet->getDefaultStyle()->getFont()->setSize(10);

    // Set table heading bold text
    $sheet->getStyle('1')->getFont()->setBold(true);

    // Header Row
    $headers = [
        'S.No',
        'Bill No',
        'Arrival Plant',
        'Bill Date',
        'Arrival Date',
        'Supplier',
        'Center Name',
        'Production Plant',
        'Variety',
        'Count',
        'Vehicle Number',
        'RM Production Plant',
        'Final Quantity',
        'Rate',
        'Amount',
        'Harvest',
        'Harvest Amount',
        'Final Amount'
    ];

    $colIndex = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($colIndex . '1', $header);
        $colIndex++;
    }

    // Column Widths
    foreach (range('A', 'R') as $columnID) {
        if ($columnID === 'F') {
            $sheet->getColumnDimension($columnID)->setWidth(40);
        } else if ($columnID === 'K') {
            $sheet->getColumnDimension($columnID)->setWidth(15);
        } else if ($columnID === 'I') {
            $sheet->getColumnDimension($columnID)->setWidth(10);
        } else {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }

    // Align Specific Columns to Right
    $columnsToAlign = ['J', 'M', 'N', 'O', 'P', 'Q', 'R'];
    foreach ($columnsToAlign as $col) {
        $sheet->getStyle($col)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    }

    // Fill Data
    $row = 2;
    // Fill Data
    $row = 2;
    foreach ($List as $item) {
        $sheet->setCellValue("A$row", $item->SNO);
        $sheet->setCellValue("B$row", $item->BillNo);
        $sheet->setCellValue("C$row", $item->ArrivalPlant);
        $sheet->setCellValue("D$row", \PHPExcel_Shared_Date::PHPToExcel(strtotime($item->Date)));
        $sheet->getStyle("D$row")->getNumberFormat()->setFormatCode('m/d/yyyy');
        $sheet->setCellValue("E$row", \PHPExcel_Shared_Date::PHPToExcel(strtotime($item->ArrivalDate)));
        $sheet->getStyle("E$row")->getNumberFormat()->setFormatCode('m/d/yyyy');
        $sheet->setCellValue("F$row", $item->Supplier);
        $sheet->setCellValue("G$row", $item->Center);
        $sheet->setCellValue("H$row", $item->ProductionPlant);
        $sheet->setCellValue("I$row", $item->Variety);
        $sheet->setCellValue("J$row", $item->Count);
        $sheet->setCellValue("K$row", $item->VehicalNumber);
        $sheet->setCellValue("L$row", $item->ProductionPlant);
        $sheet->setCellValue("M$row", cleanNumber($item->Qty));
        $sheet->getStyle("M$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->setCellValue("N$row", $item->Rate);
        $sheet->setCellValue("O$row", cleanNumber($item->Amount));
        $sheet->getStyle("O$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->setCellValue("P$row", $item->Harvest);
        $sheet->setCellValue("Q$row", cleanNumber($item->HarvestAmount));
        $sheet->getStyle("Q$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->setCellValue("R$row", cleanNumber($item->FinalAmount));
        $sheet->getStyle("R$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $row++;
    }

    // Footer Totals Row
    if ($Totals) {
        $sheet->setCellValue("A$row", "Total");
        $sheet->setCellValue("M$row", cleanNumber($Totals->TotalQuantity));
        $sheet->getStyle("M$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->setCellValue("O$row", cleanNumber($Totals->TotalValue));
        $sheet->getStyle("O$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->setCellValue("Q$row", cleanNumber($Totals->TotalHarvestAmount));
        $sheet->getStyle("Q$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
        $sheet->setCellValue("R$row", cleanNumber($Totals->TotalAmountt));
        $sheet->getStyle("R$row")->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

        // Set footer cells to bold
        $sheet->getStyle("A$row")->getFont()->setBold(true);
        $sheet->getStyle("M$row")->getFont()->setBold(true);
        $sheet->getStyle("O$row")->getFont()->setBold(true);
        $sheet->getStyle("Q$row")->getFont()->setBold(true);
        $sheet->getStyle("R$row")->getFont()->setBold(true);
    }


    try {
        $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
        $filePath = $_SERVER['DOCUMENT_ROOT'] . "/Purchase_Report_" . $timestamp . ".xlsx";
        $writer->save($filePath);

        $data['filename'] = $host . "download-file.php?file=Purchase_Report_" . $timestamp . ".xlsx";
        $data['status'] = 'Done';

        echo json_encode($data);
        exit;
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'Error',
            'message' => $e->getMessage()
        ]);
        exit;
    }

    // Write to Output
    // $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
    // $filePath = $_SERVER['DOCUMENT_ROOT'] . "/Purchase_Report_" . $timestamp . ".xlsx";
    // $writer->save($filePath);


    // exit;
    // $filename = 'Purchase_Report.xls';
    // header('Content-Type: application/vnd.ms-excel');
    // header("Content-Disposition: attachment; filename=" . $filename . "");
} else {
    echo $content;
}

function cleanNumber($num)
{
    return floatval(str_replace([',', "'"], '', $num)); // Remove comma & apostrophe, convert to number
}
