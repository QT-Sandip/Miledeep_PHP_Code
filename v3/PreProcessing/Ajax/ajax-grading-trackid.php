<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$methodType = trim($_POST["methodType"]);
$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$IsShowAddValueAddtion = $ArrayClientConfigData['AddvalueAddition'];
$mergeHonLoose = $ArrayClientConfigData['mergeHonLoose'];
$ShowBatchCode = $ArrayClientConfigData['ShowBatchCode'];

function fnGradingTable($result)
{
    global $ShowBatchCode;
    $ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
    $IsShowAddValueAddtion = $ArrayClientConfigData['AddvalueAddition'];
    $Species = trim($_POST["trackSpecies"]);

    $resList = $result->GradingBreakUpDetails->GradingData;
    $resVariety = $result->GradingBreakUpDetails->Variety;
    $resProductionGrades = $result->GradingBreakUpDetails->ProductionGrades;
    $resTreatment = $result->GradingBreakUpDetails->Treatment;
    $resTotals = $result->GradingBreakUpDetails->Totals;
    $resGradingDetails = $result->GradingDetails;
    $resGrandTotals = $result->GradingBreakUpDetails->GrandTotals;

    $BillDetails = !empty($result->GradingBreakUpDetails->BillDetails) ? $result->GradingBreakUpDetails->BillDetails : '';

    $error = $result->Error;
    $msg = $result->Msg;

    $html = '
    <div class="card">
        <div class="card-body">
            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>SNO</th>'.(
                            $ShowBatchCode == 1 ?
                            '<th>Batch No / HON Count</th>' :''
                        ).
                        '<th>Variety</th>
                        <th>HL_Count (Per Lb)</th>
                        <th>Grade</th>
                        <th>Treatment</th>
                        <th>Freezing</th>
                        <th style="min-width: 150px;">No Of Crates</th>
                        <th style="min-width: 150px;">Grading Qty</th>
                       <!-- <th style="min-width: 90px;">Remove Crate</th>-->
                        <th style="min-width: 150px;">HL/Tub Weight</th>
                        <!--<th style="min-width: 90px;">Add Crate</th>-->
                        <th style="min-width: 90px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="tblBody">';
    $i = 1;
    $length = count($resList);
    foreach ($resList as $list) {

        if ($list->DeleteAccess == 0) {
            $IsDisable = "disabled";
        } else {
            $IsDisable = "";
        }
        $html .= '<tr>
                        <td>' . $i . '</td>'.(
                            $ShowBatchCode == 1 ? 
                                 ($i == 1 ) ?
                                '<td rowspan="'.$length.'" style="min-width: 100px;">'.$BillDetails.'</td>'
                                : '<td style="display:none"></td>'
                             : '').
                        '<td style="min-width: 100px;">
                            <select class="drpSelect form-control drpVariety" ' . $IsDisable . ' >
                                <option value=""> Select</option>';
        foreach ($resVariety as $Variety) {

            if ($Variety->VarietyID == $list->Variety) {
                $select = "selected";
            } else {
                $select = "";
            }

            $html .= '<option value="' . $Variety->VarietyID . '" ' . $select . '>' . $Variety->Species . '-' . $Variety->ShortName . '</option> ';
        }
        $html .= '</select>
                        </td>
                        <td><input type="text" class="form-control txthlCount" id="txthlCount" name="txthlCount" placeholder="Enter HL Count" value="' . $list->HLCount . '" ' . $IsDisable . ' /></td>
                        <td style="min-width: 100px;">
                            <select class="drpSelect form-control drpGrade" ' . $IsDisable . '>
                                <option value=""> Select</option>';
        foreach ($resProductionGrades as $ProductionGrades) {
            if ($ProductionGrades->Grade == $list->Grade) {
                $select = "selected";
            } else {
                $select = "";
            }
            $html .= '<option value="' . $ProductionGrades->Grade . '" ' . $select . '>' . $ProductionGrades->Grade . '</option> ';
        }



        $html .= '</select>
                        </td>
                        <td style="min-width: 100px;">
                            <select class="drpSelect form-control drpTreatment"  id="drpTreatment' . $i . '" ' . $IsDisable . '>
                                <option value=""> Select</option>';
        foreach ($resTreatment as $Treatment) {
            if ($Treatment->ShortCode == $list->Treatment) {
                $select = "selected";
            } else {
                $select = "";
            }
            $html .= '<option value="' . $Treatment->ShortCode . '" ' . $select . '>' . $Treatment->ShortCode . '</option> ';
        }

        $html .= '</select>
                        </td>
                        <td style="min-width: 100px;">
                            <select class="drpSelect form-control drpIsBlock" ' . $IsDisable . ' >
                                <option value=""> Select</option>';
        if ($list->IsBlock == 0) {
            $selectNo = "selected";
        } else {
            $selectNo = "";
        }

        $html .= '<option value="0" ' . $selectNo . '>IQF</option> ';
        if ($list->IsBlock == 1) {
            $selectYes = "selected";
        } else {
            $selectYes = "";
        }

        $html .= '<option value="1" ' . $selectYes . '>BLOCK</option> ';
        $html .= '</select>
                        </td>
                        <td style="min-width: 100px;"><input type="text" class="form-control txtNoOfCrates" id="txtNoOfCrates" name="txtNoOfCrates" placeholder="Enter No Crates" value="' . $list->NoofCrates . '" readonly /></td>
                        <td><input type="text" class="form-control txtGradingQty" id="txtGradingQty" name="txtGradingQty" placeholder="Enter Grading Qty" value=" ' . $list->GradingQty . '" readonly /></td>
                        <!--<td class="text-center">
                            <button type="button" class="mr-3 text-danger btnActions btnRemove" data-brkup-id="' . $list->GradingBreakUpID . ' " ' . $IsDisable . '><i class="fa fa-minus fa-lg"></i></button>
                        </td>-->
                        <!--<td style="min-width: 100px;"><input required type="text" class="form-control txtCrateQty" id="txtCrateQty" name="txtCrateQty" placeholder="Enter Crate Qty" value="' . $list->CrateQuantity . '"/></td>-->
                        
                        <td style="min-width: 100px;"><input required type="text" class="form-control txtCrateQty" id="txtCrateQty" name="txtCrateQty" 
                        placeholder="Enter Crate Qty" value=""/></td>
                        
                        <!--<td class="text-center">
                            <button type="button" class="mr-3 text-success btnActions btnAdd" data-brkup-id="' . $list->GradingBreakUpID . '" ><i class="fa fa-plus"></i></button>
                        </td>  -->';
        if ($list->DeleteAccess != 0) {
            $html .= '<td class="text-center">
                        <button type="button" class="mr-3 text-success btnActions btnAdd" data-brkup-id="' . $list->GradingBreakUpID . '" title="Save"><i class="fa fa-save"></i></button>
                        <button type="button" class="mr-3 text-danger btnActions btnDelete" id="btnDelete" data-breakup-id="' . $list->GradingBreakUpID . '" ' . $IsDisable . ' ><i class="mdi mdi-trash-can font-size-16"></i></button>'.(($IsShowAddValueAddtion ==1 )?'                        
                        <button type="button" class="mr-3 text-success btnActions" onClick="valAdd(' . $list->GradingBreakUpID . ','. $list->valueAddition . ')" data-brkup-id="vad' . $list->GradingBreakUpID . '" title="Add"><i class="fa fa-plus"></i></button>':'').'
                    </td>';
        } 
        else {
            // $html .= '<td class="text-center">'.(($IsShowAddValueAddtion==1 && $list->IsBlock == 1 && $list->Treatment == 'UNT' )?'                        
            //             <button type="button" class="mr-3 text-success btnActions" onClick="valAdd(' . $list->GradingBreakUpID . ')" data-brkup-id="vad' . $list->GradingBreakUpID . '" title="Add"><i class="fa fa-plus"></i></button>':'').'</td>';
            $html .= '<td></td>';
        }
        $html .= '</tr>';
        $i++;
    }
    $html .= '</tbody>
                <tfoot>';
    $html .= '<tr>
                            <th>TotalCrates: ' . $resTotals->TotalCrates . '</th>
                            <th>TotalWeight: ' . $resTotals->TotalWeight . '</th>
                            <th>Total HON Qty: ' . number_format($resGrandTotals->TotalHON, 3) . '</th>                          
                            <th>Total HL Qty: ' . $resGrandTotals->TotalHL . '</th>
                            <th>Yield: ' . $resGrandTotals->Yield . '</th>
                            <th style="display: none"></th>
                            <th style="display: none"></th>
                            <th style="display: none"></th>
                            <th style="display: none"></th>
                            <th style="display: none"></th>
                        </tr>';


    $html .= '</tfoot>
            </table>
            <input type="hidden" id="BillDetails" value="'.$BillDetails.'" data-total_rows="'.($i -1) .'">
        </div>
    </div>';

    $response = array('htmlRes' => $html, 'FormData' => $resGradingDetails, 'Error' => $error, 'Msg' => $msg);

    return $response;
}

