<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);

$api = API_RM_ARRIVAL;

if ($methodType == "get") {

    $ArrivalID = $_POST["ArrivalID"];

    $bodyContent = array(
        "ArrivalID" => $ArrivalID
    );

    $result = CallAPI($api, "GET", "", $bodyContent);
} else if ($methodType == "post") {

    $ArrivalID = trim($_POST["ArrivalID"]); // ArrivalID == Empty (Insert new) or Edit Arrival by ID
    $ArrivalBrkUpID = trim($_POST["ArrivalBrkUpID"]);
    $drpSpecies = trim($_POST["drpSpecies"]);
    $drpArrivalStationId = trim($_POST["drpArrivalStationId"]);
    $drpPurchaseType = trim($_POST["drpPurchaseType"]);
    $drpProcessingStations = trim($_POST["drpProcessingStations"]);
    $txtAutoArrivalRefNo = trim($_POST["txtAutoArrivalRefNo"]);
    $hdnArrivalNumber = trim($_POST["hdnArrivalNumber"]);
    $drpVehicleNumber = trim($_POST["drpVehicleNumber"]);
    $drpDriverName = trim($_POST["drpDriverName"]);
    $drpGraderName = trim($_POST["drpGraderName"]);
    $txtArrivalDate = trim($_POST["txtArrivalDate"]);
    $txtBillDate = trim($_POST["txtBillDate"]);
    $drpAgentName = trim($_POST["drpAgentName"]);
    $txtFarmLocation = trim($_POST["txtFarmLocation"]);
    $drpSuppliers = trim($_POST["drpSuppliers"]);
    // $txtExtra = trim($_POST["txtExtra"]);
    $txtRemarks = trim($_POST["txtRemarks"]);
    $txtHarvestCommission = trim($_POST["txtHarvestCommission"]);

    $drpVariety = trim($_POST["drpVariety"]);
    $txtArrivalCount = trim($_POST["txtArrivalCount"]);
    $txtArrivalWeight = trim($_POST["txtArrivalWeight"]);
    $txtArrivalRate = trim($_POST["txtArrivalRate"]);
    $drpMasterRates = trim($_POST["drpMasterRates"]);

    $bodyContentPost = array(
        "ArrivalID" => $ArrivalID,
        "ArrivalBrkUpID" => $ArrivalBrkUpID,
        "Species" => $drpSpecies,
        "GraderID" => $drpGraderName,
        "AgentID" => $drpAgentName,
        "ArrivalNumber" => $hdnArrivalNumber,
        "AutoArrivalReferenceNo" => $txtAutoArrivalRefNo,
        "DriverID" => $drpDriverName,
        "VehicalID" => $drpVehicleNumber,
        "FarmAddress" => $txtFarmLocation,
        "PurchaseCategoryID" => $drpPurchaseType,
        "ProcessingStationID" => $drpProcessingStations,
        "ArrivalDate" => $txtArrivalDate,
        "BillDate" => $txtBillDate,
        "ArrivalStationID" => $drpArrivalStationId,
        "Remarks" => $txtRemarks,
        "RmSupplierID" => $drpSuppliers,
        // "Extra" => $txtExtra,
        "HarvestCommission" => $txtHarvestCommission,

        "Variety" => $drpVariety,
        "ArrivalCount" => $txtArrivalCount,
        "ArrivalWeight" => $txtArrivalWeight,
        "ArrivalRate" => $txtArrivalRate,
        "MasterRates" => $drpMasterRates,
        // "PostedUserID" => "",
        // "SOURCE" => ""
    );

    // echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
    // exit;

    $result = CallAPI($api, "POST", "", $bodyContentPost);
} else if ($methodType == "delete") {
    $ArrivalID = $_POST["ArrivalID"];
    $BreakUpID = $_POST["BreakUpID"];

    $bodyContent = array(
        "ArrivalID" => $ArrivalID,
        "BreakUpID" => $BreakUpID
    );

    $result = CallAPI($api, "DELETE", "", $bodyContent);
}
// else if ($methodType == "getRmRate") {

