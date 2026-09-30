<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);
$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$hasPpcStandardYields = $ArrayClientConfigData['hasPpcStandardYields'];
$hasWageSelector = $ArrayClientConfigData['hasWageSelector'];
if (isset($methodType) && !empty($methodType)) {

    $api = API_VALUE_ADDITION_DETAILS;
    //    exit;

    if ($methodType == "get") {

        if (isset($_POST["ArrivalID"])) {
            $bodyContentPost = array(
                "ArrivalID" => trim($_POST["ArrivalID"])
            );
            $result = CallAPI($api, "GET", "", $bodyContentPost);
        } else if (isset($_POST["TransactionDate"])) {
            $bodyContentPost = array(
                "TransactionDate" => trim($_POST["TransactionDate"])
            );
            $result = CallAPI($api, "GET", "", $bodyContentPost);
        }
        // echo json_encode($result, JSON_PRETTY_PRINT); exit;

    } else if ($methodType == "post") {

        $ArrivalDate = trim($_POST["ArrivalDate"]);
        $ArrivalID = trim($_POST["ArrivalID"]);
        // $AriavalLotCode = trim($_POST["drpLotCode"]);
        $ProductionPlant = trim($_POST["drpProductionPlant"]);
        $Center = trim($_POST["drpCenter"]);
        $TransactionDate = trim($_POST["txtTransactionDate"]);
        $Shift = trim($_POST["drpShift"]);
        $IceConsumption = trim($_POST["txtIceConsumption"]);
        $Remarks = trim($_POST["txtRemarks"]);
        $Variety = trim($_POST["drpVariety"]);
        $HLCount = trim($_POST["drpHLCount"]);
        $HLWt = trim($_POST["txtHLWt"]);
        $ReceivedHLWt = trim($_POST["txtReceivedHLWt"]);
        $WeightAfterVA = trim($_POST["txtWeightAfterVA"]);
        $CountAfterVA = trim($_POST["txtCountAfterVA"]);
        $ReturnWt = trim($_POST["txtReturnWt"]);
        $BrokenWt = trim($_POST["txtBrokenWt"]);
        $hlBreakUpId = trim($_POST["hlBreakUpId"]);
        $WageID = trim($_POST["drpWages"]);
        $bodyContentPost = array(
            "ArrivalID" => $ArrivalID,
            "Plant" => $ProductionPlant,
            "Center" => $Center,
            "TransactionDate" => $TransactionDate,
            "Shift" => $Shift,
            "IceConsumption" => $IceConsumption,
            "Remarks" => $Remarks,
            "VarietyID" => $Variety,
            "HLCount" => $HLCount,
            "HLQuantity" => $HLWt,
            "ReceivedWeight" => $ReceivedHLWt,
            "WeightAfterVA" => $WeightAfterVA,
            "CountAfterVA" => $CountAfterVA,
            "ReturnWeight" => $ReturnWt,
            "BrokenWeight" => $BrokenWt,
            "HLBreakupID" => $hlBreakUpId,
            "WageID" => $WageID
        );

        // var_dump($bodyContentPost);
        // echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
        // exit;
        $result = CallAPI($api, "POST", "", $bodyContentPost);

        // var_dump($bodyContentPost);

        // var_dump($result);
        // exit();

    } else if ($methodType == "delete") {

        $ArrivalID = $_POST["ArrivalID"];
        $BreakUpID = $_POST["BreakUpID"];
        $TransactionDate = $_POST["transactionDate"];

        $bodyContent = array(
            "ArrivalID" => $ArrivalID,
            "BreakUpID" => $BreakUpID,
            "TransactionDate" => $TransactionDate,
        );

        $result = CallAPI($api, "DELETE", "", $bodyContent);
    } else if ($methodType == "update") {

        $BreakUpID = $_POST["BreakUpID"];
        $ArrivalID = $_POST["ArrivalID"];

        $ArrivalDate = trim($_POST["ArrivalDate"]);
        $ProductionPlant = trim($_POST["drpProductionPlant"]);
        $Center = trim($_POST["drpCenter"]);
        $TransactionDate = trim($_POST["txtTransactionDate"]);
        $Shift = trim($_POST["drpShift"]);
        $IceConsumption = trim($_POST["txtIceConsumption"]);
        $Remarks = trim($_POST["txtRemarks"]);
        $Variety = trim($_POST["drpVariety"]);
        $HLCount = trim($_POST["drpHLCount"]);
        $HLWt = trim($_POST["txtHLWt"]);
        $ReceivedHLWt = trim($_POST["txtReceivedHLWt"]);
        $WeightAfterVA = trim($_POST["txtWeightAfterVA"]);
        $CountAfterVA = trim($_POST["txtCountAfterVA"]);
        $ReturnWt = trim($_POST["txtReturnWt"]);
        $BrokenWt = trim($_POST["txtBrokenWt"]);
        $hlBreakUpId = trim($_POST["hlBreakUpId"]);
        $WageID = trim($_POST["drpWages"]);
        $bodyContent = array(
            "BreakUpID" => $BreakUpID,
            "ArrivalID" => $ArrivalID,
            "Plant" => $ProductionPlant,
            "Center" => $Center,
            "TransactionDate" => $TransactionDate,
            "Shift" => $Shift,
            "IceConsumption" => $IceConsumption,
            "Remarks" => $Remarks,
            "VarietyID" => $Variety,
            "HLCount" => $HLCount,
            "HLQuantity" => $HLWt,
            "ReceivedWeight" => $ReceivedHLWt,
            "WeightAfterVA" => $WeightAfterVA,
            "CountAfterVA" => $CountAfterVA,
            "ReturnWeight" => $ReturnWt,
            "BrokenWeight" => $BrokenWt,
            "HLBreakupID" => $hlBreakUpId,  // Grading BreakUpID
            "WageID" => $WageID
        );

        // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
        // exit;
        $result = CallAPI($api, "POST", "", $bodyContent);

        // echo json_encode($result, JSON_PRETTY_PRINT);
        // exit;

    }

    // echo json_encode($result, JSON_PRETTY_PRINT);
    // exit;

    $error = $result->Error;
    $msg = $result->Msg;
    $Data = end($result->Data->ValueAdditionData);
    $List = $result->Data->ValueAdditionData;

    $HLCounts = $result->Data->HLCounts;
    // var_dump($List);

    if (!isset($error) || empty($error)) {
        $htmlResponse = '
            <div class="card">
                <div class="card-body">
                    <table id="tblDataTable" class="table table-hover table-bordered">
                        <thead class="thead-light">
                                <tr>
                                    <th style="width:50px;">S.No</th>
                                    <th>Arrival Code </th>
                                    <th>Production Plant</th>
                                    <th>Center</th>
                                    <th>Transaction Date</th>
                                    <th>Shift</th>';

        if ($hasWageSelector == 1) {
            $htmlResponse .= '<th>Wages</th>';
        }
        $htmlResponse .= '<th>Variety </th>
                                    <th>HL Wt(Kgs)</th>
                                    <th>HL Count</th>
                                    <th>Received HL Wt(Kgs)</th>
                                    <th>Weight after VA(Kgs)</th>
                                    <th>Count after VA </th>
                                    <th>Return Wt(Kgs)</th>
                                    <th>Broken Wt(Kgs)</th>
                                    <th>Yield %</th>';
        if ($hasPpcStandardYields == 1) {
            $htmlResponse .= '<th>Standard Yield %</th>
                                    <th>Yield Diff </th>';
        }
        $htmlResponse .= '<th>Ice Consumption</th>
                                    <th>Remarks</th>
                                    <th>Actions</th>
                                </tr>
                        </thead>
                        <tbody>';
        $i = 1;
        foreach ($List as $item) {
            // var_dump($item);
            $htmlResponse .= '
                                <tr>
                                    <td >' . $item->SNo . '</td>
                                    <td>' . $item->LotNo . '</td>
                                    <td class="tdPlant" data-plantid="' . $item->PlantID . '">' . $item->Plant . '</td>
                                    <td class="tdCenter" data-centerid="' . $item->CenterID . '">' . $item->Center . '</td>
                                    <td>' . $item->ProcessingDate . '</td>
                                    <td class="tdShift" data-shiftid="' . $item->ShiftID . '">' . $item->Shift . '</td>';
            if ($hasWageSelector == 1) {
                $htmlResponse .= '<td class="tdWages" data-wageid="' . $item->WageID . '">' . $item->Wage . '</td>';
            }
            $htmlResponse .= '<td class="tdVariety" data-varietyid="' . $item->VarietyID . '">' . $item->Variety . '</td>
                                    <td class="tdHLWt">' . $item->HLQuantityKgs . '</td>
                                    <td class="tdHLCount">' . $item->HLCount . '</td>
                                    <td class="tdReceivedWeight">' . $item->ReceivedWeight . '</td>
                                    <td class="tdWtAftrVA">' . $item->WtAftrVA . '</td>
                                    <td class="tdCountAftrVA">' . $item->CountAftrVA . '</td>
                                    <td class="tdReturnWeight">' . $item->ReturnWeight . '</td>
                                    <td class="tdBrokenWeight">' . $item->BrokenWeight . '</td>
                                    <td class="tdYield">' . $item->Yield . '% </td>';
            if ($hasPpcStandardYields == 1) {
                $htmlResponse .= ' <td class="tdStandardYield">' . (!empty($item->standardYield) ? $item->standardYield . "%" : "") . '</td>
                                    <td class="tdYieldDiff">' . (!empty($item->Diff) ? $item->Diff . "%" : "") . ' </td>';
            }
            $htmlResponse .= '<td class="tdIceConsumption">' . $item->IceConsumption . '</td>
                                    <td class="tdRemarks">' . $item->Remarks . '</td>
                                    <td>
                                        <button type="button" id="btnDelete" name="btnDelete" data-ArrivalId="' . $item->ArrivalID . '" data-ArrivalBrkUpID="' . $item->BreakUpID . '" class="btn btn-link p-0 deleteBrkUp" ' . $DeleteAccess . ' >
                                            <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                        </button>

                                        <button type="button"  data-ArrivalId="' . $item->ArrivalID . '" data-ArrivalBrkUpID="' . $item->BreakUpID . '" class="btn btn-link p-0 btnEdit" ' . $DeleteAccess . ' >
                                            <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                                        </button>
                                    </td>  
                                </tr>';
            $i++;
        }
        $htmlResponse .= '
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td> 
                                <td><span  style="font-weight: 600;font-size: 16px;">Grand Total</span></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><span style="font-weight: 600;font-size: 16px;">' . $result->Data->Totals->TotalHLQuantity . '</span></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>';
    }
    $response = array('html' => $htmlResponse, 'json' => $Data, 'hlCounts' => $HLCounts, "Error" => $error, "Msg" => $msg);
    echo json_encode($response);

    // echo json_encode($result, JSON_PRETTY_PRINT);
    // exit();
} else {

    $api = API_RM_PRODUCTION_LIST;

    $ArrivalDate = trim($_POST["ArrivalDate"]);
    $Type = trim($_POST["Type"]);

    $bodyContent = array(
        'ArrivalDate' => $ArrivalDate,
        'Type' => $Type,
        'ArrivalPlant' => "",
        'Supplier' => "",
        'PsId' => "",
        'Version' => '0'//v2
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);

    // var_dump($resList);
    // exit;
    header('Content-Type: application/json');
    echo json_encode($resList);
}
