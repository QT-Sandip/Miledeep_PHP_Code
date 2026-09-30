<?php

include_once '../../config/global.php';
include_once '../../config/api-call.php';

$api = API_RM_BOOKING_LIST;

$PsId = trim($_POST["PsId"]);
$ArrivalDate = trim($_POST["ArrivalDate"]);

$bodyContent = array(
    'BookingDate' => $ArrivalDate,
    'PsId' => $PsId,
    'Type' => "",
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
            <?php foreach ($List as $item) { ?>
                <tr>
                    <td><?php echo $item->SNO ?></td>	
                    <td><?php echo $item->Species ?></td>	
                    <td><?php echo $item->BookingNumber ?></td>
                    <td><?php echo $item->BookingDate ?></td>	
                    <td><?php echo $item->PurchaseType ?></td>
                    <td><?php echo $item->Grader ?></td>	
                    <td><?php echo $item->Agent ?></td>
                    <td><?php echo $item->BookingCount ?></td>
                    <td><?php echo $item->BookingWeight ?></td>	
                    <td><?php echo $item->BookingRate ?></td>  
                    <td>
                        <?php if ($resList->EditAccess) { ?>
                            <a target="_blank" href="<?php echo $resList->EditLink."?BookingID=". $item->BookingID ?>" class="mr-3 text-primary" data-placement="top" title="Edit Booking" data-original-title="Edit">
                                <i class="mdi mdi-pencil font-size-16"></i>
                            </a>
                        <?php } ?>

                        <?php if ($resList->DeleteAccess) { ?>
                            <a target="_blank" href="#" class="mr-3 text-primary" data-placement="top" title="Delete Booking" data-original-title="Delete">
                                <i class="mdi mdi-pencil font-size-16"></i>
                            </a>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

<?php } ?>