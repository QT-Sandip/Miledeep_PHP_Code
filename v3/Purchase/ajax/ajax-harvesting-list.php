<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_HARVESTING_DETAILS;

$bodyContent = array(
    'BookingDate' => trim($_POST["bookingDate"]),
    'PurchaseStation' => trim($_POST['purchaseCenters']),
);

$resList = CallAPI($api, "GET", "", $bodyContent);

$error = $resList->Error;
$List = $resList->Data;
?>

<?php if (!isset($error) || empty($error)) { ?>

    <table id="tblDataTable" class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th>S.No</th>
                <th style="min-width: 120px;">Booking Date</th>
                <th style="min-width: 120px;">Booking Number</th>
                <th style="min-width: 120px;">Farmer Name</th>
                <th style="min-width: 120px;">Phone Number</th>
                <th style="min-width: 120px;">Seal Number</th>
                <th style="min-width: 120px;">Material Total Qty</th>
                <th style="min-width: 120px;">Material Total Crates weight</th>
                <th style="min-width: 120px;">With Out Net Weight</th>
                <th style="width: 120px;">Remarks</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $TotalMaterialTotalQuantity = 0;
            $TotalMaterialTotalCrates = 0;
            $TotalWithOutNetQty = 0;
            foreach ($List as $item) {
                $TotalMaterialTotalQuantity += $item->MaterialTotalQuantity;
                $TotalMaterialTotalCrates += $item->MaterialTotalCrates;
                $TotalWithOutNetQty += $item->WithOutNetQty;
                ?>
                <tr>
                    <td><?php echo $item->SNO ?></td>
                    <td><?php echo date('d-m-Y', strtotime($item->BookingDate)) ?></td>
                    <td><?php echo $item->BookingNumber ?></td>
                    <td><?php echo $item->FarmerName ?></td>
                    <td><?php echo $item->PhoneNumber ?></td>
                    <td><?php echo $item->SealNumber ?></td>
                    <td class="material-total-quantity"><?php echo $item->MaterialTotalQuantity ?></td>
                    <td class="material-total-crates"><?php echo $item->MaterialTotalCrates ?></td>
                    <td class="without-netqty"><?php echo $item->WithOutNetQty ?></td>
                    <td><?php echo $item->Remarks ?></td>
                    <td>
                        <?php if ($item->HarvestingID) { ?>
                            <a target="_blank"
                                href="create-harvesting?BookingID=<?php echo $item->BookingID ?>&HarvestingID=<?php echo $item->HarvestingID ?>"
                                class="mr-3 text-primary" data-placement="top" title="Edit Harvesting" data-original-title="Edit">
                                <i class="mdi mdi-pencil font-size-16"></i>
                            </a>
                            <a target="_blank"
                                href='<?php echo "Reports/harvesting-details-print?HarvestingID=" . $item->HarvestingID ?>'
                                class="mr-3 text-success" data-placement="top" title="View Print" data-original-title="View">
                                <i class="dripicons-preview font-size-16"></i>
                            </a>
                            <a target="_blank"
                                href='<?php echo "Reports/T1/WeighmentSlip?HarvestingID=" . $item->HarvestingID . "&BookingID=" . $item->BookingID ?>'
                                class="mr-3 text-success" data-placement="top" title="Weighment Slip" data-original-title="View">
                                <i class="dripicons-print font-size-16"></i>
                            </a>
                        <?php } else { ?>
                            <a target="_blank"
                                href="create-harvesting?BookingID=<?php echo $item->BookingID ?>&HarvestingID=<?php echo $item->HarvestingID ?>"
                                class="mr-3 text-primary" data-placement="top" title="Add Harvesting" data-original-title="Edit">
                                <i class="mdi mdi-plus font-size-16"></i>
                            </a>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
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
                <td id="TotalWithOutNetQty"><?php echo $TotalWithOutNetQty ?></td>
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>
<?php } ?>