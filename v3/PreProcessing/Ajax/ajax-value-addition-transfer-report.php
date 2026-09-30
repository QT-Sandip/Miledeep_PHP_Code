<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_VALUE_ADDITION_TRANSFER_REPORT;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $StartDate = trim($_GET["fromDate"]);
    $EndDate = trim($_GET["toDate"]);
    $DateType = trim($_GET["dateType"]);
    $Plant = trim($_GET["productionPlant"]);
    $PlantName = trim($_GET["plantName"]);
    $border = "border=''";
    $firstRow = '<td colspan="19" style="text-align: center; font-weight: bold;">
            Value Addition Transfer Report_Date:(' . date('d/m/Y', strtotime($StartDate)) . ' - ' . date('d/m/Y', strtotime($EndDate)) . ')Plant: ' . htmlspecialchars($PlantName) . '
            </td>';
} else {
    $StartDate = trim($_POST["fromDate"]);
    $EndDate = trim($_POST["toDate"]);
    $DateType = trim($_POST["dateType"]);
    $Plant = trim($_POST["productionPlant"]);
    $border = "";
    $firstRow = '';
}


$bodyContent = array(

    "StartDate" => $StartDate,
    "EndDate" => $EndDate,
    "DateType" => $DateType,
    "Plant" => $Plant
);

// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// exit;


$resList = CallAPI($api, "GET", "", $bodyContent);

if (isset($resList) && !empty($resList)) {
    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->ReportData;
        $Totals = $resList->ReportTotals;

        $content = '';
        $content .= '<table id="tblDataTable" ' . $border . ' class="table table-hover table-bordered">';
        $content .= $firstRow;

        $content .= '<thead class="thead-light">
                <tr>
                    <th style="width: 50px;">S.No</th>
                    <th style="min-width: 100px; width: 100px;">Arrival Date</th>
                    <th style="min-width: 100px; width: 100px;">Bill Date</th>
                    <th style="min-width: 100px; width: 120px;">Production Plant</th>
                    <th style="min-width: 100px; width: 120px;">Vehicle Number</th>
                    <th style="min-width: 100px; width: 120px;">Bill Number</th>
                    <th style="min-width: 100px; width: 120px;">Supplier </th>
                    <th style="min-width: 100px; width: 120px;">Center Name</th>
                    <th style="min-width: 100px; width: 120px;">Production Plant </th>
                    <th style="min-width: 100px; width: 120px;">Variety</th>
                    <th style="min-width: 50px; width: 50px;">Count </th>
                    <th style="min-width: 100px; width: 120px;">Rm Production plant </th>
                    <th style="min-width: 100px; width: 120px;">Transfer Date</th>
                    <th style="min-width: 100px; width: 100px;">Final Qty</th>
                    <th style="min-width: 80px; width: 80px;">Rate</th>
                    <th style="min-width: 100px; width: 120px;">Purchase Amount</th>
                    <th style="min-width: 80px; width: 80px;">Harvest Commission</th>
                    <th style="min-width: 80px; width: 80px;">Harvest Amout</th>
                    <th style="min-width: 80px; width: 80px;">Final Amount </th>
                </tr>
            </thead>
            <tbody>';
            $TotalFinalWeight=0;
            $TotalPurchaseAmount=0;
            $TotalFinalAmount=0;
            foreach ($List as $item) {
                $TotalFinalWeight=$FinalWeight;
            $TotalPurchaseAmount=$PurchaseAmount;
            $TotalFinalAmount=$FinalAmountAmount;
                $content .= '<tr>
                <td style="width: 50px;">'. $item->SNO .'</td>
                        <td style="min-width: 100px; width: 100px;">'. $item->ArrivalDate.'</td>
                        <td style="min-width: 100px; width: 100px;">'. $item->BillDate.'</td>
                        <td style="min-width: 100px; width: 120px;">'. $item->ProductionPlant.'</td>
                        <td style="min-width: 120px; width: 120px;">'. $item->VehicleNumber.'</td>
                        <td style="min-width: 100px; width: 120px;">'. $item->ArrivalNumber.'</td>
                        <td style="min-width: 100px; width: 120px;">'. $item->SupplierName.'</td>
                        <td style="min-width: 100px; width: 120px;">'. $item->CenterName.'</td>
                        <td style="min-width: 100px; width: 120px;">'. $item->ProductionPlant.'</td>
                        <td style="min-width: 100px; width: 120px;">'. $item->Variety . '-' . $item->Species.'</td>
                        <td style="min-width: 50px; width: 50px;">'. number_format($item->Count, 2).'</td>
                        <td style="min-width: 100px; width: 120px;">'. $item->RmProductionPlant.'</td>
                        <td style="min-width: 100px; width: 120px;">'. $item->TransferDate.'</td>
                        <td class="tdFinalWeight" style="min-width: 100px; width: 100px;">'. number_format($item->FinalWeight, 2).'</td>
                        <td style="min-width: 80px; width: 80px;">'. number_format($item->Rate, 2).'</td>
                        <td class="tdPurchaseAmount"  style="min-width: 100px; width: 120px;">'. number_format($item->PurchaseAmount, 2).'</td>
                        <td style="min-width: 80px; width: 80px;">'. (($item->HarvestCommission != 0) ? number_format($item->HarvestCommission, 2) : '0').'</td>
                        <td style="min-width: 80px; width: 80px;">'. (($item->HarvestAmount != 0) ? number_format($item->HarvestAmount, 2) : '0').'</td>
                        <td class="tdFinalAmount" style="min-width: 80px; width: 80px;">'. number_format($item->FinalAmountAmount, 2).'</td>
                    </tr>';
                } 
        $content .= '</tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td style="font-family: Arial, sans-serif; font-weight: 900; color: #000; text-align: center; vertical-align: middle;">Totals</td>
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
                    <td id="TotalFinalWeight" style="font-family: Arial, sans-serif; font-weight: 900; color: #000; text-align: center; vertical-align: middle;">'.number_format($resList->TotalFinalWeight, 2).'</td>
                    <td></td>
                    <td id="TotalPurchaseAmount" style="font-family: Arial, sans-serif; font-weight: 900; color: #000; text-align: center; vertical-align: middle;">'.number_format($resList->TotalPurchaseAmount, 2).'</td>
                    <td></td>
                    <td></td>
                    <td id="TotalFinalAmount" style="font-family: Arial, sans-serif; font-weight: 900; color: #000; text-align: center; vertical-align: middle;">'.number_format($resList->TotalFinalAmount, 2).'</td>
                </tr>
            </tfoot>
        </table>';
    } else {
        $content .= "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}
    if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
        $filename = 'Value_Addition_Transfer_Report.xls';
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment; filename=" . $filename);
    }
    echo $content;
    


