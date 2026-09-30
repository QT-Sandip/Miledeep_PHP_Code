<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API__SOAKING_FLOOR_BALANCE_REPORT_VARIETY;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $EndDate = trim($_GET["toDate"]);
    $ReceivingPlantID = trim($_GET["receivingPlantID"]);
    $PlantName = trim($_GET["plantName"]);
    $ChillRoomPreparationType = $_GET["ChillRoomPreparationType"];

    $ToDate = date('d/m/Y', strtotime($EndDate));

    $border = "border='1'";
    $firstRow = '<td colspan="23" style="text-align: center; font-weight: bold;">
            Chill Room Balance Report_TillDate:(' . $ToDate . ')_ReceivingPlant: ' . $PlantName . '
            </td>';

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Chill Room Balance Report_TillDate:'. $ToDate . '.xls"');

    echo '<html><head><meta charset="UTF-8"></head><body>';
} else {
    $EndDate = trim($_POST["toDate"]);
    $ReceivingPlantID = trim($_POST["receivingPlantID"]);
    $ChillRoomPreparationType = trim($_POST["ChillRoomPreparationType"]);
    $border = "";
    $firstRow = '';
}

// Fetch data
$bodyContent = array(
    "EndDate" => $EndDate,
    "ReceivingPlantID" => $ReceivingPlantID,
    "ChillRoomPreparationType" => $ChillRoomPreparationType,
);

$resList = CallAPI($api, "GET", "", $bodyContent);



if (isset($resList) && !empty($resList)) {
    $error = $resList->Error;

    if (empty($error)) {

        $RawHLList = $resList->RawHLTrackData;
        $RawHLCombinationData = $resList->RawHLBalanceData->ArrayVarietyData;
        $RawHLGrandTotals = $resList->RawHLBalanceData;
        $RawHLTableID = "tblDataTableRawHL";

        $RawMeatLList = $resList->RawMeatTrackData;
        $RawMeatCombinationData = $resList->RawMeatBalanceData->ArrayVarietyData;
        $RawMeatGrandTotals = $resList->RawMeatBalanceData;
        $RawMeatTableID = "tblDataTableRawMeat";

        $CookedMeatList = $resList->CookedMeatTrackData;
        $CookedMeatCombinationData = $resList->CookedMeatBalanceData->ArrayVarietyData;
        $CookedMeatGrandTotals = $resList->CookedMeatBalanceData;
        $CookedMeatTableID = "tblDataTableCookedMeat";

        $BlockMeatList = $resList->BlockMeatTrackData;
        $BlockMeatCombinationData = $resList->BlockMeatBalanceData->ArrayVarietyData;
        $BlockMeatGrandTotals = $resList->BlockMeatBalanceData;
        $BlockMeatTableID = "tblDataTableBlockMeat";

        $ThawingMeatList = $resList->ThawingMeatTrackData;
        $ThawingMeatCombinationData = $resList->ThawingMeatBalanceData->ArrayVarietyData;
        $ThawingMeatGrandTotals = $resList->ThawingMeatBalanceData;
        $ThawingMeatTableID = "tblDataTableThawingMeat";

        $CookedHLList = $resList->CookedHLTrackData;
        $CookedHLCombinationData = $resList->CookedHLBalanceData->ArrayVarietyData;
        $CookedHLGrandTotals = $resList->CookedHLBalanceData;
        $CookedHLTableID = "tblDataTableCookedHL";


        $table1 = generateTable($RawHLTableID, $RawHLList, $RawHLCombinationData, $RawHLGrandTotals, $border, $firstRow, 'Raw HL');
        $table2 = generateTable($CookedHLTableID, $CookedHLList, $CookedHLCombinationData, $CookedHLGrandTotals, $border, $firstRow, 'Cooked HL');
        $table3 = generateTable($RawMeatTableID, $RawMeatLList, $RawMeatCombinationData, $RawMeatGrandTotals, $border, $firstRow, 'Raw Meat');
        $table4 = generateTable($CookedMeatTableID, $CookedMeatList, $CookedMeatCombinationData, $CookedMeatGrandTotals, $border, $firstRow, 'Cooked Meat');
        $table5 = generateTable($BlockMeatTableID, $BlockMeatList, $BlockMeatCombinationData, $BlockMeatGrandTotals, $border, $firstRow, 'Block Meat');
        $table6 = generateTable($ThawingMeatTableID, $ThawingMeatList, $ThawingMeatCombinationData, $ThawingMeatGrandTotals, $border, $firstRow, 'Thawing Meat');

        if (!empty($RawHLList) && is_array($RawHLList)) echo $table1;
        if (!empty($CookedHLList) && is_array($CookedHLList)) echo $table2;
        if (!empty($RawMeatLList) && is_array($RawMeatLList)) echo $table3;
        if (!empty($CookedMeatList) && is_array($CookedMeatList)) echo $table4;
        if (!empty($BlockMeatList) && is_array($BlockMeatList)) echo $table5;
        if (!empty($ThawingMeatList) && is_array($ThawingMeatList)) echo $table6;

    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>NOTE : " . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</div>";
    }
} else {
    echo "<div class='text-center mt-3 mb-3 text-bold'>No data available</div>";
}