//     $apiRmRate = API_GET_RM_RATE;

//     $bodyContent = array(
//         "BillDate" => trim($_POST["billDate"]),
//         "CenterId" => trim($_POST["centerId"]),
//         "Species" => trim($_POST["species"]),
//         "ArrivalCount" => trim($_POST["arrivalCount"])
//     );
//     // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
//     $resRmRate = CallAPI($apiRmRate, "GET", "", $bodyContent);
//     // exit;
//     // header('Content-Type: application/json');
//     $rmRate = $resRmRate->RmRate;
//     echo json_encode((float)$rmRate);
//     exit;
// }



$resArrivalData = $result->ArrivalData;
$resBreakUpData = $result->BreakUpData;
$resGrandTotal = $result->GrandTotal;
$resRmRate = $result->RmRate;

// echo json_encode($resBreakUpData, JSON_PRETTY_PRINT);
// exit;
$error = $result->Error;
$msg = $result->Msg;

$DeleteAccess = $result->DeleteAccess === true ? '' : 'disabled';

?>

<?php

if (!isset($error) || empty($error)) {
    $htmlResponse = '
    <div class="card">
        <div class="card-body">
            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>S.No</th>
                        <th>Variety</th>
                        <th>Count</th>
                        <th>Weight</th>
                        <th>Master Rate</th>
                        <th>Harvest Com.</th>
                        <th>Rate</th>
                        <th>Total Value</th>
                        <th>Bill Amt</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>';
    $i = 1;
    foreach ($resBreakUpData as $item) {
        // echo $result->DeleteAccess;
        // $deleteAccess = $result->DeleteAccess ? "disabled" : "";  
        $htmlResponse .= '
                        <tr >
                            <td>' . $i . '</td>
                            <td class="tdVariety">' . $item->Variety . '</td>
                            <td class="tdCount">' . $item->Count . '</td>
                            <td class="tdWeight">' . $item->Weight . '</td>
                            <td>' . $item->MasterRate . '</td>
                            <td>' . $item->HarvestCommission . '</td>
                            <td class="tdRate">' . $item->Rate . '</td>
                            <td>' . $item->BreakupTotal . '</td>
                            <td>' . $item->BillAmt . '</td>
                            <td>
                                <button type="button" id="btnEdit" name="btnEdit" data-ArrivalBrkUpID="' . $item->ArrivalBrkUpID . '" class="btn btn-link p-0 editBrkUp" ' . $DeleteAccess . ' >
                                    <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                                </button>';
        if ($item->RecievalCount == 0) {
            $htmlResponse .= '   <button type="button" id="btnDelete" name="btnDelete" data-ArrivalBrkUpID="' . $item->ArrivalBrkUpID . '" class="btn btn-link p-0 deleteBrkUp" ' . $DeleteAccess . ' >
                                    <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                </button>';
        }
        $htmlResponse .= '</td>
                        </tr>';
        $i++;
    }
    $htmlResponse .= '
                    </tbody>
                    <tfoot>
                        <tr>
                            <td><span  style="font-weight: 600;font-size: 16px;">Grand Total</span></td> 
                            <td></td>
                            <td></td>
                            <td><span style="font-weight: 600;font-size: 16px;">' . $resGrandTotal->WeightTotal . '</span></td>
                            <td></td>
                            <td></td>
                            <td><span style="font-weight: 600;font-size: 16px;">' . $resGrandTotal->AvgRate . '</span></td>
                            <td><span style="font-weight: 600;font-size: 16px;">' . $resGrandTotal->ValueTotal . '</span></td>
                            <td><span style="font-weight: 600;font-size: 16px;">' . $resGrandTotal->BillAmtTotal . '</span></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>';
}

$response = array('html' => $htmlResponse, 'json' => $resArrivalData, "Error" => $error, "Msg" => $msg);
echo json_encode($response);

?>