<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_HARVESTING_DETAILS;

$methodType = trim($_POST["methodType"]);

if ($methodType != "getBookingData") {
    if ($methodType == "formPost") {
        $bodyContentPost = array(
            "HarvestingID" => $_POST['harvestID'],
            "BookingID" => $_POST['bookingID'],
            "BookingDate" => $_POST['bookingDate'],
            "SupervisorORGrader" => $_POST['supervisor'],
            "FarmerName" => $_POST['farmerName'],
            "PhoneNumber" => $_POST['phoneNumber'],
            "SealNumber" => $_POST['sealNumber'],
            "BoysGroupID" => $_POST['boysGroup'],
            "WorkingBoys" => $_POST['workingBoys'],
            "MTCrates" => $_POST['mtCrates'],
            "Nets" => $_POST['nets'],
            "Savels" => $_POST['savels'],
            "WeightStones" => $_POST['weightStones'],
            "WeightMc" => $_POST['weightMC'],
            "WaterTin" => $_POST['waterTin'],
            "Remarks" => $_POST['remarks'],
            "MTCrateWeight" => $_POST['mtCrateWeight'],
            "CheckingCount" => $_POST['checkingCount'],
            "CheckingQty" => $_POST['checkingQty'],
            "MaterialTotalQuantity" => $_POST['materialTotalQty'],
            "MaterialTotalCrates" => $_POST['materialTotalCrates'],
            "HarvestingBreakupID" => $_POST['harvestBreakUpID'],
            "Variety" => $_POST['variety'],
            "Weight" => $_POST['weight'],
            "NoofCrates" => $_POST['noofCrates'],
            "Count" => $_POST['count'],
            "HarvestDate" => $_POST['harvestDate'],
            "CheckingCrate" => $_POST['checkingCrate'],
            "NumberOfPieces" => $_POST['numberOfPieces'],

        );
        // echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
        // exit;
        $resList = CallAPI($api, "POST", "", $bodyContentPost);
    } else if ($methodType == 'getList') {
        $bodyContent = [
            "HarvestingID" => $_POST['harvestID'],
        ];
        $resList = CallAPI($api, "GET", "", $bodyContent);
    }

    $BreakUpData = $resList->Data[0]->BreakUpData;
    $HarvestingID = $resList->HarvestID;

    $Error = $resList->Error;
    $Msg = $resList->Msg;

    $content = '';
    $content .= '<table id="tblDataTable" class="table table-hover table-bordered">
    <thead class="thead-light">
        <tr>
            <th style="width: 80px">S.No</th>
            <th>Variety</th>
            <th>Weight(Kgs)</th>
            <th>No Of Crates</th>
            <th>Count</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>';
    foreach ($BreakUpData as $item) {
        $content .= '
      <tr>
                <td>' . $item->SNO . '</td>
                <td class="tdVariety" data-varietyid="' . $item->Variety . '">' . $item->DefectName . '</td>
                <td class="tdWeight">' . $item->Weight . '</td>
                <td class="tdNoofCrates">' . $item->NoofCrates . '</td>
                <td class="tdCount">' . $item->Count . '</td>
                <td class="text-center">
                    <button type="button" id="btnEdit" name="btnEdit" data-brkUpID="' . $item->HarvestingBreakupID . '" class="btn btn-link p-0 btnEdit" title="Edit">
                        <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                    </button>
                </td>
            </tr>';
    }
    $content .= '</tbody>
</table>
<div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of ' . $resTotal . ' entries</div>';

    $ArrayData = [
        'Content' => $content,
        'Error' => $Error,
        'Msg' => $Msg,
        'HarvestingID' => $HarvestingID,
    ];

    header('Content-Type: application/json');
    echo json_encode($ArrayData);
} else {

    $bodyContent = array(
        'BookingID' => $_POST["bookingID"],
    );
    $resBooking = CallAPI($api, "GET", "", $bodyContent);
    $resBookingData = $resBooking->BookingData;
    header('Content-Type: application/json');
    echo json_encode($resBookingData);
    exit;
}
