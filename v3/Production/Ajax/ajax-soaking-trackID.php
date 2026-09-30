<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

if (isset($_POST["methodType"]) && !empty($_POST["methodType"])) {
    $methodType = trim($_POST["methodType"]);
} else {
    $methodType = "";
}


function fnSoakingTable($result)
{ 
    $ClientConfigData = (object)$_SESSION['ClientConfigData'];
    $addExtraQty = $ClientConfigData->addExtraQty;  //sriram
    $removePlantCode = $ClientConfigData->removePlantCode;  //sriram
    $ShowBatchCode = $ClientConfigData->ShowBatchCode;
    $error = $result->Error;
    $msg = $result->Msg;
    $SouCountData = $result->SourceCount;
    $BalanceData = $result->BalanceData;
    $resTotals = $result->SoakingDetails->Totals;
    if (isset($result->VaDetails->VaData) && !empty($result->VaDetails->VaData)) {
        $vaData = $result->VaDetails->VaData;
    } else {
        $vaData = null;
    }
    $Grade = $result->VaDetails->Grade;

    if (isset($result->SoakingDetails->SelectedSoakingData) && !empty($result->SoakingDetails->SelectedSoakingData)) {
        $soakingData = end($result->SoakingDetails->SelectedSoakingData);
    } else {
        $soakingData = null;
    }

    if (!isset($error) || empty($error)) {
        $List = $result->SoakingDetails->SoakingData;
        $html = '
        <div class="card">
            <div class="card-body">
                <table id="tblDataTable" class="table table-hover table-bordered">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:50px;">S.No</th>
                            <th>Track Code </th>
                            <th>Plant</th>' .
                            (($ShowBatchCode == 1) ? ('<th>Batch Code</th>') : '')
                            .
                            '<th>Soaking Date</th>
                            <th>Start Time</th>
                            <th>Time Period</th>
                            <th>Chemical</th>
                            <th>Chemical Quantity</th>
                            <th>Salt(KGs)</th>
                            <th>Remarks</th>
                            <th>Variety</th>
                            <th>Type Of Product</th>
                            <th>Treatment</th>
                            <th>Grade</th>
                            <th>Line List</th>
                            <th>Tub Number</th>
                            <th>Source Count</th>
                            <th>PickUp</th>
                            <th>In Count</th>
                            <th>In Quantity</th>'.
                            (($addExtraQty == 1) ? ('<th>Excess Quantity</th>') : '')
                            .'<th>Ice Qty %</th>
                            <th>Water Qty %</th>
                            <th>Shift</th>
                            <th>Soaking In Tempt</th>
                            <th>Soaking Out Tempt</th>
                          <!--  <th>Out Quantity</th>-->
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
        $i = 1;
        $TotalSoakInQty = 0;
        
        foreach ($List as $item) {
            // $TotalSoakInQty =$SoakInQty;
            $TotalSoakInQty = $item->SoakInQty;


            $html .= '
                        <tr>
                            <td style="widtd:50px;">' . $i . '</td>
                            <td>' . $item->TrackCode . ' </td>
                            <td class="tdPlantID" data-plantid="' . $item->PlantID . '">' . $item->Plant . '</td>'.
                              (($ShowBatchCode == 1) ? ('<td class="tdLotNo">' . $item->LotNo . '</td>') : '')
                            .'<td class="tdTransactionDate" data-soakdate="' . date("Y-m-d", strtotime($item->TransactionDate)) . '">' . $item->TransactionDate . '</td>
                            <td class="tdSoakInTime">' . $item->SoakInTime . '</td>
                            <td class="tdTimePeriod">' . $item->TimePeriod . '</td>
                            <td class="tdChemical" data-chemicalid="' . $item->ChemicalID . '">' . $item->ChemicalName . '</td>
                            <td class="tdChemicalQty">' . $item->ChemicalQty . '</td>
                            <td class="tdSaltQty">' . $item->SaltQty . '</td>
                            <td class="tdRemarks">' . $item->Remarks . '</td>
                            <td class="tdVarietyID" data-varietyid="' . $item->VarietyID . '">' . $item->Species . ' ' . $item->Variety . '</td>
                           <td class="tdTypeOFProduct">' . $item->TypeProduct . '</td>
                            <td class="tdSoakingTreatment">' . $item->SoakingTreatment . '</td>
                            <td class="tdGrade">' . $item->VAGrade . '</td>
                            <td class="tdLineList">' . $item->LineNumber . '</td>
                            <td class="tdTubNumber" data-tubnumber="' . $item->TubNumber . '">' . $item->TubCode . '</td>
                            <td class="tdSoakInCount">' . $item->SoakInCount . '</td>
                            <td class="tdPickUp">' . $item->PickUp . '</td>
                             <td class="tdIncount">' . $item->InCounts . '</td>
                            <td class="tdSoakInQty">
                            ' . $item->SoakInQty . '
                            <input type="hidden" id="hdnOutCount" value="' . $item->SoakOutCount . '" />
                            </td>'.
                            (($addExtraQty == 1) ? ('<td class="tdExcessQuantity">' . $item->ExcessQuantity . '</td>') : '')
                            .'<td class="tdIceQtyPercentage">' . $item->IceQtyPercentage . '</td>
                            <td class="tdWaterQtyPercentage">' . $item->WaterQtyPercentage . '</td>
                            <td class="tdShift" data-shiftid="' . $item->ShiftID . '">' . $item->Shift . '</td>
                            <td class="tdSoakInTemperature">' . $item->SoakInTemperature . '</td>
                            <td class="tdSoakOutTemperature">' . $item->SoakOutTemperature . '</td>
                            
                           <!-- <td class="tdSoakOutQty">' . $item->OutQuantity . '</td>-->
                            <td>  ';

            // $html .= '     <button class="mr-3 text-primary btnActions btnEdit" data-breakupid="' . $item->BreakUpID . '" data-arrivalid="' . $item->ArrivalID . '" data-placement="top" title="Edit" data-original-title="Edit">
            //                         <i class="mdi mdi-pencil font-size-16"></i>
            //                     </button>
            //                     <button class="btn btn-link text-danger btnActions btnDelete" data-breakupid="' . $item->BreakUpID . '" data-arrivalid="' . $item->ArrivalID . '" style="position: relative;top: -3px" data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete">
            //                         <i class="mdi mdi-trash-can font-size-16"></i>
            //                     </button>';

                                if ($item->EditAccess != 0) {
                                    $html .= '<button class="mr-3 text-primary btnActions btnEdit" data-breakupid="' . $item->BreakUpID . '" data-arrivalid="' . $item->ArrivalID . '" data-placement="top" title="Edit" data-original-title="Edit">
                                                <i class="mdi mdi-pencil font-size-16"></i>
                                              </button>';
                                }
                                
                                if ($item->DeleteAccess != 0) {
                                    $html .= '<button class="btn btn-link text-danger btnActions btnDelete" data-breakupid="' . $item->BreakUpID . '" data-arrivalid="' . $item->ArrivalID . '" style="position: relative;top: -3px" data-toggle="tooltip" data-placement="top" title="Delete" data-original-title="Delete">
                                                <i class="mdi mdi-trash-can font-size-16"></i>
                                              </button>';
                                }

            $html .= '   </td>
                        </tr>';
            $i++;
        }
        $html .= '
                    </tbody>
                    <tfoot>
                        <tr>
                           <td style="widtd:50px;"></td>
                            <td>Totals</td>
                            <td class="tdPlantID" data-plantid=""></td>'.
                              (($ShowBatchCode == 1) ? ('<td class="tdLotNo"></td>') : '')
                            .'<td class="tdTransactionDate"></td>
                            <td class="tdSoakInTime"></td>
                            <td class="tdTimePeriod"></td>
                            <td class="tdChemical"></td>
                            <td class="tdChemicalQty"></td>
                            <td class="tdSaltQty"></td>
                            <td class="tdRemarks"></td>
                            <td class="tdVarietyID"></td>
                           <td class="tdTypeOFProduct"></td>
                            <td class="tdSoakingTreatment"></td>
                            <td class="tdGrade"></td>
                            <td class="tdLineList"></td>
                            <td class="tdTubNumber"></td>
                            <td class="tdSoakInCount"></td>
                            <td class="tdPickUp"></td>
                            <td class="tdIncount"></td>
                            <td  class="tdSoakInQty1" id="TotalSoakInQty">' . $resTotals->TotalSoakInQty . '
                            </td>'.
                            (($addExtraQty == 1) ? ('<td class="tdExcessQuantity"></td>') : '')
                            .'<td class="tdIceQtyPercentage"></td>
                            <td class="tdWaterQtyPercentage"></td>
                            <td class="tdShift"></td>
                            <td class="tdSoakInTemperature"></td>
                            <td class="tdSoakOutTemperature"></td>

                            <td> </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>';
    }

    $response = array(
        'htmlRes' => $html,
        'soakingData' => $soakingData,
        'vaData' => $vaData,
        'Grade' => $Grade,
        'souCountData' => $SouCountData,
        'balanceData' => $BalanceData,
        "Error" => $error,
        "Msg" => $msg
    );

    return $response;
}

