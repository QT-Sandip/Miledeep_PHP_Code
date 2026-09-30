<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_FLOOR_BALANCE_REPORT;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $EndDate = trim($_GET["toDate"]);
    $ReceivingPlantID = trim($_GET["receivingPlantID"]);
    $PlantName = trim($_GET["plantName"]);
    $border = "border='1'";

    $firstRow = '<td colspan="23" style="text-align: center; font-weight: bold;">
            Floor Balance Report_TillDate:(' . date('d/m/Y', strtotime($EndDate)) . ')_ReceivingPlant: ' . $PlantName . '
            </td>';

    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="Floor Balance Report.xls"');
} else {
    $EndDate = trim($_POST["toDate"]);
    $ReceivingPlantID = trim($_POST["receivingPlantID"]);
    $border = "";
    $firstRow = '';
}

$bodyContent = array(
    "EndDate" => $EndDate,
    "ReceivingPlantID" => $ReceivingPlantID,
    "TypeOfMeterial" => $_POST['drpTypeOfMeterial'],
    "Treatment" => $_POST['drpTreatment']
);

$resList = CallAPI($api, "GET", "", $bodyContent);

if (!empty($resList)) {

    $error = $resList->Error;

    if (empty($error)) {
        $List = $resList->Data; // column headers
        $CombinationData = $resList->BalanceData->ArrayVarietyData; // rows
        $GrandTotals = $resList->BalanceData;

        // === Step 1: detect empty columns ===
        $columnHasData = [];
        foreach ($List as $colIndex => $colItem) {
            $hasData = false;
            foreach ($CombinationData as $row) {
                if (!empty($row->BalanceData[$colIndex]->Balance) && $row->BalanceData[$colIndex]->Balance != 0) {
                    $hasData = true;
                    break;
                }
            }
            $columnHasData[$colIndex] = $hasData;
        }

        // === Step 2: build table ===
        $content = '';
        $content .= '<table id="tblDataTable" ' . $border . ' style="width: 100%; border-collapse: collapse;" class="table table-hover table-bordered">';
        $content .= $firstRow;

        // Table header
        $content .= '<thead class="thead-light">    
            <tr>
                <th style="width: 50px; vertical-align: middle; text-align: center;">SNO</th>
                <th style="vertical-align: middle; text-align: center;">Variety</th>
                <th style="vertical-align: middle; text-align: center;">Grade</th>
                <th style="vertical-align: middle; text-align: center;">Count</th>
                <th style="vertical-align: middle; text-align: center;">Treatment</th>';

        foreach ($List as $colIndex => $item) {
            if ($columnHasData[$colIndex]) {
                $content .= '<th colspan="' . htmlspecialchars($item->rowCount) . '" style="min-width:120px">' .
                    htmlspecialchars($item->TrackCode) . ' <br>' . htmlspecialchars($item->ArrivalDate) . '</th>';
            }
        }

        $content .= '<th>Total KGs</th>
                <th>Total Crates</th>
                </tr>
        </thead>
        <tbody>';

        // Table rows
        $i = 0;
        foreach ($CombinationData as $item) {

            // check if this row has at least one non-empty & non-zero balance
            $rowHasData = false;
            foreach ($item->BalanceData as $colIndex => $itemList) {
                if ($columnHasData[$colIndex] && isset($itemList->Balance) && $itemList->Balance != "" && $itemList->Balance != 0) {
                    $rowHasData = true;
                    break;
                }
            }

            // skip if no balance data OR total KGs is zero/empty
            if (!$rowHasData || empty($item->TotalBalanceQty)) {
                continue; // skip entire row
            }

            $content .= '<tr>
                <td>' . ++$i . '</td>
                <td>' . htmlspecialchars($item->ShortName) . '</td>
                <td>' . htmlspecialchars($item->PackingGrade) . '</td>
                <td>' . htmlspecialchars($item->HLCount) . '</td>
                <td>' . htmlspecialchars($item->Treatment) . '</td>';

            foreach ($item->BalanceData as $colIndex => $itemList) {
                if ($columnHasData[$colIndex]) {
                    $balanceValue = isset($itemList->Balance) ? $itemList->Balance : '';
                    $content .= '<td>' . htmlspecialchars($balanceValue) . '</td>';
                }
            }

            $content .= '<td style="text-align: center; vertical-align: middle;">' . htmlspecialchars($item->TotalBalanceQty) . '</td>
                        <td style="text-align: center; vertical-align: middle;">' . htmlspecialchars($item->TotalBalanceCrates) . '</td>
                    </tr>';
        }

        $content .= '</tbody>
            <tr style="font-family: Arial, sans-serif; font-weight: 900; color: #000; text-align: center; vertical-align: middle;">
                <td></td>';

        if ($GrandTotals->GrandBalanceQty != 0) {
            $content .= '<td colspan="2" style="font-weight: 900 !important;">Grand Totals</td>';
        }

        $content .= '<td></td>
                <td></td>';

        foreach ($List as $colIndex => $item) {
            if ($columnHasData[$colIndex]) {
                $content .= '<td></td>';
            }
        }

        $content .= '<td style="font-weight: 900 !important;text-align: center; vertical-align: middle;">' . htmlspecialchars($GrandTotals->GrandBalanceQty) . '</td>
                <td style="font-weight: 900 !important;text-align: center; vertical-align: middle;">' . htmlspecialchars($GrandTotals->GrandBalanceCrates) . '</td>
                <!-- <td></td> -->
            </tr>
        </table>';

        if (!isset($_GET["excelDownload"]) || $_GET["excelDownload"] != "T") {
            $content .= '<div class="mt-3 mb-3">Showing 1 to ' . $i . ' out of ' . $i . ' entries</div>
                <div>
                    <ul class="pagination pagination-rounded justify-content-center"></ul>
                </div>';
        }

        echo $content;

    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>NOTE : " . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . "</div>";
    }

} else {
    echo "<div class='text-center mt-3 mb-3 text-bold'>No data available</div>";
}
