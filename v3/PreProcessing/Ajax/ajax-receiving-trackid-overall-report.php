<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientID = $_SESSION['ClientID'];
$DisplayBillNo = isset($ClientID) && $ClientID !== 'DFLV3' ? true : false;
$ColSpan = isset($ClientID) && $ClientID !== 'DFLV3' ? 10 : 9;

$ClientConfigData = (object) $_SESSION['ClientConfigData'];
$mergeHonLoose = $ClientConfigData->mergeHonLoose;

$api = API_RECEIVING_OVERALL_REPORT;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $StartDate = trim($_GET["fromDate"]);
    $EndDate = trim($_GET["toDate"]);
    $ReceivingPlantID = trim($_GET["receivingPlantID"]);
    $PlantName = trim($_GET["plantName"]);
    $border = "border=''";
    $firstRow = '<td style="text-align: center; font-weight: bold;">
            Rm Received Report_Date(' . date('d/m/Y', strtotime($StartDate)) . ' - ' . date('d/m/Y', strtotime($EndDate)) . ')_ReceivingPlant: ' . $PlantName . '
            </td>';
} else {
    $StartDate = trim($_POST["fromDate"]);
    $EndDate = trim($_POST["toDate"]);
    $ReceivingPlantID = trim($_POST["receivingPlantID"]);
    $border = "";
    $firstRow = '';
}

$bodyContent = array(
    "StartDate" => $StartDate,
    "EndDate" => $EndDate,
    "ReceivingPlantID" => $ReceivingPlantID,
    "MergeHonLoose" => $mergeHonLoose
);

// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// exit;
$resList = CallAPI($api, "GET", "", $bodyContent);

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->Data;
        $ArrivalQTY = $resList->ArrivalQTY;
        $ReceivingQTY = $resList->ReceivingQTY;

        $content = '';
        // $content .= ' <table id="tblDataTable" ' . $border . ' class="table table-hover table-bordered">';
        $content .= ' <table id="tblDataTable" ' . $border . ' class="table table-hover table-bordered" style="table-layout: fixed;">';

        $content .= $firstRow;

        $content .= '<thead class="thead-light">
                    <tr>
                    <th style="min-width:50px;" class="SNo">SNo</th>
                    <th style="min-width: 80px;width: 80px;" class="Arrival_Date">Arrival Date</th>
                    <th style="min-width: 130px;width: 130px;" class="Vehicle_No">Vehicle No</th>
                    <th style="min-width: 130px;width: 130px;" class="Track_Number">Track Number</th>
                    <th style="min-width: 100px;width: 100px;" class="Seal_No">Seal No</th>';
        if ($DisplayBillNo) {
            $content .= '<th style="min-width: 120px;max-width: 120px;" class="Bill_No">Bill No</th>';
        }
        $content .= '<th style="min-width: 240px;max-width: 240px;" class="Variety" >Variety</th>
                    <th style="min-width: 100px;max-width: 100px;" class="Count">Count</th>
                    <th style="min-width: 100px;max-width: 100px;" class="Weight">Weight</th>
                    <th style="min-width: 100px;max-width: 100px;" class="Sample_Qty">Sample Qty</th>
                    <th style="min-width: 100px;max-width: 100px;" class="No_of_Pcs">No.of Pcs</th>
                    <th style="min-width: 100px;max-width: 100px;" class="Received_Count">Received Count</th>
                    <th style="min-width: 100px;max-width: 100px;" class="Received_Weight">Received Weight</th>
                    <th style="min-width: 100px;max-width: 100px;" class="Count_Diff">Count Diff</th>
                    <th style="min-width: 90px;max-width: 90px;" class="QTY_Diff">QTY Diff</th>
                    <th style="min-width: 240px;width: 240px;" class="Remarks">Remarks</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($List as $item) {
            $content .= '<tr>
                        <td style="min-width:50px;" class="SNo">' . $item->SNo . '</td>
                        <td style="min-width: 80px; width: 80px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis;" class="Arrival_Date">' . $item->ArrivalDate . '</td>
                        <td style="min-width: 130px;width: 130px;" class="Vehicle_No">' . $item->VehicleNumber . '</td>
                        <td style="min-width: 130px;width: 130px;" overflow: hidden; white-space: nowrap; text-overflow: ellipsis;" class="Track_Number">' . $item->TrackCode . '</td>
                        <td style="min-width: 100px;width: 100px;" class="Seal_No">' . $item->SealNo . '</td>
                        
                        <td colspan="' . $ColSpan . '" style="padding: 0px !important;">
                            <table ' . $border . ' class="subTable table table-bordered mb-0" style="width: 100%">';

            $TotalArrivalQTY = 0;
            $TotalReceivingQTY = 0;
            foreach ($item->BreakUpData as $itemBreak) {
                $TotalArrivalQTY += $itemBreak->ArrivalWeight;
                $TotalReceivingQTY += $itemBreak->ReceivingQty;
                $content .= '<tr>';
                if ($DisplayBillNo) {
                    $content .= '<td style="min-width: 120px;max-width: 120px;" class="Bill_No">' . $itemBreak->ArrivalNumber . '(' . $itemBreak->LotCode . ')</td>';
                }
                $content .= '<td style="min-width: 240px;max-width: 240px;" class="Variety">' . $itemBreak->OverallVariety . '</td>
                                <td style="min-width: 100px;max-width: 100px;" class="Count">' . $itemBreak->ArrivalCount . '</td>
                                <td style="min-width: 100px;max-width: 100px;" class="tdWeight">' . $itemBreak->ArrivalWeight . '</td>
                                <td style="min-width: 100px;max-width: 100px;" class="Sample_Qty">' . $itemBreak->SampleQuantity . '</td>
                                <td style="min-width: 100px;max-width: 100px;" class="No_of_Pcs">' . $itemBreak->NoofPieces . '</td>
                                <td style="min-width: 100px;max-width: 100px;" class="Received_Count">' . $itemBreak->ReceivingCount . '</td>
                                <td style="min-width: 100px;max-width: 100px;" class="tdReceived_Weight">' . $itemBreak->ReceivingQty . '</td>
                                <td style="min-width: 100px;max-width: 100px;" class="Count_Diff">' . round($itemBreak->CountDifference, 2) . '</td>
                                <td style="min-width: 90px;max-width: 90px;" class="QTY_Diff">' . round($itemBreak->QuantityDifference, 2) . '</td>
                            </tr>';
            }

            $content .= '</table></td>';
            if ($DisplayBillNo) {
                $content .= '<td style="display: none"></td>';
            }
                        
            $content .= '<td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="min-width: 150px;width: 150px;" class="Remarks">' . $item->Remarks . '</td>
                        </tr>';
        }

        $content .= '</tbody>
            <tfoot>
                <tr style="font-weight: bold;">
                    <td style="min-width:50px;"></td>
                    <td style="min-width: 80px;width: 80px;">Grand Totals</td>
                    <td style="min-width: 130px;width: 130px;"></td>
                    <td style="min-width: 130px;max-width: 130px;"></td>
                    <td style="min-width: 130px;max-width: 130px;"></td>
                    ';
        if ($DisplayBillNo) {
            $content .= '<td style="min-width: 130px;max-width: 130px;"></td>';
        }
        $content .= '<td style="min-width: 100px;max-width: 300px;"></td>
                    <td style="min-width: 100px;max-width: 100px;"></td>
                    <td id="ArrivalQTY" style="font-weight: bold;">' . $ArrivalQTY . '</td>
                    <td style="min-width: 100px;max-width: 100px;"></td>
                    <td style="min-width: 100px;max-width: 100px;"></td>
                    <td style="min-width: 100px;max-width: 100px;"></td>
                    <td id="ReceivingQTY" style="font-weight: bold; style="min-width: 100px;max-width: 100px;">' . $ReceivingQTY . '</td>
                    <td style="min-width: 100px;max-width: 100px;"></td>
                    <td style="min-width: 100px;max-width: 100px;"></td>
                    <td style="min-width: 150px;width: 150px;"></td>
                </tr>
            </tfoot>
        </table>';

        if (!isset($_GET["excelDownload"]) || $_GET["excelDownload"] != "T") {
            $content .= '<div class="mt-3 mb-3">Showing 1 to ' . count($List) . ' out of ' . count($List) . ' entries</div>';
        }
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>Note: " . $error . "</div>";
    }
}

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $filename = 'Rm Received Report.xls';
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=" . $filename . "");
}

echo $content;