// Define the function to generate the table
function generateTable($TableId, $List, $CombinationData, $GrandTotals, $border, $firstRow, $HeaderName)
{
    // ✅ Determine valid columns (not fully empty)
    $validCols = [];
    foreach ($List as $colIndex => $colItem) {
        $hasData = false;
        foreach ($CombinationData as $rowItem) {
            if (!empty($rowItem->BalanceData[$colIndex]->Balance) && $rowItem->BalanceData[$colIndex]->Balance != 0) {
                $hasData = true;
                break;
            }
        }
        if ($hasData) {
            $validCols[] = $colIndex;
        }
    }

    $content = '<table id="' . $TableId . '" ' . $border . ' style="width: 100%; border-collapse: collapse;" class="table table-hover table-bordered">';
    $content .= $firstRow;

    $content .= '<thead class="thead-light">   
    <tr> <th style="width: 200px; vertical-align: middle; text-align: center;">  <h5>' . $HeaderName . '</h5>  </th></tr>
    <tr>
        <th style="width: 20px; vertical-align: middle; text-align: center;">SNO</th>       
        <th style="vertical-align: middle; text-align: center;">Variety</th>
        <th style="vertical-align: middle; text-align: center;">Grade</th>';

    foreach ($List as $colIndex => $item) {
        if (in_array($colIndex, $validCols)) {
            $content .= '<th colspan="' . htmlspecialchars($item->rowCount) . '" style="min-width:120px;text-align: center;">'
                . htmlspecialchars($item->TrackCode) . ' <br>' . htmlspecialchars($item->ArrivalDate) . '</th>';
        }
    }

    $content .= '<th style="vertical-align: middle; text-align: center;">Total KGs</th></tr></thead><tbody>';

    $i = 0;
    foreach ($CombinationData as $item) {

      
        // ✅ Check if row has any non-empty balance
        $rowHasData = false;
        foreach ($validCols as $colIndex) {
            //   echo($validCols);exit;
            if (!empty($item->BalanceData[$colIndex]->Balance) && $item->BalanceData[$colIndex]->Balance != 0) {
                $rowHasData = true;
                break;
            }
        }
        if (!$rowHasData && (empty($item->TotalBalanceQty) || $item->TotalBalanceQty == 0)) {
            continue; // Skip this row
        }

        $content .= '<tr>
            <td style="vertical-align: middle; text-align: center;">' . ++$i . '</td>
            <td style="vertical-align: middle; text-align: center;">' . htmlspecialchars($item->ShortName) . '</td>
            <td style="vertical-align: middle; text-align: center;">' . htmlspecialchars($item->PackingGrade) . '</td>';

        foreach ($validCols as $colIndex) {
            $val = isset($item->BalanceData[$colIndex]->Balance) ? $item->BalanceData[$colIndex]->Balance : '';
            $content .= '<td style="vertical-align: middle; text-align: center;">' . htmlspecialchars($val) . '</td>';
        }

        $content .= '<td style="vertical-align: middle; text-align: center;">' . htmlspecialchars($item->TotalBalanceQty) . '</td></tr>';
    }

    //  Grand totals
    $content .= '</tbody><tr style="font-family: Arial, sans-serif; font-weight: 900; color: #000; text-align: center; vertical-align: middle;"><td></td>';

    if ($GrandTotals->GrandBalanceQty != 0) {
        $content .= '<td></td><td style="vertical-align: middle; text-align: center;">Grand Totals</td>';
    }

    foreach ($validCols as $colIndex) {
        $content .= '<td></td>';
    }

    $content .= '<td style="vertical-align: middle; text-align: center;">' . htmlspecialchars($GrandTotals->GrandBalanceQty) . '</td></tr></table>';

    return $content;
}

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    echo '</body></html>';
}
