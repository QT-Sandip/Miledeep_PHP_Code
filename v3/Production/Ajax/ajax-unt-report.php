<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
require __DIR__ . '/../../../Libraries/PHPExcel/Classes/PHPExcel.php'; // Adjust path if needed

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData->IsPlantFilter;
$FreezingReportGrouping = $ClientConfigData->FreezingReportGrouping;

$api = API_UNT_REPORT;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $fromDate = trim($_GET["fromDate"]);
    $toDate = trim($_GET["toDate"]);
    $plantId = trim($_GET["plantId"]);
    $ProductType = trim($_POST["productType"]);
    $border = "border='1'";
} else {
    $fromDate = trim($_POST["fromDate"]);
    $toDate = trim($_POST["toDate"]);
    $plantId = trim($_POST["plantId"]);
    $ProductType = trim($_POST["productType"]);
    $border = "";
}

$bodyContent = array(
    "FromDate" => $fromDate,
    "ToDate" => $toDate,
    "PlantID" => $plantId,
    "ProductType"=> $ProductType,
    // "IsPlantFilter" => $IsPlantFilter,
    'FreezingReportGrouping' => $FreezingReportGrouping,
);

$resList = CallAPI($api, "GET", "", $bodyContent);
//echo '<pre>';print_r($resList);die;
// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// exit;
$Totals = $resList->Totals;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->ArrivalData;
        $resTotals = $resList->Totals;
        $PageNo = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
        $PageCount = ceil($resList->TotalCount / 10); // Adjust based on pagination logic

        $content = '
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width:50px;">S.No</th>
                    <th style="min-width: 100px;width: 100px;">Arrival Date</th>
                    <th style="min-width: 130px;width: 130px;">Track Code</th>
                    <th style="min-width: 100px;width: 100px;">Tub Number</th>
                    <th style="min-width: 130px;max-width: 130px;">Variety</th>
                    <th style="min-width: 100px;max-width: 100px;">TypeProduct</th>
                     <th style="min-width: 100px;max-width: 100px;">Grade</th>

                    <th style="min-width: 100px;max-width: 100px;">Source Count</th>
                    <th style="min-width: 100px;max-width: 100px;">In Quantity</th>

                  
                    <th style="min-width: 100px;max-width: 100px;">Out Count</th>
                    <th style="min-width: 100px;max-width: 100px;">Out Quantity</th>
                    <th style="min-width: 100px;max-width: 100px;">Yield(%)</th>
                    <th style="min-width: 120px;width: 120px;">Plant</th>
                   <!-- <th style="min-width: 100px;max-width: 100px;">Remarks</th>-->
                </tr>
            </thead>
            <tbody>';

            $TotalInQuantity = 0;
            $TotalOutQuantity = 0;

            foreach ($List as $itemBreak) { 

                // var_dump($List);
                // exit;
                $TotalInQuantity += $itemBreak->InQuantity; 
                 $TotalOutQuantity += $itemBreak->OutQuantity; 
            $content .= '<tr>
                        <td style="min-width:50px;">' .$itemBreak->SNo. '</td>
                        <td style="min-width: 100px;width: 100px;">' .$itemBreak->ArrivalDate. '</td>
                        <td style="min-width: 130px;width: 130px;">' .$itemBreak->TrackCode. '</td>
                        <td style="min-width: 100px;width: 100px;">' .$itemBreak->TubNo. '</td>
                        <td style="min-width: 130px;max-width: 130px;">
                            '. $itemBreak->Species . '-' . $itemBreak->Variety. '
                        </td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->TypeProduct. '</td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->Grade. '</td>
                        
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->SourceCount. '</td>
                        <td class="tdInQuantity" style="min-width: 100px;max-width: 100px;">' .$itemBreak->InQuantity. '</td>
                     
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->OutCount. '</td>
                        <td class="tdOutQuantity" style="min-width: 100px;max-width: 100px;">' .$itemBreak->OutQuantity. '</td>
                        <td class="tdYield" style="min-width: 100px;max-width: 100px;">' .$itemBreak->SoakingYield. '</td>
                         <td style="min-width: 120px;width: 120px;">' .$itemBreak->Plant. '</td>
                       
                        <!--<td style="min-width: 120px;max-width: 100px;">' .$itemBreak->Remarks. '</td>-->

                    </tr>';
        }

        $content .= '</tbody>
         <tfoot>
                <tr style = "font-weight: bold;">
                    <td style="min-width: 80px;width: 80px;">Totals</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td id="TotalInQuantity">' .$Totals->GrandTotalInQuantity. '</td>
                    <td></td>
                    <td id="TotalOutQuantity">' .$Totals->GrandTotalOutQuantity. '</td>
                    <td id="TotalYield">'.$Totals->AverageSoakingYield.'</td>
                    <td></td>
                </tr>
            </tfoot>
        
        </table>';

        $content .= '
        <div class="mt-3 mb-3">Showing 1 to ' . count($List) . ' out of ' . count($List) . ' entries</div>
        <input type="hidden" id="hdnTotalRecords" value="' . $resList->TotalCount . '" />
        <div>
            <ul class="pagination pagination-rounded justify-content-center">';
        
        for ($i = 1; $i <= $PageCount; $i++) {
            $pageActive = ($PageNo == $i) ? "active" : "";
            $content .= '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
        }

        $content .= '</ul>
        </div>';

    } else {
         $content .=    "<div class='text-center mt-3 mb-3 text-bold'>Note: " . $error . "</div>";
    }
}

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $timestamp = date("YmdHis");
    $List = $resList->SoakingData;
    // Create PHPExcel object
    $spreadsheet = new PHPExcel();
    $sheet = $spreadsheet->getActiveSheet();

    // Set Font Size
    $spreadsheet->getDefaultStyle()->getFont()->setSize(9);

    // Set table heading bold text
    $sheet->getStyle('1')->getFont()->setBold(true);

    // Header Row
    $headers = [
            'S.No',
            'Arrival Date',
            'Track Code',
            'Tub Number',
            'Variety',
            'TypeProduct',
            'Grade',
            'Source Count',
            'In Quantity',
            'Out Count',
            'Out Quantity',
            'Yield(%)',
            'Plant',
           /*  'Remarks', */
    ];

    $colIndex = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($colIndex . '1', $header);
        $colIndex++;
    }

    // Column Widths
    foreach (range('A', 'R') as $columnID) {
        if ($columnID === 'B') {
            $sheet->getColumnDimension($columnID)->setWidth(15);
        } else if ($columnID === 'E') {
            $sheet->getColumnDimension($columnID)->setWidth(20);
        } else if ($columnID === 'F') {
            $sheet->getColumnDimension($columnID)->setWidth(10);
        } else if ($columnID === 'I') {
            $sheet->getColumnDimension($columnID)->setWidth(12);
        } else if ($columnID === 'J') {
            $sheet->getColumnDimension($columnID)->setWidth(15);
        } else if ($columnID === 'K') {
            $sheet->getColumnDimension($columnID)->setWidth(30);
        } else if ($columnID === 'M' || $columnID === 'N' ) {
            $sheet->getColumnDimension($columnID)->setWidth(12);
        } else if ($columnID === 'O' || $columnID === 'P' ) {
            $sheet->getColumnDimension($columnID)->setWidth(15);
        } else if ($columnID === 'Q' || $columnID === 'R' ) {
            $sheet->getColumnDimension($columnID)->setWidth(12);
        } else {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }
    }

    // Align Specific Columns to Right
    $columnsToAlign = ['M', 'N', 'O', 'P', 'Q', 'R'];
    foreach ($columnsToAlign as $col) {
        $sheet->getStyle($col)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
    }
    // $sheet->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    // $sheet->getStyle('E')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    // $sheet->getStyle('J')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    
    // Fill Data
    $row = 2;
    foreach ($List as $item) {
        $sheet->setCellValue("A$row", $item->SNo);
        $sheet->setCellValue("B$row", $item->ArrivalDate);
        $sheet->setCellValue("C$row", $item->TrackCode);
        $sheet->setCellValue("D$row", $item->TubNo);
        $sheet->setCellValue("E$row", $item->Species . '-' . $item->Variety);
        $sheet->setCellValue("F$row", $item->TypeProduct);
        $sheet->setCellValue("G$row", $item->Grade);
        $sheet->setCellValue("H$row", $item->SourceCount);
        $sheet->setCellValue("I$row", $item->InQuantity);
        $sheet->setCellValue("J$row", $item->OutCount);
        $sheet->setCellValue("K$row", $item->OutQuantity);
        $sheet->setCellValue("L$row", $item->SoakingYield);
        $sheet->setCellValue("M$row", $item->Plant);
        $row++;
    }
 /*    if ($Totals) {
        $sheet->setCellValue("A$row", "Total");
        $sheet->setCellValue("O$row", $Totals->TotalSoakInQty);
        $sheet->setCellValue("P$row", $Totals->TotalSoakOutQty);

     
        // // Set footer cells to bold
        $sheet->getStyle("A$row")->getFont()->setBold(true);
        $sheet->getStyle("O$row")->getFont()->setBold(true);
        $sheet->getStyle("P$row")->getFont()->setBold(true);
    } */

    // // Ensure UTF-8 encoding
    // $spreadsheet->getActiveSheet()->setCellValueExplicit('A1', 'Report', PHPExcel_Cell_DataType::TYPE_STRING);

    // Clear all echo'd content before downloading
    ob_clean();


    // Write to Output
    $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
    $filePath = $_SERVER['DOCUMENT_ROOT'] . "/UNT_Report_" . $timestamp . ".xlsx";
    $writer->save($filePath);
    $data['filename'] = $host. "download-file.php?file="."UNT_Report_" . $timestamp . ".xlsx";
    $data['status'] = 'Done';
    echo json_encode($data);
}else{
    echo $content;
}

?>
