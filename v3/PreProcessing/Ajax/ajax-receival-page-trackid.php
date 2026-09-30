<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);
$Type = trim($_POST["Type"]);
$IsVehicleTrack = trim($_POST["IsVehicleTrack"]);

$ClientConfigData = $_SESSION['ClientConfigData'];

// $IsVehicleTrack = $ClientConfigData->IsVehicleTrack;
// $IsHONPacking = $ClientConfigData->IsHONPacking;//sriram

$IsVehicleTrack = $ClientConfigData['IsVehicleTrack'];
$IsHONPacking = $ClientConfigData['IsHONPacking']; // sriram
$mergeHonLoose = $ClientConfigData['mergeHonLoose']; // sriram

$_POST["IsVehicleTrack"] = $IsVehicleTrack;


function fnDataTableList($result)
{

    $resList = $result->Data->RmReceivingBreakUp;
    $resRmReceivingDetails = $result->Data->RmReceivingDetails;
    $resTotal = $result->Data->Totals;
    $error = $result->Error;
    $msg = $result->Msg;
    $IsHONPacking = $result->Data->IsHONPacking;
    $deleteAccess = $result->Data->deleteAccess;

    $html =
        '<div class="card">
        <div class="card-body">
        <table id="tblDataTable" class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th>S.No</th>
                <th style="min-width: 50px">Bill No</th>
                <th>Variety</th>
                <th>Count</th>
                <th>Head on Weight</th>' .
        (($IsHONPacking == 1) ? '<th>HONPacking</th>' : '') .
        '<th>Recv Count</th>
                <th>Recv Weight</th>
                <th>Sample HO Qty</th>
                <th>No Of Pcs</th>
                 <th style="min-width: 80px">Count Diff</th>
                <th style="min-width: 80px">Qty Diff</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>';
    $TotalWeight = 0;
    $TotalSampQty = 0;
    $TotalRecvQty = 0;
    $QtyDiff = 0;
    foreach ($resList as $item) {
        $TotalWeight += isset($item->ArrivalWeight) ? floatval($item->ArrivalWeight) : 0;
        $TotalSampQty += isset($item->ReceivingCount) ? floatval($item->ReceivingCount) : 0;
        $TotalRecvQty += isset($item->ReceivingQty) ? floatval($item->ReceivingQty) : 0;
        $QtyDiff += isset($item->QuantityDifference) ? floatval($item->QuantityDifference) : 0;



        if ($item->CountDifference > 0) {
            $classCountDifference = "text-danger fw-bolder";
        } else if ($item->CountDifference == 0) {
            $classCountDifference = "";
        } else {
            $classCountDifference = "text-success fw-bolder";
        }

        if ($item->QuantityDifference > 0) {
            $classQuantityDifference = "text-success fw-bolder";
        } else if ($item->QuantityDifference == 0) {
            $classQuantityDifference = "";
        } else {
            $classQuantityDifference = "text-danger fw-bolder";
        }

        $html .=
            '<tr>
                    <td> ' . $item->SNo . '</td>
                    <td> ' . $item->ArrivalNumber . '</td>
                    <td> ' . $item->OverallVariety . '</td>
                    <td><input type="text"  class="form-control txtCount" id="txtCount" name="txtCount" readonly value="' . $item->ArrivalCount . '" /></td>
                    <td><input type="text"  class="form-control txtWeight" id="txtWeight" name="txtWeight" readonly value="' . $item->ArrivalWeight . '" /></td> ' .
            (($IsHONPacking == 1) ?
                '<td>
                    <select class="drpSelect form-control drpHONPacking" id="drpHONPacking" name="drpHONPacking">
                    <option value="0" ' . ($item->HONPacking == 0 ? "selected" : "") . '>No</option>
                    <option value="1" ' . ($item->HONPacking == 1 ? "selected" : "") . '>Yes</option>
                    </select>
                    </td>' : '') .
            '<td><input type="number" min="0" step="0.01" class="form-control txtRecvCount" id="txtRecvCount" name="txtRecvCount" value="' . $item->ReceivingCount . '" /></td>
                    <td><input type="number" min="0" step="0.001" class="form-control txtRecvQty" id="txtRecvQty" name="txtRecvQty" value="' . $item->ReceivingQty . '" /></td>
                                        <td><input type="number" min="0" step="0.001" class="form-control txtSampleQty" id="txtSampleQty" name="txtSampleQty" value="' . $item->SampleQuantity . '" /></td>
                    <td><input type="number" min="0" step="0.01" class="form-control txtNoOfPcs" id="txtNoOfPcs" name="txtNoOfPcs" value="' . $item->NoofPieces . '" /></td>
                    <td class="tdCountDifference text-center ' . $classCountDifference . '"> ' . $item->CountDifference . '</td>
                    <td class="tdQuantityDifference text-center ' . $classQuantityDifference . '">' . $item->QuantityDifference . '</td>
                    <td>';

        if (isset($resRmReceivingDetails->ReceivingID) && !empty($resRmReceivingDetails->ReceivingID)) {
            $display = "show";
        } else {
            $display = "hide";
        }

        if (empty($item->ReceivalBreakupID)) {
            $html .=
                '<button class="mr-3 text-primary btnActions btnSaveRow" data-placement="top" title="Add Breakup" 
                                style="' . ($display == "show" ? "" : "display: none") . '"
                                data-arrival-id="' . $item->ArrivalID . '" 
                                data-arr-breakup-id="' . $item->ArrivalBreakupID . '" 
                                data-original-title="Edit">
                                <i class="btn-success fa fa-save"></i> <!-- plus -->
                            </button>';
        } else if (isset($item->ReceivalBreakupID) && !empty($item->ReceivalBreakupID) && $item->EditAccess == 1 && $item->DeleteAccess == 1) {
            $html .=
                '<button class="mr-3 text-primary  btnActions btnUpdateRow" data-placement="top" title="Update Breakup" 
                                style="' . ($display == "show" ? "" : "display: none") . '"
                                data-receival-breakupid="' . $item->ReceivalBreakupID . '" 
                                data-arrival-id="' . $item->ArrivalID . '" 
                                data-arr-breakup-id="' . $item->ArrivalBreakupID . '" 
                                data-original-title="Edit">
                                <i class="btn-success fa fa-save"></i> <!-- pencil -->
                            </button>';

            if ($deleteAccess == 1) {
                $html .=
                    '<button class="btn btn-link text-danger btnActions btnDelete" style="position: relative;top: -3px;' . ($display == "show" ? "" : "display: none") . '" data-breakup-id="' . $item->ReceivalBreakupID . '"  
                                            data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete">
                                                <i class="mdi mdi-trash-can font-size-16"></i></button>';
            }
            if ($resRmReceivingDetails->CratePageAccess == 1) {
                $Q = "LotID=$item->LotID&ArrivalID=$item->ArrivalID&saveType=save";
                $html .=
                    '<a class="mr-3 text-success btnActions" data-placement="top" title="Add Crates" href="crates-creation?Q=' . base64_encode($Q) . '" target="_blank"
                                                    style="' . ($display == "show") . '" data-original-title="Add Crates">  <i class="fa fa-plus btn-plus"></i>
                                                </a>';
            }
        } else {
            if ($resRmReceivingDetails->CratePageAccess == 1) {
            $Q = "LotID=$item->LotID&ArrivalID=$item->ArrivalID&saveType=view";
            $html .=
                    '<a class="mr-3 text-success btnActions" data-placement="top" title="View Crates" href="crates-creation?Q='.base64_encode($Q) . '" target="_blank"
                                                    style="' . ($display == "show") . '" data-original-title="View Crates">  <i class="fa fa-eye btn-eye"></i>
                                                </a>';
            }
        }
        $html .=
            '</td>
                </tr>';
    }
    $html .= '
        </tbody>
        <tfoot>';
    foreach ($resTotal as $total) {
        $html .= ' <tr>

                 <td></td>
                <td><b>Totals</b></td>
                <td></td>
                <td></td>
                <td id="TotalWeight"><b>' . number_format($TotalWeight, 3) . '</b></td>' .
            (($IsHONPacking == 1) ? '<td></td>' : '') .
            '<td id="TotalSampQty"><b>' . number_format($TotalSampQty, 3) . '</b></td>
                <td id="TotalRecvQty"><b>' . number_format($TotalRecvQty, 3) . '</b></td>
                <td></td>
                <td></td>
                <td></td>
                <td id="QtyDiff"><b>' . number_format($QtyDiff, 3) . '</b></td>
                <td></td>
                
            </tr>';
    }
    $html .= '</tfoot>
    </table>
    </div>
    </div>';
    $response = array('htmlRes' => $html, 'FormData' => $resRmReceivingDetails,'LotID' => $result->LotId, 'ArrivalID' =>$result->ArrivalID, 'Error' => $error, 'Msg' => $msg);

    return $response;
}

if (isset($methodType) && !empty($methodType)) {

    $api = API_RECEIVAL_TRACK_LIST;

    if ($methodType == "getPlantData") {
        $bodyContent = array(
            "ArrivalDate" => trim($_POST['arrivalDate']),
            "VehicleID" => trim($_POST['vehicleID']),
            "IsVehicleTrack" => trim($_POST["IsVehicleTrack"]),
            "IsHONPacking" => trim($_POST["IsHONPacking"]), // sriram
            "RemovePlantCode" => trim($_POST["removePlantCode"]), // sriram
            "MergeHonLoose" => $mergeHonLoose, // sriram
        );
        $result = CallAPI($api, "GET", "", $bodyContent);


        if (!isset($error) || empty($error)) {

            header('Content-Type: application/json');
            $response = array('PlantDetails' => $result->Data->RmReceivingDetails->ReceivingPlantID, 'Error' => $error, 'Msg' => $msg);

            echo json_encode($response);
        }
    } else if ($methodType == "getFormData") {

        $bodyContent = array(
            "VehicleID" => trim($_POST["vehicleID"]),
            "ArrivalDate" => trim($_POST["arrivalDate"]),
            "ReceivingPlantID" => trim($_POST["receivalPlant"]),
            "ReceivingID" => trim($_POST["receivingID"]),
            "IsVehicleTrack" => trim($_POST["IsVehicleTrack"]),
            "IsPlantFilter" => trim($_POST["IsPlantFilter"]), // sriram
            "IsHONPacking" => trim($_POST["IsHONPacking"]), // sriram
            "RemovePlantCode" => trim($_POST["removePlantCode"]), // sriram
            "MergeHonLoose" => $mergeHonLoose, // sriram
        );

        $result = CallAPI($api, "GET", "", $bodyContent);
        if (!isset($error) || empty($error)) {

            header('Content-Type: application/json');
            echo json_encode(fnDataTableList($result));
        }
    } else if ($methodType == "FormPost") {

        $bodyContent = array(
            "ArrivalDate" => trim($_POST["arrivalDate"]),
            "VehicleID" => trim($_POST["vehicleId"]),
            "VehicleNumber" => trim($_POST["vehicleNumber"]),
            "ReceivingPlantID" => trim($_POST["arrivalPlantId"]),
            "SealNo" => trim($_POST["sealNumber"]),
            "SupervisorID" => trim($_POST["supervisorId"]),
            "Remarks" => trim($_POST["remarks"]),
            "IsVehicleTrack" => trim($_POST["IsVehicleTrack"]),
            "IsPlantFilter" => trim($_POST["IsPlantFilter"]), // sriram
            "RemovePlantCode" => trim($_POST["removePlantCode"]), // sriram
            "MergeHonLoose" => $mergeHonLoose, // sriram
            "BreakUpdate" => false,
        );
        $result = CallAPI($api, "POST", "", $bodyContent);
        header('Content-Type: application/json');
        echo json_encode($result);
    } else if ($methodType == "TableSave") {
        if ($IsHONPacking == 1) {
            $bodyContent = array(
                "ReceivingID" => trim($_POST["trackId"]),
                "ArrivalID" => trim($_POST["arrivalID"]),
                "ArrivalBreakupID" => trim($_POST["arrivalBreakupID"]),
                "ArrivalDate" => trim($_POST["arrivalDate"]),
                "VehicleID" => trim($_POST["vehicleID"]),
                "SampleQuantity" => trim($_POST["sampleQuantity"]),
                "NoofPieces" => trim($_POST["noofPieces"]),
                "ReceivingCount" => trim($_POST["receivingCount"]),
                "ReceivingQty" => trim($_POST["receivingQty"]),
                "ReceivingPlantID" => trim($_POST["arrivalPlantId"]),
                "IsVehicleTrack" => trim($_POST["IsVehicleTrack"]),
                "IsHONPacking" => $IsHONPacking,
                "HONPacking" => trim($_POST["HONPacking"]),
                "RemovePlantCode" => trim($_POST["removePlantCode"]), // sriram
                "MergeHonLoose" => $mergeHonLoose, // sriram
                "BreakUpdate" => true,
            );
        } else {
            $bodyContent = array(
                "ReceivingID" => trim($_POST["trackId"]),
                "ArrivalID" => trim($_POST["arrivalID"]),
                "ArrivalBreakupID" => trim($_POST["arrivalBreakupID"]),
                "ArrivalDate" => trim($_POST["arrivalDate"]),
                "VehicleID" => trim($_POST["vehicleID"]),
                "SampleQuantity" => trim($_POST["sampleQuantity"]),
                "NoofPieces" => trim($_POST["noofPieces"]),
                "ReceivingCount" => trim($_POST["receivingCount"]),
                "ReceivingQty" => trim($_POST["receivingQty"]),
                "ReceivingPlantID" => trim($_POST["arrivalPlantId"]),
                "IsVehicleTrack" => trim($_POST["IsVehicleTrack"]),
                "RemovePlantCode" => trim($_POST["removePlantCode"]), // sriram
                "MergeHonLoose" => $mergeHonLoose, // sriram
                "BreakUpdate" => true,
                "IsHONPacking" => $IsHONPacking,
            );
        }
        $result = CallAPI($api, "POST", "", $bodyContent);
        header('Content-Type: application/json');
        echo json_encode(fnDataTableList($result));
    } else if ($methodType == "TableUpdate") {
        $bodyContent = array(
            "ReceivingID" => trim($_POST["trackId"]),
            "ArrivalID" => trim($_POST["arrivalID"]),
            "ArrivalBreakupID" => trim($_POST["arrivalBreakupID"]),
            "ReceivalBreakupID" => trim($_POST["receivalBreakupID"]),
            "ArrivalDate" => trim($_POST["arrivalDate"]),
            "VehicleID" => trim($_POST["vehicleID"]),
            "SampleQuantity" => trim($_POST["sampleQuantity"]),
            "NoofPieces" => trim($_POST["noofPieces"]),
            "ReceivingCount" => trim($_POST["receivingCount"]),
            "ReceivingQty" => trim($_POST["receivingQty"]),
            "ReceivingPlantID" => trim($_POST["arrivalPlantId"]),
            "IsVehicleTrack" => trim($_POST["IsVehicleTrack"]),
            "HONPacking" => trim($_POST["HONPacking"]),
            "RemovePlantCode" => trim($_POST["removePlantCode"]), // sriram
            "MergeHonLoose" => $mergeHonLoose, // sriram
            "BreakUpdate" => true,
            "IsHONPacking" => $IsHONPacking,
        );
        $result = CallAPI($api, "POST", "", $bodyContent);
        header('Content-Type: application/json');
        echo json_encode(fnDataTableList($result));


    } else if ($methodType == "deleteRow") {

        $bodyContent = array(
            "ReceivalBreakupID" => trim($_POST["breakUpID"]),
            "VehicleID" => trim($_POST["vehicleID"]),
            "ArrivalDate" => trim($_POST["arrivalDate"]),
            "ReceivingPlantID" => trim($_POST["arrivalPlantId"]),
            "IsVehicleTrack" => trim($_POST["IsVehicleTrack"]),
            "RemovePlantCode" => trim($_POST["removePlantCode"]), // sriram
            "MergeHonLoose" => $mergeHonLoose, // sriram
            "IsHONPacking" => $IsHONPacking,
        );

        $result = CallAPI($api, "DELETE", "", $bodyContent);
        header('Content-Type: application/json');
        echo json_encode(fnDataTableList($result));
    }
    } else {

    $api = API_RM_PRODUCTION_TRACK_LIST; // API_RM_ARRIVAL_LIST; 

    $ArrivalDate = trim($_POST["ArrivalDate"]);// exit;
    $PlantID = trim($_POST["plantid"]);

    $bodyContent = array(
        'ArrivalDate' => $ArrivalDate,
        'PlantID' => $PlantID,
        'Supplier' => "",
        'PsId' => "",
        'Type' => "Vehicle",
        'IsVehicleTrack' => $IsVehicleTrack,
        'RemovePlantCode' => trim($_POST["removePlantCode"]), // sriram
    );
    $resList = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resList);
}