if (isset($methodType) && !empty($methodType)) {

    $api = API_SOAKING_TRACKID_DETAILS;
    
    $ClientConfigData = (object)$_SESSION['ClientConfigData'];
    $AddvalueAddition = $ClientConfigData->AddvalueAddition;  //sriram
    $DoContinueTubNo = $ClientConfigData->DoContinueTubNo;  //sriram

    if ($methodType == "get") {

        $bodyContent = array(
            "TrackID" => trim($_POST["trackID"]),
            "PlantID" => trim($_POST["plantID"]),
            "ArrivalDate" => trim($_POST["arrivalDate"]),
            "AddExtraQty" => trim($_POST["addExtraQty"]),
            "AddvalueAddition" => $AddvalueAddition
        );

        $result = CallAPI($api, "GET", "", $bodyContent);

        header('Content-Type: application/json');
        echo json_encode(fnSoakingTable($result));
    } else if ($methodType == "post") {

        $bodyContentPost = array(
            "SoakingID" => trim($_POST["breakUpID"]),
            "ArrivalID" => trim($_POST["arrivalid"]),
            "ArrivalDate" => trim($_POST["arrivalDate"]),
            "TrackID" => trim($_POST["trackID"]),
            "PlantID" => trim($_POST["plant"]),

            "SoakingDate" => trim($_POST["soakingDate"]),
            "StartTime" => trim($_POST["startTime"]),
            "TimePeriod" => trim($_POST["timePeriod"]),
            "ChemicalID" => trim($_POST["chemical"]),
            "SoakingTreatment" => trim($_POST["soakingTreatment"]),
            "ChemicalQuantity" => trim($_POST["chemicalQuantity"]),
            "SaltKg" => trim($_POST["saltKg"]),
            "VarietyID" => trim($_POST["variety"]),
            "VAGrade" => trim($_POST["grade"]),
            "LineNumber" => trim($_POST["lineList"]),
            "TubNumber" => trim($_POST["tubNumber"]),
            "InCount" => trim($_POST["inCount"]),
            "InQuantity" => trim($_POST["inQuantity"]),
            "OutCount" => trim($_POST["outCount"]),
            // "OutQuantity" => trim($_POST["outQty"]),
             "InCounts" => trim($_POST["inCounts"]),

            "IceQtyPercentage" => trim($_POST["txtIceQty"]),
            "WaterQtyPercentage" => trim($_POST["txtWaterQty"]),
            "Shift" => trim($_POST["drpShift"]),
            "SoakInTemperature" => trim($_POST["txtSoakingInTemp"]),
            "SoakOutTemperature" => trim($_POST["txtSoakingOutTemp"]),
            "ExcessQuantity" => trim($_POST["ExcessQuantity"]),

            "Remarks" => trim($_POST["remarks"]),
            "VABreakupID" => trim($_POST["vabreakUpid"]),
            "ReceivalBreakupID" => trim($_POST["receivalbreakupid"]),
            "IsPlantFilter" => trim($_POST["IsPlantFilter"]),
            "VLID" => trim($_POST["vlid"]),
            "AddExtraQty" => trim($_POST["addExtraQty"]),
            "AddvalueAddition" => $AddvalueAddition,
            "DoContinueTubNo" => $DoContinueTubNo,
            
        );

        $result = CallAPI($api, "POST", "", $bodyContentPost);

        header('Content-Type: application/json');
        echo json_encode(fnSoakingTable($result));
    } else if ($methodType == "getSouCount") {

        $bodyContent = array(
            "ArrivalID" => trim($_POST["arrivalid"]),
            "TrackID" => trim($_POST["trackID"]),
            "VABreakUpID" => trim($_POST["vabreakUpid"]),
            "PlantID" => trim($_POST["plantID"]),
            "MaterialType" => trim($_POST["materialType"]),
            "AddExtraQty" => trim($_POST["addExtraQty"]),
            "AddvalueAddition" => $AddvalueAddition
        );

        $result = CallAPI($api, "GET", "", $bodyContent);

        header('Content-Type: application/json');
        echo json_encode(fnSoakingTable($result));
    } else if ($methodType == "delete") {

        $bodyContent = array(
            "ArrivalID" => trim($_POST["arrivalid"]),
            "TrackID" => trim($_POST["trackid"]),
            "BreakUpID" => $_POST["breakupid"],
            "PlantID" => trim($_POST["plantID"]),
            "AddExtraQty" => trim($_POST["addExtraQty"]),
            "AddvalueAddition" => $AddvalueAddition
        );

        $result = CallAPI($api, "DELETE", "", $bodyContent);

        header('Content-Type: application/json');
        echo json_encode(fnSoakingTable($result));
    } else if ($methodType == "getAutoTub") {
         $bodyContent = array(
            "PlantID" => trim($_POST["plantID"]),
            "ArrivalDate" => trim($_POST["arrivalDate"]),
             "TrackID" => trim($_POST["trackId"]),
            "type" => "getAutoTub",
            "IsPlantFilter"=> trim($_POST["IsPlantFilter"])
        );

        $result = CallAPI($api, "GET", "", $bodyContent);
        header('Content-Type: application/json');
        echo json_encode(($result));
    }
} else {
    $ClientConfigData = (object)$_SESSION['ClientConfigData'];
    $AddvalueAddition = $ClientConfigData->AddvalueAddition;  //sriram
    $api = API_RM_PRODUCTION_TRACK_LIST;

    $bodyContent = array(
        "ArrivalDate" => trim($_POST["arrivalDate"]),
        "Type" => trim($_POST["type"]),
        "ReceivingPlantID" => trim($_POST["plantID"]),
        "AddvalueAddition" => $AddvalueAddition,
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resList);
}
