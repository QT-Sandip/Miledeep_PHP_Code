<?php
include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT.'config/api-call.php';

$api = API_RM_BILLS_LIST;

$center = trim($_POST["center"]);
$supplier = trim($_POST["supplier"]);
$paymentMode = trim($_POST["paymentMode"]);
$startDate = trim($_POST["startDate"]);
$endDate = trim($_POST["endDate"]);

$bodyContent = array (
    'Center' => $center,
    'Supplier' => $supplier,
    'PaymentMode' => $paymentMode,
    'StartDate' => $startDate,
    'EndDate' => $endDate,
);

$resList = CallAPI($api, "GET", "", $bodyContent);

if(isset($resList) && !empty($resList)) {

    $error = $resList->Error;

if (!isset($error) || empty($error)) { 
    $List = $resList->Data;
    
?>                                            
    <table id="tblDataTable" class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th>S.No</th>	
                <th>ArrivalNumber</th>	
                <th>ArrivalDate </th>
                <th>PurchaseType</th>	
                <th>Grader</th>	
                <th>Agent</th>
                <th>ArrivalCount</th>
                <th>ArrivalWeight</th>
                <th>ArrivalRate</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($List as $item) { ?>
                <tr>
                    <td><?php echo $item->SNO ?></td>	
                    <td><?php echo $item->ArrivalNumber ?></td>	
                    <td><?php echo $item->ArrivalDate ?></td>
                    <td><?php echo $item->PurchaseType ?></td>
                    <td><?php echo $item->Grader ?></td>	
                    <td><?php echo $item->Agent ?></td>
                    <td><?php echo $item->ArrivalCount ?></td>
                    <td><?php echo $item->ArrivalWeight ?></td>	
                    <td><?php echo $item->ArrivalRate ?></td>  
                    <td><?php 
                        if($item->BillStatus == "0") {
                            echo '<span class="statusPending"> Pending <span>';
                        }else{
                            echo '<span class="statusCompleted"> Completed <span>';
                        }
                        ?></td>
                    <td class="text-center">
                        <?php if ($resList->EditAccess) { ?>
                            <?php if($item->BillStatus == "0") { ?>
                            <a target="_blank" href="<?php echo $resList->EditLink."?ArrivalID=". $item->RmArrivalID ?>" class="mr-3 text-primary" data-placement="top" title="Add Bill" data-original-title="Add">
                                <i class="mdi mdi-plus  font-size-16"></i>
                            </a>
                            <?php } else { ?>
                             <a target="_blank" href="<?php echo $resList->EditLink . "?ArrivalID=" . $item->RmArrivalID ?>" class="mr-3 text-primary" data-placement="top" title="Edit Bill" data-original-title="Edit">
                                <i class="mdi mdi-pencil  font-size-16"></i>
                            </a> &nbsp;
                             <a target="_blank" href="<?php echo WORKING_URL ?>Purchase/Reports/T1/commission-bill-page1?ID=<?php echo $item->RmArrivalID ?>" class="mr-3 text-primary" data-placement="top" title="Print Bill" data-original-title="printer">
                                <i class="mdi mdi-printer font-size-16"></i>
                            </a> &nbsp;
                             <a target="_blank" href="<?php echo WORKING_URL ?>Purchase/Reports/T1/commission-bill-page2?ID=<?php echo $item->RmArrivalID ?>" class="mr-3 text-primary" data-placement="top" title="Commission Bill" data-original-title="printer">
                                <i class="mdi mdi-printer font-size-16"></i>
                            </a>
                             <?php } ?>
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

<?php } else {

    ?>
    <table id="tblDataTable" class="table table-hover table-bordered">
    <thead class="thead-light">
        <tr>
            <th>S.No</th>	
            <th>Payment Mode</th>	
            <th>Bill Date </th>
            <th>Voucher No</th>	
            <th>Cheque/NEFT.No</th>	
            <th>Amount</th>
            <th>Type</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        
            <tr>
                <td>1</td>	
                <td>Payment Mode</td>	
                <td>Bill Date </td>
                <td>Voucher No</td>	
                <td>Cheque/NEFT.No</td>	
                <td>Amount</td>
                <td>
                    <!-- <span class="statusPending"> Pending <span> -->
                    <span class="statusCompleted"> Completed <span>
                </td>
                <td class="text-center">
                   
                        <!-- <a target="_blank" href="#" class="mr-3 text-primary" data-placement="top" title="Add Bill" data-original-title="Add">
                            <i class="mdi mdi-plus  font-size-16"></i>
                        </a> -->
                   
                        <a target="_blank" href="#" class="mr-3 text-primary" data-placement="top" title="Edit" data-original-title="Edit">
                            <i class="mdi mdi-pencil  font-size-16"></i>
                        </a> &nbsp;
                        <a target="_blank" href="#" class="mr-3 text-primary" data-placement="top" title="Print Bill" data-original-title="printer">
                            <i class="mdi mdi-printer font-size-16"></i>
                        </a> &nbsp;   
                        <a target="_blank" href="#" class="mr-3 text-danger" data-placement="top" title="Delete " data-original-title="Delete">
                            <i class="mdi mdi-delete  font-size-16"></i>
                        </a>
                   
                </td>
            </tr>

            <tr>
                <td>2</td>	
                <td>Payment Mode</td>	
                <td>Bill Date </td>
                <td>Voucher No</td>	
                <td>Cheque/NEFT.No</td>	
                <td>Amount</td>
                <td>
                    <!-- <span class="statusPending"> Pending <span> -->
                    <span class="statusCompleted"> Completed <span>
                </td>
                <td class="text-center">
                   
                        <!-- <a target="_blank" href="#" class="mr-3 text-primary" data-placement="top" title="Add Bill" data-original-title="Add">
                            <i class="mdi mdi-plus  font-size-16"></i>
                        </a> -->
                   
                        <a target="_blank" href="#" class="mr-3 text-primary" data-placement="top" title="Edit" data-original-title="Edit">
                            <i class="mdi mdi-pencil  font-size-16"></i>
                        </a> &nbsp;
                        <a target="_blank" href="#" class="mr-3 text-primary" data-placement="top" title="Print Bill" data-original-title="printer">
                            <i class="mdi mdi-printer font-size-16"></i>
                        </a> &nbsp;
                        <a target="_blank" href="#" class="mr-3 text-danger" data-placement="top" title="Delete " data-original-title="Delete">
                            <i class="mdi mdi-delete  font-size-16"></i>
                        </a>
                   
                </td>
            </tr>
            
    </tbody>
</table>

<?php 


}
} ?>