if( isset($_POST['type']) && $_POST['type'] == "SAVE_VALUE_ADDITION" ){

    $dataID = $_POST['id'];
    $oldGradingData = legacyAuditFetchRowById('PpcHLGradingBreakup', 'DataID', $dataID, 'PreProcessing');
    $sqlUpdate = "UPDATE `PpcHLGradingBreakup` SET `valueAddition`='".$_POST['value']."' where DataID='$dataID'";
    $iconn->query($sqlUpdate);
    legacyAuditLogUpdateById('PpcHLGradingBreakup', 'DataID', $dataID, $oldGradingData, $_POST, [
        'ReferenceNo' => $dataID,
        'Remarks' => 'Value addition saved for grading data',
    ], 'PreProcessing');
    echo "Success";
    exit;
}

if (isset($methodType) && !empty($methodType)) {

    $api = API_GRADING_TRACK_DETAILS;

    if ($methodType == "getReceivingCount") {

        $bodyContent = array(
            "ArrivalDate" => trim($_POST["arrivalDate"]),
            "TrackID" => trim($_POST["trackID"]),
            "IsHONPacking" => trim($_POST["IsHONPacking"]),
            "mergeHonLoose" => $mergeHonLoose,
        );

        // echo json_encode($bodyContent, JSON_PRETTY_PRINT);

        $result = CallAPI($api, "GET", "", $bodyContent);
       // echo '<pre>';print_r($result);die;
        // echo json_encode($result, JSON_PRETTY_PRINT);exit;


        if (!isset($error) || empty($error)) {

            $list .= '<table class="popupReceivingCountTable table table-hover table-bordered no-footer dataTable">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center"> <!--<input type="checkbox" id="chkSelectAll">--></th><br>
                                <th>Receiving Count List</th>
                            </tr>
                        <thead>
                        <tbody>';
            $i = 1;
            foreach ($result->GradingDetails->ReceivingCountDeatils as $item) {
                if($item->BillNumber){

                $count = count(explode(",", $item->BillNumber));
                $GradingBreakUpStatus = $item->GradingBreakUpStatus;
                if ($count != 1) {
                    $style = "color: #0d61ff";
                } else {
                    $style = "";
                }
                if($GradingBreakUpStatus > 0){
                    $style = "color: #ff0d4e";
                }
                $list .= '<tr>
                            <td class="text-center"><input value="' . $item->ArrivalID . '" id="' . $i . '" data-gradingstatus="' . $item->GradingStatus . '" data-receivalbreakupid="' . $item->ReceivalBreakupID . '" data-billnumber="' . $item->BillNumber . '" data-overaldeleteaccess="' . $item->OverallDeleteAccess . '" type="checkbox" class="chkSelectRecList" ></td>
                            <td  style="' . $style . '" data-list="' . $count . '" ><label for="' . $i . '">' . $item->BillNumber . '</label></td>
                        </tr>';

                }
                        $i++;
            }
            $list .= '</tbody></table>';

            header('Content-Type: application/json');
            $response = array('ResvCountDtsHTML' => $list, 'Error' => $error, 'Msg' => $msg);
            echo json_encode($response);
        }
    } else if ($methodType == "getList") {
        $bodyContent = array(
            "ArrivalDate" => trim($_POST["arrivalDate"]),
            "TrackID" => trim($_POST["trackID"]),
            "ArrivalID" => trim($_POST["receivingCount"]),
            "ReceivalBreakupID" => trim($_POST["receivalBreakupId"]),
            "IsHONPacking" => trim($_POST["IsHONPacking"]),
            "IsShowAddValueAddtion" => trim($IsShowAddValueAddtion),
            "mergeHonLoose" => $mergeHonLoose,
            "ShowBatchCode" => $ShowBatchCode,
        );

        $result = CallAPI($api, "GET", "", $bodyContent);

        if (!isset($error) || empty($error)) {

            header('Content-Type: application/json');
            echo json_encode(fnGradingTable($result)); // 
        }
    } else if ($methodType == "postForm") { //

        $bodyContent = array(
            "ArrivalDate" => trim($_POST["arrivalDate"]),
            "ArrivalID" => trim($_POST["arrivalID"]),
            "ReceivalBreakupID" => trim($_POST["receivalBreakupID"]),
            "GradingID" => trim($_POST["gradingID"]),
            "TrackID" => trim($_POST["trackID"]),
            "MixIds" => trim($_POST["arrivalID"]),
            "Plant" => trim($_POST["productionPlant"]),
            "Center" => trim($_POST["center"]),
            "TransactionDate" => trim($_POST["transactionDate"]),
            "MachineID" => trim($_POST["gradingMachine"]),
            "StartTime" => trim($_POST["startTime"]),
            "EndTime" => trim($_POST["endTime"]),
            "BreakTime" => trim($_POST["breakTime"]),
            "Remarks" => trim($_POST["remarks"]),
            "Type" => "",
            "IsPlantFilter" => trim($_POST["IsPlantFilter"]),
            "IsHONPacking" => trim($_POST["IsHONPacking"]),
            "GradingSupervisorID" => trim($_POST["drpSuperVisor"]),
            "IsShowAddValueAddtion" => trim($IsShowAddValueAddtion),
            "mergeHonLoose" => $mergeHonLoose,
        );

        $result = CallAPI($api, "POST", "", $bodyContent);

        header('Content-Type: application/json');
        echo json_encode(fnGradingTable($result)); //
    } else if ($methodType == "AddNewLine") { //

        $bodyContent = array(
            "TrackID" => trim($_POST["trackID"]),
            "ArrivalID" => trim($_POST["arrivalID"]),
            "GradingID" => trim($_POST["gradingID"]),
            "ReceivalBreakupID" => trim($_POST["receivalBreakupID"]),
            "MixIds" => trim($_POST["arrivalID"]), // trim($_POST["MixIds"]),
            "Type" => "BreakUpData",
            "IsHONPacking" => trim($_POST["IsHONPacking"]),
            "IsShowAddValueAddtion" => trim($IsShowAddValueAddtion),
            "mergeHonLoose" => $mergeHonLoose,
        );
        $result = CallAPI($api, "POST", "", $bodyContent);
        header('Content-Type: application/json');
        echo json_encode(fnGradingTable($result)); //
    } else if ($methodType == "AddCrate") { //

        $bodyContent = array(
            "ArrivalID" => trim($_POST["arrivalID"]),
            "TrackID" => trim($_POST["trackID"]),
            "BreakUpID" => trim($_POST["dataBrkupID"]),
            "VarietyID" => trim($_POST["variety"]),
            "HLCount" => trim($_POST["hlCount"]),
            "GradeID" => trim($_POST["prodGrade"]),
            "Treatment" => trim($_POST["treatment"]),
            "IsBlock" => trim($_POST["isBlock"]),
            "CrateQty" => trim($_POST["crateQty"]),
            "ActionType" => "AddCrate",
            "Type" => "BreakUpData",
            "GradingQty" => trim($_POST["gradingQty"]),
            "NoCrates" => trim($_POST["noCrates"]),
            "GradingID" => trim($_POST["gradingID"]),
            "ReceivalBreakupID" => trim($_POST["receivalBreakupID"]),
            "MixIds" => trim($_POST["arrivalID"]),
            "IsHONPacking" => trim($_POST["IsHONPacking"]),
            "IsShowAddValueAddtion" => trim($IsShowAddValueAddtion),
            "mergeHonLoose" => $mergeHonLoose,
        );
        $result = CallAPI($api, "POST", "", $bodyContent);
        header('Content-Type: application/json');
        echo json_encode(fnGradingTable($result)); // 
    } else if ($methodType == "RemoveCrate") { //

        $bodyContent = array(
            "ArrivalID" => trim($_POST["arrivalID"]),
            "TrackID" => trim($_POST["trackID"]),
            "BreakUpID" => trim($_POST["dataBrkupID"]),
            "VarietyID" => trim($_POST["variety"]),
            "HLCount" => trim($_POST["hlCount"]),
            "GradeID" => trim($_POST["prodGrade"]),
            "Treatment" => trim($_POST["treatment"]),
            "IsBlock" => trim($_POST["isBlock"]),
            "CrateQty" => trim($_POST["crateQty"]),
            "ActionType" => "RemoveCrate",
            "Type" => "BreakUpData",
            "GradingQty" => trim($_POST["gradingQty"]),
            "NoCrates" => trim($_POST["noCrates"]),
            "GradingID" => trim($_POST["gradingID"]),
            "ReceivalBreakupID" => trim($_POST["receivalBreakupID"]),
            "MixIds" => trim($_POST["arrivalID"]), // trim($_POST["MixIds"]),
            "IsHONPacking" => trim($_POST["IsHONPacking"]),
            "IsShowAddValueAddtion" => trim($IsShowAddValueAddtion),
            "mergeHonLoose" => $mergeHonLoose,
        );

        $result = CallAPI($api, "POST", "", $bodyContent);
        header('Content-Type: application/json');
        echo json_encode(fnGradingTable($result)); // 
    } else if ($methodType == "deleteRow") { //

        $bodyContent = array(
            "TrackID" => trim($_POST["trackID"]),
            "DataBreakUpID" => trim($_POST["breakUpID"]),
            "ArrivalID" => trim($_POST["arrivalID"]),
            "ReceivalBreakupID" => trim($_POST["receivalBreakupID"]),
            "MixIds" => trim($_POST["arrivalID"]),
            "IsHONPacking" => trim($_POST["IsHONPacking"]),
            "IsShowAddValueAddtion" => trim($IsShowAddValueAddtion),
            "mergeHonLoose" => $mergeHonLoose,

        );

        $result = CallAPI($api, "DELETE", "", $bodyContent);
        header('Content-Type: application/json');
        echo json_encode(fnGradingTable($result)); // 
    } else if ($methodType == "deleteMixedIds") { //

        $bodyContent = array(
            "TrackID" => trim($_POST["trackID"]),
            "DataBreakUpID" => trim($_POST["breakUpID"]),
            "ArrivalID" => trim($_POST["arrivalID"]),
            "ReceivalBreakupID" => trim($_POST["receivalBreakupID"]),
            "MixIds" => trim($_POST["arrivalID"]),
            "Type" => "DeleteTotalGradingDetails",
            "IsHONPacking" => trim($_POST["IsHONPacking"]),
            "IsShowAddValueAddtion" => trim($IsShowAddValueAddtion),
            "mergeHonLoose" => $mergeHonLoose,
        );

        $result = CallAPI($api, "DELETE", "", $bodyContent);
        header('Content-Type: application/json');
        echo json_encode(fnGradingTable($result)); //
    }
} else {

    $api = API_RM_PRODUCTION_TRACK_LIST;

    $bodyContent = array(
        "ArrivalDate" => trim($_POST["arrivalDate"]),
        'ReceivingPlantID' => trim($_POST['plantID']),
        "Type" => trim($_POST["type"]),
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resList);
}