<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
require __DIR__ . '/../../../Libraries/PHPExcel/Classes/PHPExcel.php'; // Adjust path if needed

$ClientConfigData = $_SESSION['ClientConfigData'];
//  $ClientID = $_SESSION['ClientID']; 

// $IsPlantFilter = $ClientConfigData->IsPlantFilter;
$IsPlantFilter = $ClientConfigData['IsPlantFilter'];

$api = API_FREEZING_REPORT;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $fromDate = trim($_GET["fromDate"]);
    $toDate = trim($_GET["toDate"]);
    $plantId = trim($_GET["plantId"]);
    $lineNumber = trim($_GET["lineNumber"]);
    $freezingType = trim($_GET["freezingType"]);
    $productType = trim($_GET["productType"]);
    $shifts = trim($_GET["shifts"]);

    $border = "border='1'";
} else {
    $fromDate = trim($_POST["fromDate"]);
    $toDate = trim($_POST["toDate"]);
    $plantId = trim($_POST["plantId"]);
    $lineNumber = trim($_POST["lineNumber"]);
    $freezingType = trim($_POST["freezingType"]);
    $productType = trim($_POST["productType"]);
    $shifts = trim($_POST["shifts"]);
    $border = "";
}

$bodyContent = array(
    "FromDate" => $fromDate,
    "ToDate" => $toDate,
    "PlantID" => $plantId,
    "LineNumber" => $lineNumber,
    "FreezingType" => trim($freezingType),
    "TypeProduct" => trim($productType),
    "Shift" => trim($shifts),
    "IsPlantFilter" => $IsPlantFilter
);

$resList = CallAPI($api, "GET", "", $bodyContent);



