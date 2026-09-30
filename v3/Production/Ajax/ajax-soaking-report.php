<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
require __DIR__ . '/../../../Libraries/PHPExcel/Classes/PHPExcel.php'; // Adjust path if needed

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$IsPlantFilter = $ClientConfigData->IsPlantFilter;
$methodType = trim($_POST["methodType"]);

$api = API_SOAKING_REPORTS;

if ($methodType == "yearChange") {
    $fyYear  = intval($_POST["FYYear"]);

    $fromDate = $fyYear . "-04-01";
    $toDate   = ($fyYear + 1) . "-03-31";

    $bodyContent = array(
        'FromDate' => $fromDate,
        'ToDate'   => $toDate,
        'DataType' => trim($_POST["DataType"]),
        'PlantID'  => trim($_POST["PlantID"])

    );
	// echo print_r($bodyContent);
	// exit;
	//error_log(print_r($bodyContent, true));

    $result = CallAPI($api, "GET", "", $bodyContent);
	// echo print_r($result);
	// exit;
    // error_log(print_r($bodyContent, true));
	header('Content-Type: application/json');
	$response = array('ArrivalsList' => $result, 'Error' => $error, 'Msg' => $msg);

	echo json_encode($response);
	exit;

	//echo print_r($response);
	// exit;

}
else if($_POST['DataType'] === 'getArrivalsByDate'){
    // echo 3433;
    // exit;
    $fromDate = $_POST['fromDate'];
    $toDate   = $_POST['toDate'];
    $plantId  = $_POST['PlantID'];
    $bodyContent = array(
        'FromDate' => $fromDate,
        'ToDate'   => $toDate,
        'DataType' => trim($_POST["DataType"]),
        'PlantID'  => trim($_POST["PlantID"]),
        'SoakingDate' => trim($_POST["soakingDate"])

    );
    // echo print_r( $bodyContent);
    // exit;

    $result = CallAPI($api, "GET", "", $bodyContent);
	header('Content-Type: application/json');
	$response = array('ArrivalsList' => $result, 'Error' => $error, 'Msg' => $msg);

	echo json_encode($response);
	exit;

}
else if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $fromDate = trim($_GET["fromDate"]);
    $toDate = trim($_GET["toDate"]);
    $plantId = trim($_GET["plantId"]);
    $lineNumber = trim($_GET["lineNumber"]);
    $SoakingDate = trim($_POST["soakingDate"]);
    $ProductType = trim($_POST["productType"]);
    $border = "border='1'";
} else if(!empty($_POST['methodType']) && $_POST['methodType'] == 'getLinersByPlant'){
    $resList = CallAPI($api, "GET", "", $_POST);
    echo json_encode($resList);die;
}else {
    $fromDate = trim($_POST["fromDate"]);
    $toDate = trim($_POST["toDate"]);
    $plantId = trim($_POST["plantId"]);
    $lineNumber = trim($_POST["lineNumber"]);
    $SoakingDate = trim($_POST["soakingDate"]);
    $ProductType = trim($_POST["productType"]);
     $ArrivalID = trim($_POST["arrivalID"]);
    $border = "";
}

