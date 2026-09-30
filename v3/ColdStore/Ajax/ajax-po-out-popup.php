<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($cp . "Includes/IncludePage.php");
include_once ROOT . 'config/api-call.php';
$ClientConfigData = $_SESSION['ClientConfigData'];
$convertLooseToMcs = !empty($ClientConfigData['convertLooseToMcs']) ? trim($ClientConfigData['convertLooseToMcs']):0; //sriram


$sumMCS = $_POST["sumMCS"];
$mcsValues = $_POST["mcsValues"];
$PoID = $_POST["PoID"];
$sumLoose = $_POST["sumLoose"];

// var_dump($_POST);
// exit;

$api = API_PO_OUT_POPUP;

$bodyContent = array(
    'InvId' => $PoID,
    'mcsValues' => $mcsValues,
);

$resList = CallAPI($api, "GET", "", $bodyContent);

$List = $resList->Data;
$TotalMcs = $resList->TotalMcs;
$IsStockOutDateAtCs = $resList->IsStockOutDateAtCs;
$LooseArray = htmlspecialchars(json_encode($resList->LooseArray), ENT_QUOTES, 'UTF-8');
$StockOutDate = $resList->SaleStockOutDate;

// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;

$data .= '<table  class="table table-bordered ">
    <thead>
        <tr>
            <th>Variety</th>
            <th>Grade</th>
            <th>PackingStyle Style</th>
            <th>FreezingType Type</th>
            <th>Glaze</th>
            <th style="width: 100px;">Reason</th>
            <th>Total Mcs</th>
            <th>Aval Mcs</th>';
            if($convertLooseToMcs == 1){
        $data .=    '<th>Aval Loose Mcs</th>
            <th>Select Loose Mcs</th>
            <th>Total Aval Mcs</th>
            <th>Remaining Loose Bags</th>';
            }
        $data .=    '<th>Balance Mcs</th>
        </tr>
    </thead>
    <tbody>';

$mcsValueIndex = 0;
$totalLooseMcs = 0;
$Balance = 0;
foreach ($List as $item) {

    $OrderMcsPerGrade = $item->OrderedMcs;
    $SelectedMcs = $item->SelectedMcs;
    $SelectedLoose = $item->SelectedLoose;
    $SelectedAvalMcs = $SelectedMcs;
    if($convertLooseToMcs == 1){
        $SelectedMcs = $SelectedMcs + $SelectedLoose;
    }
    $ReqMcs = $OrderMcsPerGrade - $SelectedMcs;
    $Balance += $ReqMcs;
    $totalLooseMcs += $SelectedLoose;

    // echo $OrderMcsPerGrade;

    $data .= '
        <tr class="loose-conversion">
            <td >' . $item->Variety . '</td>
            <td >' . $item->Grade . '</td>
            <td >' . $item->PackingStyle . '</td>
            <td >' . $item->FreezingType . '</td>
            <td >' . $item->GlazePerc . '</td>
            <td><div class="col-md-6 col-lg-3 mb-3" style="width:100%">
                <select class="form-control drpSelect" id="drpReason" name="drpReason">
                    <option value="Shipment Out" data-transtypeid="0">Shipment Out</option>
                </select></div>
            </td>
            <td class="ordermcs">' . $OrderMcsPerGrade . '</td>
            <td class="avalmcs">' . $SelectedAvalMcs . '</td>';
            if($convertLooseToMcs == 1){
        $data .= '<td class="totalLooseMcs">' . $SelectedLoose . '</td>
            <td >
                <input type="number"
                    style="width:150px;"
                    class="form-control txtLooseMcs"
                    name="txtLooseMcs"
                    value="' . $SelectedLoose . '"
                    min="0"
                    max="' . $SelectedLoose . '"
                    data-ordermcs="' . $OrderMcsPerGrade . '"
                    data-avalmcs="' . $SelectedAvalMcs . '"
                    data-varietyid="' . $item->VarietyID . '">
            </td>
            <td class="selectedMcs">' . $SelectedMcs . '</td>
            <td >' . $item->looseBags . '</td>';
            }
            $data .= '<td class="reqMcs">' . $ReqMcs . '</td>';

    // if (isset($mcsValues) && is_array($mcsValues) && isset($mcsValues[$mcsValueIndex])) {
    //  $data .= '<td class="tdSumAvalMcs">' . $mcsValues[$mcsValueIndex] . '</td>';

    //  $ReqMcs = $mcsValues[$mcsValueIndex] - $OrderMcsPerGrade;
    //  $data .= '<td >' . $ReqMcs . '</td>';

    //  $mcsValueIndex++;
    // } else {
    //  $data .= '<td class="tdSumAvalMcs"></td>';
    //  $data .= '<td></td>';
    // }

    $data .= '</tr>';
}
// if($convertLooseToMcs == 1){
//  $Balance += $totalLooseMcs;
// }
$data .= '<tr>

        <td >Balance Mcs</td>
        <td ></td>
        <td ></td>
        <td ></td>
        <td ></td>
        <td ></td>
        <td ></td>
        <td ></td>';
        if($convertLooseToMcs == 1){
        $data .= '<td ></td>
        <td></td>
        <td ></td>
        <td ></td>';
        }
$data .= '<td>' . $Balance . '</td>';
$data .= '</tr>';
$data .= '<tr>
        <td ></td>
        <td ></td>
        <td ></td>
        <td ></td>
        <td ></td>
        <td >'.(($IsStockOutDateAtCs) ?
            '<label> Stock Out Date</label>
                <input type="date" value='.$StockOutDate.' class="form-control" name="PoDate" id="stock-out-date" placeholder="Enter Stock Out Date" '.(($StockOutDate!='') ? 'readonly=""' : '').'>
                <input type="hidden" id="IsStockOutAtCs" value='.$IsStockOutDateAtCs.'>
                <input type="hidden" id="sales-stockout-date" value='.$StockOutDate.'>'
            : '').'
        </td>
        <td></td>
        <td ></td>';
        if($convertLooseToMcs == 1){
        $data .= '<td ></td>
        <td ></td>
        <td ></td>
        <td ></td>';
        }
if ($Balance != 0) {
    $data .= '<td  style="width: 110px"><label>&nbsp;</label><button type="button"  disabled id="CSOUT" class="btn btn-danger">Stock Out </button>
    <input type="hidden" id="tdRemainingLooseBags" value='.$LooseArray.'></td>';
} else {
    $data .= '<td  style="width: 110px"><label>&nbsp;</label><button type="button"  id="CSOUT" class="btn btn-primary">Stock Out </button>
    <input type="hidden" id="tdRemainingLooseBags" value='.$LooseArray.'></td>';
}
$data .= '</tr>';

$data .= '</tbody></table>';
echo $data;
exit;
?>
