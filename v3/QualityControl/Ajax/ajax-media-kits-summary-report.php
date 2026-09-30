<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_MEDIA_KITS_SUMMARY_REPORT;

$PageNo = trim($_POST["pageNo"]);
$bodyContent = array(
    'FromDate' => trim($_POST["fromDate"]),
    'ToDate' => trim($_POST["toDate"]),
    'Status' => trim($_POST["status"]),
    // 'SearchKey' => trim($_POST["searchKey"]),
    // 'PageNo' => trim($_POST["pageNo"]),
    // 'LoadAll' => trim($_POST["loadAll"])
);

$resList = CallAPI($api, "GET", "", $bodyContent);
//echo '<pre>';print_r($resList);die;
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->KitSummaryData;
        header('Content-Type: application/json');
        echo json_encode($List);
        exit;
        $content = '';
        $content .= ' <table id="tblDataTable" ' . $border . ' class="table table-hover table-bordered" style="width: 100%; table-layout: fixed;">';
        $content .= $firstRow;

        $content .= '<thead class="thead-light">
                <tr>
                    <th style="min-width:50px;">SNo</th>
                    <th style="width: 120px; text-align: left;">Order Date</th>
                    <th style="width: 120px; text-align: left;">Received Date</th>
                    <th style="width: 120px; text-align: left;">Received  Invoice No</th>
                    <th style="width: 120px; text-align: left;">MFG Date</th>
                    <th style="width: 120px; text-align: left;">Exp Date</th>
                    <th style="width: 120px; text-align: left;">Media</th>
                    <th style="width: 120px; text-align: left;">Order </th>
                    <th style="width: 120px; text-align: left;">Received </th>
                    <th style="width: 120px; text-align: left;">Balance </th>
                     <th style="width: 120px; text-align: left;">Lot Number</th>
                    <th style="width: 120px; text-align: left;">Opening Date</th>
                     <th style="width: 120px; text-align: left;">Total Qty</th>
                    <th style="width: 120px; text-align: left;">Used Qty</th>
                    <th style="width: 120px; text-align: left;">Balance</th>                    
                </tr>
            </thead>
            <tbody>';

        foreach ($List as $item) {
            // echo print_r($List);
            // exit;
            $content .= '<tr>
    <td>' . $item->SNO . '</td>
    <td>' . $item->KitsOrderedDate . '</td>
    <td>' . $item->KitsReceivedDate . '</td>
    <td>' . $item->InvoiceNo . '</td>
    <td>' . $item->ManufactureDate . '</td>
    <td>' . $item->ExpiryDate . '</td>
    <td>' . $item->Media . '</td>
    <td>' . $item->NoOfKitsOrdered . '</td>
    <td>' . $item->NoOfKitsReceived . '</td>
    <td>' . $item->BalanceKits . '</td>
    <td colspan="5" style="padding: 0px !important;">
        <table class="subTable table table-bordered mb-0" style="width: 100%; table-layout: fixed;">
';

foreach ($item->BreakUpData as $itemBreak) {
    $content .= '<tr>
        <td>' . $itemBreak->KitBreakupLotNo . '</td>
        <td>' . $itemBreak->KitOpeningDate . '</td>
        <td>' . $itemBreak->NoOfWells . '</td>
        <td>' . $itemBreak->UtilizedWells . '</td>
        <td>' . $itemBreak->BalanceWells . '</td>
    </tr>';
}

$content .= '</table>
    </td>
</tr>';
        }

        $content .= '</tbody></table>';

        if (!isset($_GET["excelDownload"]) || $_GET["excelDownload"] != "T") {
            $content .= '<div class="mt-3 mb-3">Showing 1 to ' . count($List) . ' out of ' . count($List) . ' entries</div>';
        }

    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>Note: " . $error . "</div>";
    }
}

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $filename = 'Kits Summary Report.xls';
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=" . $filename . "");
}

echo $content;
?>