$bodyContent = array(
    "FromDate" => $fromDate,
    "ToDate" => $toDate,
    "PlantID" => $plantId,
    "LineNumber" => $lineNumber,
    "IsPlantFilter" => $IsPlantFilter,
    "SoakingDate"=> $SoakingDate,
    "ProductType"=> $ProductType,
    "ArrivalID" => $ArrivalID
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
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width:40px;">S.No</th>
                    <th style="min-width: 90px;width: 90px;">Arrival Date</th>
                    <th style="min-width: 90px;max-width: 90px;">Soaking Date</th>
                    <th style="min-width: 130px;width: 130px;">Track Code</th>
                    <th style="min-width: 60px;max-width: 60px;">Tub Number</th>
                    <th style="min-width: 220px;max-width: 220px;">Variety</th>
                    <th style="min-width: 100px;max-width: 100px;">Type Of Product</th>
                    <th style="min-width: 80px;max-width: 80px;">Grade</th>
                    <th style="min-width: 100px;max-width: 100px;">Source Count</th>
                     <th style="min-width: 100px;max-width: 100px;">In Count</th>
                    <th style="min-width: 100px;max-width: 100px;">In Quantity</th>
                    <th style="min-width: 100px;max-width: 100px;">Out Count</th>
                    <th style="min-width: 100px;max-width: 100px;">Out Quantity</th>
                    <th style="min-width: 100px;max-width: 100px;">Soaking Yield(%)</th>
                    <th style="min-width: 100px;max-width: 100px;">Remarks</th>

                    <th style="min-width: 100px;width: 100px;">Lot/Bill No</th>
                    <th style="min-width: 130px;width: 130px;">Plant</th>

                    <th style="min-width: 80px;max-width: 80px;">Line Number</th>

                    <th style="min-width: 250px;max-width: 250px;">Chemical</th>
                    <th style="min-width: 100px;max-width: 100px;">Chemical Qty</th>
                    <th style="min-width: 100px;max-width: 100px;">Salt(KGs)</th>

                    <th style="min-width: 100px;max-width: 100px;">Start Time</th>
                    <th style="min-width: 100px;max-width: 100px;">Time Period</th>
                </tr>
            </thead>
            <tbody>';

            $TotalInQuantity = 0;
            $TotalOutQuantity = 0;
            $TotalSoakingYield=0;
            $SoakingYieldCount=0;
            foreach ($List as $itemBreak) { 
                $TotalInQuantity += $itemBreak->InQuantity; 
                 $TotalOutQuantity += $itemBreak->OutQuantity;
                 $TotalSoakingYield+=$itemBreak->SoakingYield;
                 if($itemBreak->SoakingYield>0){
                    $SoakingYieldCount++;
                 }
            $content .= '<tr>
                        <td style="min-width:40px;">' .$itemBreak->SNo. '</td>
                        <td style="min-width: 90px;width: 90px;">' .$itemBreak->ArrivalDate. '</td>
                        <td style="min-width: 90px;max-width: 90px;">'. $itemBreak->SoakingDate. '</td>
                        <td style="min-width: 130px;width: 130px;">' .$itemBreak->TrackCode. '</td>
                        <td style="min-width: 60px;max-width: 60px;">'. $itemBreak->TubNumber. '</td>
                        <td style="min-width: 220px;max-width: 220px;">
                            '. $itemBreak->Species . '-' . $itemBreak->Variety. '
                        </td>
                         <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->ProductType. '</td>
                        <td style="min-width: 80px;max-width: 80px;">' .$itemBreak->Grade. '</td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->SourceCount. '</td>
                         <td class="tdIncount" style="min-width: 100px;max-width: 100px;">' . $itemBreak->InCounts . '</td>
                        <td class="tdInQuantity" style="min-width: 100px;max-width: 100px;">' .$itemBreak->InQuantity. '</td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->OutCount. '</td>
                        <td class="tdOutQuantity" style="min-width: 100px;max-width: 100px;">' .$itemBreak->OutQuantity. '</td>
                        <td class="tdSoakingYield" style="min-width: 100px;max-width: 100px;">' .$itemBreak->SoakingYield. '</td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->Remarks. '</td>

                        <td style="min-width: 100px;width: 100px;">' .$itemBreak->LotNo. '</td>
                        <td style="min-width: 130px;width: 130px;">' .$itemBreak->Plant. '</td>

                        <td style="min-width: 80px;max-width: 80px;">'. $itemBreak->LineNumber .'</td>
                        <td style="min-width: 250px;max-width: 250px;">' .$itemBreak->ChemicalName. '</td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->ChemicalQty .'</td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->Salt. '</td>

                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->StartTime. '</td>
                        <td style="min-width: 100px;max-width: 100px;">' .$itemBreak->TimePeriod. '</td>

                    </tr>';
        }

        $content .= '</tbody>
            <tfoot>
            <tr  style="font-family: Arial, sans-serif; font-weight: 900; color: #000; text-align: center; vertical-align: middle;">
                <td style="min-width:40px;"></td>
                <td style="min-width: 90px;width: 90px;">Grand Totals</td>
                <td style="min-width: 90px;width: 90px;"></td>
                <td style="min-width: 130px;width: 130px;"></td>
                <td style="min-width: 60px;width: 60px;"></td>
                 <td style="min-width: 220px;width: 220px;"></td>

                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 80px;max-width: 80px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                 <td style="min-width: 100px;max-width: 100px;"></td>
               <td id="TotalInQuantity" style="min-width: 100px;max-width: 100px;">' . $TotalInQuantity . '</td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td id="TotalOutQuantity" style="min-width: 100px;max-width: 100px;">' . $TotalOutQuantity . '</td>
                <td id="AverageSoakingYield" style="min-width: 100px;max-width: 100px;">'.number_format(($SoakingYieldCount > 0 ? $TotalSoakingYield / $SoakingYieldCount : $TotalSoakingYield),3).'</td>

                

                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 80px;max-width: 80px;"></td>

                <td style="min-width: 250px;max-width: 250px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
                <td style="min-width: 100px;max-width: 100px;"></td>
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
    $filePath = $_SERVER['DOCUMENT_ROOT'] . "/Soaking_Report_" . $timestamp . ".xlsx";
    $writer->save($filePath);
    $data['filename'] = $host. "download-file.php?file="."Soaking_Report_" . $timestamp . ".xlsx";
    $data['status'] = 'Done';
    echo json_encode($data);
}else{
    echo $content;
}

?>