$Totals = $resList->Totals;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->FreezingData;
        $resTotals = $resList->Totals;
        $PageNo = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
        $PageCount = ceil($resList->TotalCount / 10); // Adjust based on pagination logic

        $content = '<table id="tblDataTable" data-paging="false" class="table table-hover table-bordered">
            <thead class="thead-light">
              <tr>
            <th style="min-width:100px;"colspan="3">Average Per Hour Cosumption '.$Totals->AveragePerHourConsumption.'</th>
            </tr>
             
                <tr>
                    <th style="min-width:50px;">S.No</th>
                    <th>Production Date</th>
                    <th>Arrival Date</th>
                    <th>Track Code</th>
                    <th>SUPC</th>
                    <th>Product</th>
                    <th>Production Plant</th>
                    <th>Group</th>
                    <th>Shift</th>
                    <th>Po Number</th>
                    <th>Grade</th>
                    <th>Packing Style</th>
                    <th>Line Number</th>
                    <th>Tub Number</th>
                    <th>No of Cases</th>
                    <th>No of Slabs</th>
                    <th>Production Qty</th>
                    <th>Rejection Qty</th>
                    <th>Freezing Yield(%)</th>
                    <th>Start Time</th>
                     <th>End Time</th>
                    <th>Working Hours</th>
                    <th>Remarks</th>
                     </tr>
            </thead>
            <tbody>';
           $TotalNoofCases=0;
           $TotalNoofSlabs=0;
           $TotalProductionWeight=0;
           $TotalRejectionWeight=0;
        foreach ($List as $item) {
            $TotalNoofCases += $item->NoofCases; 
            $TotalNoofSlabs += $item->NoofSlabs;
            $TotalProductionWeight += $item->ProductionWeight;
            $TotalRejectionWeight += $item->RejectionWeight;

            $content .= '<tr>
                <td style="min-width:50px;">' . $item->SNo . '</td>
                <td style="min-width: 100px;width: 100px;">' . $item->ProductionDate . '</td>
                <td style="min-width: 100px;width: 100px;">' . $item->ArrivalDate . '</td>
                <td style="min-width: 100px;width: 100px;">' . $item->TrackCode . '</td>
                <td style="min-width: 120px;max-width: 120px;">' . $item->Supc . '</td>
                <td style="min-width: 100px;max-width: 100px;">' . $item->Product . '</td>
                <td style="min-width: 100px;max-width: 100px;">' . $item->ProductionPlant . '</td>
                <td style="min-width: 100px;max-width: 100px;">' . $item->Group . '</td>
                <td style="min-width: 100px;max-width: 100px;">' . $item->Shift . '</td>
                <td style="min-width: 100px;max-width: 100px;">' . $item->PoNo . '</td>
                <td style="min-width: 100px;max-width: 100px;">' . $item->Grade . '</td>
                <td style="min-width: 100px;max-width: 100px;">' . $item->PackingStyle . '</td>
                <td style="min-width: 100px;max-width: 100px;">' . $item->LineNumber . '</td>
                <td style="min-width: 100px;max-width: 100px;">' . $item->TubNo . '</td>
                <td class="tdNoofCases" style="min-width: 100px;max-width: 100px;">' . $item->NoofCases . '</td>
                <td class="tdNoofSlabs" style="min-width: 100px;max-width: 100px;">' . $item->NoofSlabs . '</td>
                <td class="tdProductionWeight" style="min-width: 100px;max-width: 100px;">' . $item->ProductionWeight . '</td>
                <td class="tdRejectionWeight" style="min-width: 100px;max-width: 100px;">' . $item->RejectionWeight . '</td>
                <td style="min-width: 100px;max-width: 100px;">' . $item->FreezingYield . '</td>
                 <td style="min-width: 100px;max-width: 100px;">' . $item->StartTimer . '</td>
                <td style="min-width: 100px;max-width: 100px;">' . $item->EndTimer . '</td>
                <td style="min-width: 100px;max-width: 100px;">' . $item->WorkingHours . '</td>
                <td style="min-width: 100px;max-width: 100px;">' . $item->Remarks . '</td> 
                </tr>';
        }

        $content .= '</tbody>
            <tr style="font-weight: bold;">
                <td></td>
                <td>Totals</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <<td id="TotalNoofCases">' . $TotalNoofCases . '</td>
                <td id="TotalNoofSlabs">' . $TotalNoofSlabs . '</td>
                <td id="TotalProductionWeight">' . $TotalProductionWeight . '</td>
                <td id="TotalRejectionWeight">' . $TotalRejectionWeight . '</td>
                <td></td>
                <td></td>
                <td></td>
                 <td id="TotalWorkingHours">' . $Totals->TotalWorkingHours . '</td>
                <td></td>

                   </tr>
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
    $List = $resList->FreezingData;
    // Create PHPExcel object
    $spreadsheet = new PHPExcel();
    $sheet = $spreadsheet->getActiveSheet();

    // Set Font Size
    $spreadsheet->getDefaultStyle()->getFont()->setSize(9);

    // Set table heading bold text
    $sheet->getStyle('1')->getFont()->setBold(true);

    // Header Row
    $headers = [
        'S.No', 'Production Date', 'Arrival Date', 'Track Code', 'SUPC', 'Product',
        'Production Plant', 'Group', 'Shift', 'Po Number', 'Grade',
        'Packing Style', 'Line Number', 'Tub Number', 'No of Cases', 'No of Slabs',
        'Production Qty', 'Rejection Qty', 'Freezing Yield(%)', 'Remarks'
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
            $sheet->getColumnDimension($columnID)->setWidth(8);
        } else if ($columnID === 'F') {
            $sheet->getColumnDimension($columnID)->setWidth(25);
        } else if ($columnID === 'K') {
            $sheet->getColumnDimension($columnID)->setWidth(10);
        } else if ($columnID === 'I') {
            $sheet->getColumnDimension($columnID)->setWidth(12);
        } else if ($columnID === 'M' || $columnID === 'N' || $columnID === 'O' || $columnID === 'P' ) {
            $sheet->getColumnDimension($columnID)->setWidth(12);
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
    $sheet->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('E')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('J')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
    
    // Fill Data
    $row = 2;
    foreach ($List as $item) {
        $sheet->setCellValue("A$row", $item->SNo);
        $sheet->setCellValue("B$row", $item->ProductionDate);
        $sheet->setCellValue("C$row", $item->ArrivalDate);
        $sheet->setCellValue("D$row", $item->TrackCode);
        $sheet->setCellValue("E$row", $item->Supc);
        $sheet->setCellValue("F$row", $item->Product);
        $sheet->setCellValue("G$row", $item->ProductionPlant);
        $sheet->setCellValue("H$row", $item->Group);
        $sheet->setCellValue("I$row", $item->Shift);
        $sheet->setCellValue("J$row", $item->PoNo);
        $sheet->setCellValue("K$row", $item->Grade);
        $sheet->setCellValue("L$row", $item->PackingStyle);
        $sheet->setCellValue("M$row", $item->LineNumber);
        $sheet->setCellValue("N$row", $item->TubNo);
        $sheet->setCellValue("O$row", $item->NoofCases);
        $sheet->setCellValue("P$row", $item->NoofSlabs);
        $sheet->setCellValue("Q$row", $item->ProductionWeight);
        $sheet->setCellValue("R$row", $item->RejectionWeight);
        $sheet->setCellValue("S$row", $item->FreezingYield);
        $sheet->setCellValue("T$row", $item->StartTimer);
        $sheet->setCellValue("U$row", $item->EndTimer);
        $sheet->setCellValue("V$row", $item->WorkingHours);
        $sheet->setCellValue("W$row", $item->Remarks);
        $row++;
    }
    if ($Totals) {
        error_log(print_r($Totals->TotalMcs,true));
        $sheet->setCellValue("A$row", "Total");
        $sheet->setCellValue("O$row", $Totals->TotalMcs);
        $sheet->setCellValue("P$row", $Totals->TotalSlabs);
        $sheet->setCellValue("Q$row", $Totals->TotalProductionWeight);
        $sheet->setCellValue("R$row", $Totals->TotalRejectionWeight);
        $sheet->setCellValue("V$row", $Totals->TotalWorkingHours);

        
     
        // // Set footer cells to bold
        $sheet->getStyle("A$row")->getFont()->setBold(true);
        $sheet->getStyle("O$row")->getFont()->setBold(true);
        $sheet->getStyle("P$row")->getFont()->setBold(true);
        $sheet->getStyle("Q$row")->getFont()->setBold(true);
        $sheet->getStyle("R$row")->getFont()->setBold(true);
    }

    // // Ensure UTF-8 encoding
    // $spreadsheet->getActiveSheet()->setCellValueExplicit('A1', 'Report', PHPExcel_Cell_DataType::TYPE_STRING);

    // Clear all echo'd content before downloading
    ob_clean();


    // Write to Output
    $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
    $filePath = $_SERVER['DOCUMENT_ROOT'] . "/Freezing_Report_" . $timestamp . ".xlsx";
    $writer->save($filePath);
    $data['filename'] = $host. "download-file.php?file="."Freezing_Report_" . $timestamp . ".xlsx";
    $data['status'] = 'Done';
    echo json_encode($data);
}else{
    echo $content;
}

?>
