<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RM_BOOKING_REPORT;

$StartDate = trim($_POST["startDate"]);
$EndDate = trim($_POST["endDate"]);
$CenterID = trim($_POST["center"]);

$bodyContent = array(
    'StartDate' => $StartDate,
    'EndDate' => $EndDate,
    'CenterID' => $CenterID,
);

$resList = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
$List = $resList->Data;
$TotalWeight = $resList->TotalWeight;
?>

<table id="tblDataTable" class="table table-hover table-bordered">
    <thead class="thead-light">
        <tr>
            <th style="min-width: 30px;">S.No</th>
            <th style="min-width: 120px;">Batch No</th>
            <th style="min-width: 120px;">Counts </th>
            <th style="min-width: 100px;">Material</th>
            <th style="min-width: 90px;">Vehical Details</th>
            <th style="min-width: 50px;">Booking Tons </th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($List as $item) { ?>
            <tr>
                <td><?php echo $item->SNO ?></td>
                <td><?php echo $item->BookingNumber ?></td>
                <td><?php echo $item->BookingCount ?></td>
                <td><?php echo $item->BookingWeight ?></td>
                <td><?php echo $item->VehicalNumber ?></td>
                <td><?php echo $item->BookingWeight ?></td>
            </tr>
        <?php  } ?>
    </tbody>
    <tfoot>
        <tr>
            <th></th>
            <th>Totals</th>
            <th></th>
            <th><?php echo $TotalWeight ?></th>
            <th></th>
            <th><?php echo $TotalWeight ?></th>
        </tr>
    </tfoot>
</table>