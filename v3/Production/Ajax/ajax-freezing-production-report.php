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

$api = API_FREEZING_PRODUCTION_REPORT;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $fromDate = trim($_GET["fromDate"]);
    $toDate = trim($_GET["toDate"]);
    $plantId = trim($_GET["plantId"]);
    $lineNumber = trim($_GET["lineNumber"]);
    $freezingType = trim($_GET["freezingType"]);
    $productType = trim($_GET["productType"]);
    $trackCodeType = trim($_POST["trackCodeType"]);
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
    $trackCodeType = trim($_POST["trackCodeType"]);
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
    "IsPlantFilter" => $IsPlantFilter,
    'FreezingReportGrouping' => $FreezingReportGrouping,
    'trackCodeType' =>$trackCodeType
);

$resList = CallAPI($api, "GET", "", $bodyContent);
//echo '<pre>';print_r($resList);die;
$Totals = $resList->Totals;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->FreezingData;
        $resTotals = $resList->Totals;
        $PageNo = isset($_GET["page"]) ? (int) $_GET["page"] : 1;
        $PageCount = ceil($resList->TotalCount / 10); // Adjust based on pagination logic

        $content = '
        <table id="tblDataTable" data-paging="false" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width:50px;">S.No</th>
                    <th style="min-width: 90px;">Production Date</th>
                    <th>Arrival Date</th>
                    <th>Track Code</th>
                    <th style="max-width: 40px;">SUPC</th>
                    <th style="min-width:300px">Product</th>
                    <th style="min-width: 180px">Po Number</th>
                    <th  style="min-width: 50px">Grade</th>
                    <th>Packing Style</th>
                    <th  style="min-width: 50px">Line Number</th>
                    <th  style="min-width: 20px">Tub Number</th>
                    <th  style="min-width: 40px">No of Cases</th>
                    <th style="min-width: 20px">No of Slabs</th>
                    <th  style="min-width: 20px">Production Qty</th>
                    <th  style="min-width: 20px">Rejection Qty</th>
                    <th  style="min-width: 20px">Freezing Yield(%)</th>
                     ' .
            (($IsPlantFilter == 0) ?
                '<th>Batch Number</th>' : ''
            ) . '
             <th  style="min-width: 150px">Production Plant</th>
              <th>Group</th>
               <th style="min-width: 50px">Shift</th>
                <th>Remarks</th>
                </tr>
            </thead>
            <tbody>';
        $TotalNoofCases = 0;
        $TotalNoofSlabs = 0;
        $TotalProductionWeight = 0;
        $TotalRejectionWeight = 0;
        foreach ($List as $item) {
            $TotalNoofCases += $item->NoofCases;
            $TotalNoofSlabs += $item->NoofSlabs;
            $TotalProductionWeight += floatval(str_replace(',', '', $item->ProductionWeight));
            $TotalRejectionWeight += $item->RejectionWeight;

            $content .= '<tr>
                <td style="min-width:50px;">' . $item->SNo . '</td>
                <td style="min-width: 90px;width: 90px;">' . $item->ProductionDate . '</td>
                <td style="min-width: 100px;width: 100px;">' . $item->ArrivalDate . '</td>
                <td style="min-width: 150px;width: 150px;">' . $item->TrackCode . '</td>
                <td style="min-width: 40px;max-width: 40px;">' . $item->Supc . '</td>
                <td style="min-width: 300px;max-width: 100px;">' . $item->Product . '</td>
                <td style="min-width: 180px;max-width: 180px;">' . $item->PoNo . '</td>
                <td style="min-width: 50px;max-width: 100px;">' . '' . $item->Grade . '</td>
                <td style="min-width: 100px;max-width: 100px;">' . $item->PackingStyle . '</td>
                <td style="min-width: 50px;max-width: 100px;">' . $item->LineNumber . '</td>
                <td style="min-width: 20px;max-width: 100px;">' . $item->TubNo . '</td>
                <td class="tdNoofCases" style="min-width: 40px;max-width: 100px;">' . $item->NoofCases . '</td>
                <td class="tdNoofSlabs" style="min-width: 20px;max-width: 100px;">' . $item->NoofSlabs . '</td>
                <td class="tdProductionWeight" style="min-width: 20px;max-width: 100px;">' . $item->ProductionWeight . '</td>
                <td class="tdRejectionWeight" style="min-width: 20px;max-width: 100px;">' . $item->RejectionWeight . '</td>
                <td style="min-width: 20px;max-width: 100px;">' . $item->FreezingYield . '</td>
                ' .
                (($IsPlantFilter == 0) ?
                    '<th>' . $item->LotNumber . '</th>' : ''
                )
                . '
                <td style="min-width: 150px;max-width: 100px;">' . $item->ProductionPlant . '</td>
                 <td style="min-width: 100px;max-width: 100px;">' . $item->Group . '</td>
                  <td style="min-width: 50px;max-width: 100px;">' . $item->Shift . '</td>
                <td style="min-width: 100px;max-width: 100px;">' . $item->Remarks . '</td>
            </tr>';
        }

        $content .= '</tbody>
        <tfoot>
            <tr style="font-weight: bold;">
                <td></td>
                <td style="min-width: 90px;">Totals</td>
                <td></td>
                <td></td>
                <td style="min-width:40px;max-width: 40px;"></td>
                <td  style="min-width:300px;"></td>
                <td></td>
                <td style="min-width: 50px"></td>
                <td></td>
                <td  style="min-width: 50px"></td>
                <td  style="min-width: 20px"></td>
                <td  style="min-width: 40px" id="TotalNoofCases">' . $TotalNoofCases . '</td>
                <td style="min-width: 20px" id="TotalNoofSlabs">' . $TotalNoofSlabs . '</td>
                <td  style="min-width: 20px" id="TotalProductionWeight">' . $TotalProductionWeight . '</td>
                <td style="min-width: 20px" id="TotalRejectionWeight">' . $TotalRejectionWeight . '</td>
                <td  style="min-width: 20px"></td> ';
        $content .= (($IsPlantFilter == 0) ?
            '<td></td>' : ''
        );
        $content .= '
         <td style="min-width: 150px"></td>
          <td></td>
         <td style="min-width: 180px"></td>
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
        $content .= "<div class='text-center mt-3 mb-3 text-bold'>Note: " . $error . "</div>";
    }
}

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

    $List = $resList->FreezingData;
    $timestamp = date("YmdHis");
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
        'Production Date',
        'Arrival Date',
        'Track Code',
        'SUPC',
        'Product',
        'Production Plant',
        'Group',
        'Shift',
        'Po Number',
        'Grade',
        'Packing Style',
        'Line Number',
        'Tub Number',
        'No of Cases',
        'No of Slabs',
        'Production Qty',
        'Rejection Qty',
        'Freezing Yield(%)',
        'Remarks'
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
        } else if ($columnID === 'M' || $columnID === 'N' || $columnID === 'O' || $columnID === 'P') {
            $sheet->getColumnDimension($columnID)->setWidth(12);
        } else if ($columnID === 'Q' || $columnID === 'R') {
            $sheet->getColumnDimension($columnID)->setWidth(12);
        } else if ($columnID === 'L') {
            $sheet->getColumnDimension($columnID)->setWidth(20);
            $sheet->getStyle($columnID)->getAlignment()->setWrapText(true);
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
        // $sheet->setCellValue("L$row", utf8_encode($item->PackingStyle));
        $sheet->setCellValue("M$row", $item->LineNumber);
        $sheet->setCellValue("N$row", $item->TubNo);
        $sheet->setCellValue("O$row", $item->NoofCases);
        $sheet->setCellValue("P$row", $item->NoofSlabs);
        $sheet->setCellValue("Q$row", $item->ProductionWeight);
        $sheet->setCellValue("R$row", $item->RejectionWeight);
        $sheet->setCellValue("S$row", $item->FreezingYield);
        $sheet->setCellValue("T$row", $item->Remarks);
        $row++;
    }
    if ($Totals) {
        error_log(print_r($Totals->TotalMcs, true));
        $sheet->setCellValue("A$row", "Total");
        $sheet->setCellValue("O$row", $Totals->TotalMcs);
        $sheet->setCellValue("P$row", $Totals->TotalSlabs);
        $sheet->setCellValue("Q$row", $Totals->TotalProductionWeight);
        $sheet->setCellValue("R$row", $Totals->TotalRejectionWeight);

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
    $data['filename'] = $host . "download-file.php?file=" . "Freezing_Report_" . $timestamp . ".xlsx";
    $data['status'] = 'Done';
    echo json_encode($data);
} else {
    echo $content;
}
