<?php

include_once '../../config/global.php';
include_once '../../config/api-call.php';

$api = API_RM_BOOKING;

$hdnSaveType = $_POST["hdnSaveType"];

$BookingID = $_POST["BookingID"];
$drpSpecies = $_POST["drpSpecies"];
$drpBookingStationId = $_POST["drpBookingStationId"];
$drpPurchaseType = $_POST["drpPurchaseType"];
$txtBookingNumber = $_POST["txtBookingNumber"];
$drpVehicleNumber = $_POST["drpVehicleNumber"];
$drpDriverName = $_POST["drpDriverName"];
$drpGraderName = $_POST["drpGraderName"];
$txtBookingDate = $_POST["txtBookingDate"];
$drpAgentName = $_POST["drpAgentName"];
$txtFarmLocation = $_POST["txtFarmLocation"];
$txtRemarks = $_POST["txtRemarks"];
$txtBookingCount = $_POST["txtBookingCount"];
$txtBookingWeight = $_POST["txtBookingWeight"];
$txtBookingRate = $_POST["txtBookingRate"];

$bodyContent = array(
    "Species" => $drpSpecies,
    "BookingStationID" => $drpBookingStationId,
    "PurchaseCategoryID" => $drpPurchaseType,
    "BookingNumber" => $txtBookingNumber,
    "VehicalID" => $drpVehicleNumber,
    "DriverID" => $drpDriverName,
    "GraderID" => $drpGraderName,
    "BookingDate" => $txtBookingDate,
    "AgentID" => $drpAgentName,
    "FarmAddress" => $txtFarmLocation,
    "Remarks" => $txtRemarks,
    "BookingCount" => $txtBookingCount,
    "BookingWeight" => $txtBookingWeight,
    "BookingRate" => $txtBookingRate,
    "BookingID" => $BookingID
);
if(isset($hdnSaveType) && !empty($hdnSaveType)) {
    $resUpdateSingleBookings = CallAPI($api, "POST", "", $bodyContent);
    var_dump($resUpdateSingleBookings); 
    $resUpdateBookings = $resUpdateSingleBookings->Data;

    // $resGetSingleBookingsList = $resUpdateBookings->BreakUpData;
    $resGetSingleBookings = $resUpdateBookings->BookingData;
}else{
    $apiGetSingleBookings = API_RM_BOOKING;

    $bodyContent = array(
        "BookingID" => $BookingID
    );

    $resGetSingleBookingsAPI = CallAPI($apiGetSingleBookings, "GET", "", $bodyContent);
    $resGetSingleBookingsList = $resGetSingleBookingsAPI->BreakUpData;
    $resGetSingleBookings = $resGetSingleBookingsAPI->BookingData[0];

}
?>

<?php
if (isset($resGetSingleBookingsList) && !empty($resGetSingleBookingsList)) {
    ?>
    <div class="card">
        <div class="card-body">
        
            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>S.No</th>	
                        <!-- <th>Booking Break ID </th> -->
                        <th>Count</th>	
                        <th>Weight</th>	
                        <th>Rate</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 1;
                    foreach ($resGetSingleBookingsList as $item) { ?>
                        <tr>
                            <td><?php echo $i ?></td>	   
                            <!-- <td><?php // echo $item->BookingBrkUpID ?></td> -->
                            <td><?php echo $item->Count ?></td>	
                            <td><?php echo $item->Weight ?></td>
                            <td><?php echo $item->Rate ?></td>	
                            <td>
                                <button type="button" id="btnDelete" name="btnDelete" disabled>Delete</button>
                            </td>                                             
                        </tr>
                        <?php $i++;
                    } ?>
                </tbody>
            </table>

        </div>
    </div>
<?php } ?>