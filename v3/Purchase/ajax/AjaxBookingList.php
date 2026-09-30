<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RM_BOOKING_LIST;

$PsId = trim($_POST["PsId"]);
$BookingDate = trim($_POST["BookingDate"]);
$ArrivalPlant = trim($_POST["ArrivalPlant"]);

$bodyContent = array(
    'BookingDate' => $BookingDate,
    'PsId' => $PsId,
    'ArrivalPlant' => $ArrivalPlant,
    'Type' => "",
);

$resList = CallAPI($api, "GET", "", $bodyContent);

$error = $resList->Error;
$List = $resList->Data;
// echo json_encode($List, JSON_PRETTY_PRINT);
?>

<?php if (!isset($error) || empty($error)) {
    ?>

    <table id="tblDataTable" class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th>S.No</th>
                <th>Species</th>
                <th>Booking Number </th>
                <th>Booking Date</th>
                <th>PurchaseType</th>
                <th>Grader</th>
                <th>Agent</th>
                <th>Booking Count</th>
                <th>Booking Weight</th>
                <th>Booking Rate</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $TotalBookingWeight = 0; // Initialize TotalBookingWeight if not already done
            foreach ($List as $item) {
                $TotalBookingWeight += $item->BookingWeight; ?>
                <tr>
                    <td><?php echo $item->SNO ?></td>
                    <td><?php echo $item->Species ?></td>
                    <td><?php echo $item->BookingNumber ?></td>
                    <td><?php echo $item->BookingDate ?></td>
                    <td><?php echo $item->PurchaseType ?></td>
                    <td><?php echo $item->Grader ?></td>
                    <td><?php echo $item->Agent ?></td>
                    <td><?php echo $item->BookingCount ?></td>
                    <td class="booking-weight"><?php echo number_format($item->BookingWeight, 3) ?></td>
                    <td><?php echo number_format($item->BookingRate, 2) ?></td>
                    <td>
                        <?php if ($resList->EditAccess) { ?>
                            <a target="_blank" href="<?php echo $resList->EditLink . "?BookingID=" . $item->BookingID ?>"
                                class="mr-3 text-primary" data-placement="top" title="Edit Booking" data-original-title="Edit">
                                <i class="mdi mdi-pencil font-size-16"></i>
                            </a>
                        <?php } ?>
                    </td>
                </tr>
            <?php } // End of foreach loop ?>

            <!-- Total row to appear after all items -->


        </tbody>
        <tfoot>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td id="total-booking-weight"><?php echo number_format($TotalBookingWeight, 3) ?></td>
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

<?php } ?>