<?php


include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
require __DIR__ . '/../../../Libraries/PHPExcel/Classes/PHPExcel.php'; // Adjust path if needed

$ClientConfigData = $_SESSION['ClientConfigData'];
// $IsPlantFilter = $ClientConfigData->IsPlantFilter;
$IsPlantFilter = $ClientConfigData['IsPlantFilter'];

$api = API_TREATMENT_REGISTER_REPORT;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $fromDate = trim($_GET["fromDate"]);
    $toDate = trim($_GET["toDate"]);
    $plantId = trim($_GET["plantId"]);
    $lineNumber = trim($_GET["lineNumber"]);
    $border = "border='1'";
} else {
    $fromDate = trim($_POST["fromDate"]);
    $toDate = trim($_POST["toDate"]);
    $plantId = trim($_POST["plantId"]);
    $lineNumber = trim($_POST["lineNumber"]);
    $border = "";
}

$bodyContent = array(
    "FromDate" => $fromDate,
    "ToDate" => $toDate,
    "PlantID" => $plantId,
    "LineNumber" => $lineNumber,
    "IsPlantFilter" => $IsPlantFilter
);

$resList = CallAPI($api, "GET", "", $bodyContent);

$Totals = $resList->Totals;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->SoakingData;
        $resTotals = $resList->Totals;
        $PageNo = isset($_GET["page"]) ? (int)$_GET["page"] : 1;
        $PageCount = ceil($resList->TotalCount / 10); // Adjust based on pagination logic

        $content = '
       <!-- <div id="nonTableData" style="font-weight: bold; margin-bottom: 10px;">
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width:50px;">S.No</th>
                    <th style="min-width: 120px;max-width: 120px;">Soaking Date</th>
                    </tr>
                     </thead>
            <tbody>
                    <tr>
                    <th style="min-width:50px;">S.No</th>
                    <th style="min-width: 120px;max-width: 120px;">Soaking Date</th>
                    </tr>
                                </tbody>
                                </table>

        </div>-->

    <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
            
        <tr>
            <th style="min-width:100px;"colspan="2">Approval No : 1866</th>
            </tr>
              <tr>
            <th style="min-width:100px; max-width:120px;">FMT/SOP/27</th>
        </tr>
                <tr>
                    <th style="min-width:50px;">S.No</th>
                    <th style="min-width: 120px;max-width: 120px;">Soaking Date</th>
                    <th style="min-width: 100px;width: 100px;">Batch No Code</th>
                    <th style="min-width: 100px;width: 100px;">Plant</th>
                    <th style="min-width: 100px;max-width: 100px;">Variety</th>
                    <th style="min-width: 100px;max-width: 100px;">Grade</th>
                    <th style="min-width: 100px;max-width: 100px;">Soak In Qty(Kgs)</th>
                    <th style="min-width: 100px;max-width: 100px;">Ice % Qty</th>
                     <th style="min-width: 100px;max-width: 100px;">Water % Qty</th>
                     <th style="min-width: 100px;max-width: 100px;">Phosphate /Non Phosphate Qty(Kgs)</th>
                    <th style="min-width: 100px;max-width: 100px;">Salt(KGs)</th>
                    <th style="min-width: 100px;max-width: 100px;">Start Time</th>
                    <th style="min-width: 100px;max-width: 100px;">Start Count</th>
                     <th style="min-width: 100px;max-width: 100px;">End Time</th>
                     <th style="min-width: 100px;max-width: 100px;">End Count</th>
                     <th style="min-width: 100px;max-width: 100px;">In Temperature(C)</th>
                    <th style="min-width: 100px;max-width: 100px;">Out Temperature(C)</th>
                    <th style="min-width: 100px;max-width: 100px;">Chemical</th>
                   <th style="min-width: 100px;max-width: 100px;">Pick Up</th>
                  <th style="min-width: 100px;max-width: 100px;">Soaking Time</th>
                   <th style="min-width: 100px;max-width: 100px;">Liner</th>
                    <th style="min-width: 100px;max-width: 100px;">Shift</th>
                   <th style="min-width: 100px;max-width: 100px;">Remarks</th>

                </tr>
            </thead>
            <tbody>';

        foreach ($List as $itemBreak) { 
            $content .= '<tr>
                        <td style="min-width:50px;">' .$itemBreak->SNo. '</td>
                        <td style="min-width: 120px;max-width: 120px;">'. $itemBreak->SoakingDate. '</td>                      
                        <td style="min-width: 100px;width: 100px;">' .$itemBreak->LotNo. '</td>
                        <td style="min-width: 100px;width: 100px;">' .$itemBreak->Plant. '</td>
                        <td style="min-width: 100px;max-width: 100px;"> '. $itemBreak->Species . '-' . $itemBreak->Variety. '  </td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->Grade. '</td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->InQuantity. '</td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->IceQtyPercentage.'</td>  
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->WaterQtyPercentage.'</td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->ChemicalQty .'</td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->Salt. '</td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->StartTime. '</td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->SourceCount. '</td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->EndTime. '</td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->OutCount. '</td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->SoakInTemperature.'</td>  
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->SoakOutTemperature.'</td> 
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->ChemicalName. '</td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->PickUp.'</td>  
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->TimePeriod. '</td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->LineNumber. '</td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->ShiftName. '</td>

                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->Remarks. '</td>


                    </tr>';
        }

        $content .= '</tbody>
            <!-- <tfoot> -->
            <tr style="font-weight: bold;">
                <td style="min-width:50px;"></td>
                <td style="min-width: 100px;width: 100px;">Grand Totals</td>
                <td style="min-width: 100px;width: 100px;"></td>
                <td style="min-width: 100px;width: 100px;"></td>
                <td style="min-width: 100px;width: 100px;"></td>

                <td style="min-width: 120px;max-width: 120px;"></td>
                <td style="min-width: 100px;max-width: 100px;">' .$resTotals->TotalSoakInQty .'</td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>

                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>

                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                 <td style="min-width: 100px;max-width: 100px;"></td>

            </tr>
            <!-- </tfoot> -->
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
        'S.No', 'Arrival Date', 'Track Code', 'Lot/Bill No', 'Plant', 'Line Number',
        'Soaking Date', 'Tub Number', 'Variety', 'Grade', 'Chemical',
        'Chemical Qty', 'Salt(KGs)', 'Source Count', 'In Quantity', 'Start Time',
        'Time Period', 'Out Count', 'Out Quantity', 'Soaking Yield(%)', 'Remarks'
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
        $sheet->setCellValue("D$row", $item->LotNo);
        $sheet->setCellValue("E$row", $item->Plant);
        $sheet->setCellValue("F$row", $item->LineNumber);
        $sheet->setCellValue("G$row", $item->SoakingDate);
        $sheet->setCellValue("H$row", $item->TubNumber);
        $sheet->setCellValue("I$row", $item->Species . '-' . $item->Variety);
        $sheet->setCellValue("J$row", $item->Grade);
        $sheet->setCellValue("K$row", $item->ChemicalName);
        $sheet->setCellValue("L$row", $item->ChemicalQty);
        $sheet->setCellValue("M$row", $item->Salt);
        $sheet->setCellValue("N$row", $item->SourceCount);
        $sheet->setCellValue("O$row", $item->InQuantity);
        $sheet->setCellValue("P$row", $item->StartTime);
        $sheet->setCellValue("Q$row", $item->TimePeriod);
        $sheet->setCellValue("R$row", $item->OutCount);
        $sheet->setCellValue("R$row", $item->OutQuantity);
        $sheet->setCellValue("S$row", $item->SoakingYield);
        $sheet->setCellValue("T$row", $item->Remarks);
        $row++;
    }
    if ($Totals) {
        $sheet->setCellValue("A$row", "Total");
        $sheet->setCellValue("O$row", $Totals->TotalSoakInQty);
        $sheet->setCellValue("P$row", $Totals->TotalSoakOutQty);

     
        // // Set footer cells to bold
        $sheet->getStyle("A$row")->getFont()->setBold(true);
        $sheet->getStyle("O$row")->getFont()->setBold(true);
        $sheet->getStyle("P$row")->getFont()->setBold(true);
    }

    // // Ensure UTF-8 encoding
    // $spreadsheet->getActiveSheet()->setCellValueExplicit('A1', 'Report', PHPExcel_Cell_DataType::TYPE_STRING);

    // Clear all echo'd content before downloading
    ob_clean();


    // Write to Output
    $writer = new PHPExcel_Writer_Excel2007($spreadsheet);
    $filePath = $_SERVER['DOCUMENT_ROOT'] . "/treatment-register-report.php" . $timestamp . ".xlsx";
    $writer->save($filePath);
    $data['filename'] = $host. "download-file.php?file="."Treatment_Register_Report" . $timestamp . ".xlsx";
    $data['status'] = 'Done';
    echo json_encode($data);
}else{
    echo $content;
}

?>
