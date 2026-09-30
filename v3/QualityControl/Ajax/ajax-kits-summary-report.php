<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_KITS_SUMMARY_REPORT;

$PageNo = trim($_POST["pageNo"]);
$bodyContent = array(
    'FromDate' => trim($_POST["fromDate"]),
    'ToDate' => trim($_POST["toDate"]),
    // 'SearchKey' => trim($_POST["searchKey"]),
    // 'PageNo' => trim($_POST["pageNo"]),
    // 'LoadAll' => trim($_POST["loadAll"])
);

$resList = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->KitSummaryData;

        $content = '';
        $content .= ' <table id="tblDataTable" ' . $border . ' class="table table-hover table-bordered" style="width: 100%; table-layout: fixed;">';
        $content .= $firstRow;

        $content .= '<thead class="thead-light">
                <tr>
                    <th style="min-width:50px;">SNo</th>
                    <th style="width: 120px; text-align: left;">Kits Order Date</th>
                    <th style="width: 120px; text-align: left;">Kits Received Date</th>
                    <th style="width: 120px; text-align: left;">Received Kits Invoice No</th>
                    <th style="width: 120px; text-align: left;">MFG Date</th>
                    <th style="width: 120px; text-align: left;">Exp Date</th>
                    <th style="width: 120px; text-align: left;">Parameter</th>
                    <th style="width: 120px; text-align: left;">Order Kits</th>
                    <th style="width: 120px; text-align: left;">Received Kits</th>
                    <th style="width: 120px; text-align: left;">Balance Kits</th>
                     <th style="width: 120px; text-align: left;">Lot Number</th>
                    <th style="width: 120px; text-align: left;">Kit Opening Date</th>
                     <th style="width: 120px; text-align: left;">Opening Wells</th>
                    <th style="width: 120px; text-align: left;">Used Wells</th>
                    <th style="width: 120px; text-align: left;">Damaged Wells</th>

                    <th style="width: 120px; text-align: left;">Balance Wells</th>
                    <th style="width: 120px; text-align: left;">Shift</th>
                    <th style="width: 120px; text-align: left;">Remarks</th>
                    <th style="width: 120px; text-align: left;">Analyzed By</th>
                    <th style="width: 120px; text-align: left;">Checked By</th>
                    <th style="width: 120px; text-align: left;">Verified By</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($List as $item) {
            $content .= '<tr>
                        <td style="width: 120px; text-align: left;">' . $item->SNO . '</td>
                        <td style="width: 120px; text-align: left;">' . $item->KitsOrderedDate . '</td>
                        <td style="width: 120px; text-align: left;">' . $item->KitsReceivedDate . '</td>
                        <td style="width: 120px; text-align: left;">' . $item->InvoiceNo . '</td>
                        <td style="width: 120px; text-align: left;">' . $item->ManufactureDate . '</td>
                        <td style="width: 120px; text-align: left;">' . $item->ExpiryDate . '</td>
                        <td style="width: 120px; text-align: left;">' . $item->Parameter . '</td>
                        <td style="width: 120px; text-align: left;">' . $item->NoOfKitsOrdered . '</td>
                        <td style="width: 120px; text-align: left;">' . $item->NoOfKitsReceived . '</td>
                         <td style="width: 120px; text-align: left;">' . $item->BalanceKits . '</td>
                        <td colspan="11" style="padding: 0px !important;">
                            <table ' . $border . ' class="subTable table table-bordered mb-0" style="width: 100%; table-layout: fixed; text-align: left;">';

            foreach ($item->BreakUpData as $itemBreak) {
                $content .= '<tr>
                              
                                <td style="width: 120px; text-align: left;">' . $itemBreak->KitBreakupLotNo . '</td>
                                <td style="width: 120px; text-align: left;">' . $itemBreak->KitOpeningDate . '</td>
                                <td style="width: 120px; text-align: left;">' . $itemBreak->NoOfWells . '</td>
                                <td style="width: 120px; text-align: left;">' . $itemBreak->UtilizedWells . '</td>
                                <td style="width: 120px; text-align: left;">' . $itemBreak->DamagedWells . '</td>
                                <td style="width: 120px; text-align: left;">' . $itemBreak->BalanceWells . '</td>
                                <td style="width: 120px; text-align: left;">' . $itemBreak->Shift . '</td>
                                <td style="width: 120px; text-align: left;">' . $itemBreak->Remarks . '</td>
                                <td style="width: 120px; text-align: left;">' . $itemBreak->AnalyzedBy . '</td>
                                <td style="width: 120px; text-align: left;">' . $itemBreak->ReceivedBy . '</td>
                                <td style="width: 120px; text-align: left;">' . $itemBreak->VerifiedBy . '</td>
                            </tr>';
            }

            $content .= '</table>
                       
        
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                    
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