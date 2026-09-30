<?php
session_start();

// SESSION VALUES 
$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$ShowDeheadingStationID = $ArrayClientConfigData['ShowDeheadingStationID'];
$hasVACrates = $ArrayClientConfigData['hasVACrates'];
$disableVAEdit = $ArrayClientConfigData['disableVAEdit'];
$EnabelGradeField = $ArrayClientConfigData['EnabelGradeField'];
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$AddvalueAddition = $ArrayClientConfigData['AddvalueAddition'];

$methodType = trim($_POST["methodType"]);
$VaStation = '';
if ($ShowDeheadingStationID == 1) {
    $VaStation = trim($_POST["vaStation"]);
}

if (isset($methodType) && !empty($methodType)) {

    $api = API_VALUE_ADDITION_TRACKING_DETAILS;

    if ($methodType == "getData") {

        $bodyContent = array(
            "TrackID" => trim($_POST["trackID"]),
            "PlantID" => trim($_POST["plantid"]),
            "IsRejInVA" => trim($_POST["isRejInVA"]),
            "AddvalueAddition" => $AddvalueAddition
        );
        $result = CallAPI($api, "GET", "", $bodyContent);
    } else if ($methodType == "postForm") {

        if ($ShowDeheadingStationID == 0) {
            $StationID = "0";
        } else {
            $StationID = trim($_POST["vaStation"]);
        }
        $bodyContentPost = array(
            "BreakUpID" => trim($_POST["breakUpID"]),
            "ArrivalID" => trim($_POST["arrivalid"]),
            "ArrivalDate" => trim($_POST["arrivalDate"]),
            "TrackID" => trim($_POST["trackID"]),
            "PlantID" => trim($_POST["arrivalPlant"]),
            "StationID" => $StationID,
            "TransactionDate" => trim($_POST["transactionDate"]),
            "Shift" => trim($_POST["shift"]),
            "Supervisor" => trim($_POST["superVisor"]),
            "HLCount" => trim($_POST["hlCount"]),
            "VarietyID" => trim($_POST["varietyId"]),
            "HLQuantity" => trim($_POST["hlQty"]),
            "Crates" => trim($_POST["crates"]),
            "WeightAfterVA" => trim($_POST["vaOutQuantity"]),
            "ReturnWeight" => trim($_POST["returnQuantity"]),
            "GradingBreakupID" => trim($_POST["gradingbreakUpid"]),
            "ReceivalBreakupID" => trim($_POST["receivalbreakupid"]),
            "IsPlantFilter" => trim($_POST["IsPlantFilter"]),
            "hasVACrates" => trim($_POST["hasVACrates"]),
            "IsRejInVA" => trim($_POST["IsRejInVA"]),
            "IsVAStationLinked" => trim($_POST["IsVAStationLinked"]),
            "AddvalueAddition" => $AddvalueAddition,
             "Grade" => trim($_POST["Grade"]),
        );
        $result = CallAPI($api, "POST", "", $bodyContentPost);
    } else if ($methodType == "Transfer") {

        $bodyContentPost = array(
            "BreakUpID" => trim($_POST["breakUpID"]),
            "TrackID" => trim($_POST["trackID"]),
            "PlantID" => trim($_POST["arrivalPlant"]),
            "SoakingPlantID" => trim($_POST["soakingPlantID"]),
            "StationID" => trim($_POST["vaStation"]),
            "TransactionDate" => trim($_POST["transactionDate"]),
            "Shift" => trim($_POST["shift"]),
            "Supervisor" => trim($_POST["superVisor"]),
            "HLCount" => trim($_POST["hlCount"]),
            "VarietyID" => trim($_POST["varietyId"]),
            "HLQuantity" => trim($_POST["hlQty"]),
            "Crates" => trim($_POST["crates"]),
            "VAOutQuantity" => trim($_POST["vaOutQuantity"]),
            "GradingBreakupID" => trim($_POST["gradingbreakUpid"]),
            "ReceivalBreakupID" => trim($_POST["receivalbreakupid"]),
            "TransferPlant" => "true",
            "AddvalueAddition" => $AddvalueAddition,
            "Grade" => trim($_POST["Grade"]),
        );

        $result = CallAPI($api, "POST", "", $bodyContentPost);
    } else if ($methodType == "deleteRow") {

        $bodyContent = array(
            "TrackID" => trim($_POST["trackID"]),
            "BreakUpID" => $_POST["breakUpID"],
            "PlantID" => trim($_POST["plantid"]),
            "AddvalueAddition" => $AddvalueAddition
        );

        $result = CallAPI($api, "DELETE", "", $bodyContent);
    } else if ($methodType == "getBalance") {
        $bodyContent = array(
            "GradingBreakUpID" => trim($_POST["gradingBreakUpID"]),
            "TrackID" => trim($_POST["trackID"]),
            "HLCount" => $_POST["hlCount"],
            "AddvalueAddition" => $AddvalueAddition
        );

        $result = CallAPI($api, "GET", "", $bodyContent);
        // echo json_encode($result, JSON_PRETTY_PRINT);
        // exit;
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }

    $error = $result->Error;
    $msg = $result->Msg;
    $ValueAdditionData = end($result->ValueAdditionDetails->ValueAdditionData);
    $List = $result->ValueAdditionDetails->ValueAdditionData;

    $VarietyList = $result->GradingBreakUpDetails->Variety;
    $GradingDataList = $result->GradingBreakUpDetails->GradingData;
    $Grade = $result->GradingBreakUpDetails->Grade;
    $GradingDataTotals = $result->GradingBreakUpDetails->Totals;

    if (!isset($error) || empty($error)) {
        $htmlResponse = '
            <div class="card">
                <div class="card-body">
                    <table id="tblDataTable" class="table table-hover table-bordered">
                        <thead class="thead-light">
                                <tr>
                                    <th class="text-center"><input type="checkbox" id="chkSelectAll"></th>
                                    <th style="width:50px;">S.No</th>
                                    <th>Arrival Code </th>
                                    <th>Production Plant</th>'.(
                                        !empty($ShowDeheadingStationID) ? '<th>VA Station</th>' : ''
                                    ).'<th>Soaking Plant</th>
                                    <!--<th>Center</th>-->
                                    <th>Transaction Date</th>
                                    <th>Shift</th>
                                    <th>Variety </th>
                                    '.(
                                        !empty($EnabelGradeField) ?'<th>Grade</th>':''
                                        ).'
                                    <th>HL Wt(Kgs)</th>
                                    <th>HL Count</th>';
        if ($hasVACrates == 1) {
            $htmlResponse .= '   <th>No of Crates</th>';
        }


        $htmlResponse .= '<th>VA Out Quantity</th>
                            <th>Rejection Weight</th>
                            <th>Yield</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>';
        $i = 1;
        $TotalHLQuantityKgs = 0;
        $TotalWtAftrVA = 0;
        $TotalYield = 0;
        $YieldCount = 0;
        foreach ($List as $item) {
            $TotalHLQuantityKgs = $HLQuantityKgs;
            $TotalWtAftrVA = $WtAftrVA;
            $TotalYield += $item->Yield;
            if ($item->Yield > 0) {
                $YieldCount++;
            }
            $htmlResponse .= '
                <tr>
                    <td class="text-center">
    <input value="' . $item->BreakUpID . '" type="checkbox" class="chkSelectRecList" ' . ($item->disable == 1 ? 'disabled' : '') . '>
</td>
                    <td class="'.$item->VarietyStatus.'">' . $item->SNo . '</td>
                    <td class="'.$item->VarietyStatus.'">' . $item->LotNo . '</td>
                    <td class="tdPlantId  '.$item->VarietyStatus.'" data-plantid="' . $item->PlantID . '">' . $item->Plant . '</td>'.(
                        !empty($ShowDeheadingStationID) ? '<td>' . $item->VAStation . '</td>' : ''
                    ).'<td class="text-danger">' . $item->SoakingPlant . '</td>
                    <!-- <td>$item->Center </td> -->  
                    <td class="tdTransactionDate  '.$item->VarietyStatus.'" data-date="' . $item->TransactionDate . '">' . $item->ProcessingDate . '</td>
                    <td class="tdShiftId  '.$item->VarietyStatus.'" data-shiftid="' . $item->ShiftID . '">' . $item->Shift . '</td>
                    <td class="tdVariety  '.$item->VarietyStatus.'" data-varietyid="' . $item->VarietyID . '">' . $item->Species . ' ' . $item->Variety . '</td>
                    '.(!empty($EnabelGradeField) ?
                        '<td class="tdGrade  '.$item->VarietyStatus.'">' . $item->Grade . '</td>' :
                        ''
                    ).'
                    <td class="tdHLWtKgs  '.$item->VarietyStatus.'">' . $item->HLQuantityKgs . '</td>
                    <td class="tdHLCount  '.$item->VarietyStatus.'">' . $item->HLCount . '</td>';
            if ($hasVACrates == 1) {
                $htmlResponse .= '<td class="tdNoofCrates  '.$item->VarietyStatus.'">' . $item->NoofCrates . '</td>';
            }

            $htmlResponse .= '<td class="tdVAOutQuantity  '.$item->VarietyStatus.'">' . $item->WtAftrVA . '</td>
                    <td class="tdYield  '.$item->VarietyStatus.'">' . $item->ReturnWeight . '</td>
                    <td class="tdYield  '.$item->VarietyStatus.'">' . $item->Yield . '</td>
                    <td>';
            if ($disableVAEdit == 0 || ($disableVAEdit == 1 && $VaStation != '' && ($VaStation == $item->StationID))) {
                if ($item->DeleteAccess == 1 && $item->EditAccess == 1) {

                    $htmlResponse .= '    <button type="button" name="btnEdit" 
                            data-arrivalid="' . $item->ArrivalID . '" 
                            data-breakupid="' . $item->BreakUpID . '" 
                            data-stationid="' . $item->StationID . '" 
                            data-supervisor="' . $item->Supervisor . '" 
                            data-noofcrates="' . $item->NoofCrates . '" 
                            class="btn btn-link p-0 btnEdit"  >
                            <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                        </button>
                        <button type="button" name="btnDelete" data-arrivalid="' . $item->ArrivalID . '" data-breakupid="' . $item->BreakUpID . '" class="btn btn-link p-0 btnDelete"  >
                            <i class="mdi mdi-delete font-size-16 text-danger"></i>
                        </button>';
                }
            }
            $htmlResponse .= '    </td>  
                </tr>';
            $i++;
        }
        $htmlResponse .= '
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td> 
                                <td></td>
                                <td></td>'.(
                                    !empty($ShowDeheadingStationID) ? '<td></td>' : ''
                                ).'<td><span  style="font-weight: 600;font-size: 14px;">Grand Total</span></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                '.(!empty($EnabelGradeField) ?
                                    '<td></td>':''
                                    ).'
                                <td></td>
                                <td id="TotalHLQuantityKgs" style="font-weight: 600;font-size: 14px;">' . number_format($result->ValueAdditionDetails->Totals->TotalHLQuantity, 3) . '</td>';
        if ($hasVACrates == 1) {
            $htmlResponse .= '<td></td>';
        }
        $htmlResponse .= '<td></td>
                                <td id="TotalWtAftrVA" style="font-weight: 600;font-size: 14px;">' . number_format($result->ValueAdditionDetails->Totals->TotalWtAftrVA, 3) . '</td>
                                <td></td> 
                                <td id="TotalYield" style="font-weight: 600;font-size: 14px;">' . number_format($YieldCount > 0 ? $TotalYield / $YieldCount : $TotalYield, 3) . '</td>
                                <td></td>                                
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>';
    }
    $response = array(
        'htmlRes' => $htmlResponse,
        'ValueAdditionData' => $ValueAdditionData,
        'VarietyList' => $VarietyList,
        'GradingDataList' => $GradingDataList,
        'Grade' => $Grade,
        'GradingDataTotals' => $GradingDataTotals,
        "Error" => $error,
        "Msg" => $msg
    );

    header('Content-Type: application/json');
    echo json_encode($response);
} else {

    $api = API_RM_PRODUCTION_TRACK_LIST;

    $bodyContent = array(
        'ArrivalDate' => trim($_POST["arrivalDate"]),
        'ReceivingPlantID' => trim($_POST['plantID']),
        'Supplier' => "",
        'PsId' => "",
        'Type' => trim($_POST["type"]),
        'IsRejInVA' => trim($_POST["isRejInVA"])
    );
    $resList = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resList);
}
