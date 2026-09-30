<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_HARVESTING_BOYS_REPORT;

$StartDate = trim($_POST["startDate"]);
$EndDate = trim($_POST["endDate"]);
$CenterID = trim($_POST["center"]);
$DailyWages = trim($_POST["dailyWages"]);

$bodyContent = array(
    'StartDate' => $StartDate,
    'EndDate' => $EndDate,
    'CenterID' => $CenterID,
    'DailyWages' => $DailyWages,
);

$resList = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;

$List = $resList->Data;
?>

<table id="tblDataTable" class="table table-hover table-bordered">
    <thead class="thead-light">
        <tr>
            <th style="min-width: 30px;">S.No</th>
            <th style="min-width: 120px;">Booking Number</th>
            <th style="min-width: 120px;">Area </th>
            <th style="min-width: 100px;">Center</th>
            <th style="min-width: 90px;">Booking Date</th>
            <th style="min-width: 120px;">Booking Quantity </th>
            <th style="min-width: 50px;">Count </th>
            <th style="min-width: 100px;">BoysGroupName </th>
            <th style="min-width: 100px;">Working Boys</th>
            <th style="min-width: 100px;">Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($List as $item) { ?>
            <tr>
                <td><?php echo $item->SNO ?></td>
                <td><?php echo $item->BookingNumber ?></td>
                <td><?php echo $item->FarmAddress ?></td>
                <td><?php echo $item->StationName ?></td>
                <td><?php echo date('d-m-Y', strtotime($item->BookingDate)) ?></td>
                <td><?php echo $item->WithOutNetQty ?></td>
                <td><?php echo $item->CheckingCount ?></td>
                <td><?php echo $item->BoysGroupName ?></td>
                <td><?php echo $item->WorkingBoys ?></td>
                <td><?php echo $item->TotalAmount ?></td>
            </tr>
        <?php } ?>
    </tbody>
    <tfoot>
        <tr>
            <td></td>
            <td>Totals</td>
            <td></td>
            <td></td>
            <td></td>
            <td><?php echo $resList->TotalBookingQty ?></td>
            <td></td>
            <td></td>
            <td><?php echo $resList->TotalBoys ?></td>
            <td><?php echo $resList->GrandTotalAmount ?></td>

        </tr>
    </tfoot>
</table>