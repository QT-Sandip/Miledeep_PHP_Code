<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$bookingDayWise = $ClientConfigData->bookingDayWise;  //sriram

$methodType = trim($_POST["methodType"]);

$api = API_RM_BOOKING;

if ($methodType == "get") {

    $BookingID = $_POST["BookingID"];

    $bodyContent = array(
        "BookingID" => $BookingID,
        "HarvestFormNewDesignForSME" =>  !empty($_POST['HarvestFormNewDesignForSME'])?$_POST["HarvestFormNewDesignForSME"]:''
    );

    $result = CallAPI($api, "GET", "", $bodyContent);
} else if ($methodType == "post") {

    $BookingID = trim($_POST["BookingID"]); // BookingID == Empty (Insert new) or Edit Booking by ID
    $drpSpecies = trim($_POST["drpSpecies"]);
    $drpBookingStationId = trim($_POST["drpBookingStationId"]);
    $drpPurchaseType = trim($_POST["drpPurchaseType"]);
    $txtBookingNumber = trim($_POST["txtBookingNumber"]);
    $drpVehicleNumber = trim($_POST["drpVehicleNumber"]);
    $drpDriverName = trim($_POST["drpDriverName"]);
    $drpGraderName = trim($_POST["drpGraderName"]);
    $txtBookingDate = trim($_POST["txtBookingDate"]);
    $drpAgentName = trim($_POST["drpAgentName"]);
    $txtFarmLocation = trim($_POST["txtFarmLocation"]);
    $drpSuppliers = trim($_POST["drpSuppliers"]);
    $txtExtra = trim($_POST["txtExtra"]);
    $txtRemarks = trim($_POST["txtRemarks"]);
    // $drpVariety = trim($_POST["drpVariety"]);
    $txtBookingCount = trim($_POST["txtBookingCount"]);
    $txtBookingWeight = trim($_POST["txtBookingWeight"]);
    $txtBookingRate = trim($_POST["txtBookingRate"]);

    $SampledBy = !empty($_POST["SampledBy"])?trim($_POST["SampledBy"]):'';
    $TagId = !empty($_POST["TagId"])?trim($_POST["TagId"]):'';
    $SampleStatus = !empty($_POST["SampleStatus"])?trim($_POST["SampleStatus"]):'';

    $bodyContentPost = array(
        "BookingID" => $BookingID,

        "Species" => $drpSpecies,
        "GraderID" => $drpGraderName,
        "AgentID" => $drpAgentName,
        "BookingNumber" => $txtBookingNumber,
        "DriverID" => $drpDriverName,
        "VehicalID" => $drpVehicleNumber,
        "FarmAddress" => $txtFarmLocation,
        "PurchaseCategoryID" => $drpPurchaseType,
        "BookingDate" => $txtBookingDate,
        "BookingStationID" => $drpBookingStationId,
        "Remarks" => $txtRemarks,
        "RmSupplierID" => $drpSuppliers,
        "Extra" => $txtExtra,

        "BookingCount" => $txtBookingCount,
        "BookingWeight" => $txtBookingWeight,
        "BookingRate" => $txtBookingRate,
        "BookingDayWise" => $bookingDayWise,

        "SampledBy" => $SampledBy,
        "TagId" => $TagId,
        "SampleStatus" => $SampleStatus,
        "HarvestFormNewDesignForSME" =>  !empty($_POST['HarvestFormNewDesignForSME'])?$_POST["HarvestFormNewDesignForSME"]:''
        // "PostedUserID" => "",
        // "SOURCE" => ""
    );

    // echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
    // exit;

    $result = CallAPI($api, "POST", "", $bodyContentPost);
} else if ($methodType == "delete") {
    $BookingID = trim($_POST["BookingID"]);
    $BreakUpID = trim($_POST["BreakUpID"]);

    $bodyContent = array(
        "BookingID" => $BookingID,
        "BreakUpID" => $BreakUpID,
        "HarvestFormNewDesignForSME" =>  !empty($_POST['HarvestFormNewDesignForSME'])?$_POST["HarvestFormNewDesignForSME"]:''
    );

    $result = CallAPI($api, "DELETE", "", $bodyContent);
}

// var_dump($result);
$resBookingData = $result->BookingData;
$resBreakUpData = $result->BreakUpData;
$resGrandTotal = $result->GrandTotal;

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
                        <th>Count</th>
                        <th>Weight</th>
                        <th>Rate</th>
                        <th>Total Value</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>';
    $i = 1;
    foreach ($resBreakUpData as $item) {

        $deleteAccess = $result->DeleteAccess ? "disabled" : "";
        $htmlResponse .= '
                        <tr >
                            <td>' . $i . '</td>
                            <td>' . $item->Count . '</td>
                            <td>' . $item->Weight . '</td>
                            <td>' . $item->Rate . '</td>
                            <td>' . $item->BreakupTotal . '</td>
                            <td>
                                <button type="button" id="btnDelete" name="btnDelete" data-BookingBrkUpID="' . $item->BookingBrkUpID . '" class="btn btn-link p-0 deleteBrkUp" ' . $DeleteAccess . ' >
                                    <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                </button>
                            </td>
                        </tr>';
        $i++;
    }
    $htmlResponse .= '
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="2"><span  style="font-weight: 600;font-size: 16px;">Grand Total</span></td>
                            <td><span style="font-weight: 600;font-size: 16px;">' . $resGrandTotal->WeightTotal . '</span></td>
                            <td><span style="font-weight: 600;font-size: 16px;">' . $resGrandTotal->AvgRate . '</span></td>
                            <td><span style="font-weight: 600;font-size: 16px;">' . $resGrandTotal->ValueTotal . '</span></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>';
}

$response = array('html' => $htmlResponse, 'json' => $resBookingData, "Error" => $error, "Msg" => $msg);
echo json_encode($response);

?>