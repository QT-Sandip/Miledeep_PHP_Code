<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_PAYMENT_LIST;

$PsId = trim($_POST["PsId"]);
$AgentType = trim($_POST["BillerType"]);
$AgentID = trim($_POST["AgentID"]);
$SupplierID = trim($_POST["SupplierID"]);
$BillDate = trim($_POST["BillDate"]);
// $Type = trim($_POST["Type"]);

$bodyContent = array(
    'PsId' => $PsId,
    'BillerType' => $AgentType,
    'AgentID' => $AgentID,
    'SupplierID' => $SupplierID,
    'BillDate' => $BillDate,
    'Type' => "0"
);

$resList = CallAPI($api, "GET", "", $bodyContent);

 //var_dump($resList); exit;

if(isset($resList) && !empty($resList)) {
    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->Data;
?>                                          
    <table id="tblDataTable" class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th class="cellCheckbox text-center" style="min-width: 80px;">
                    <input type="checkbox" class="big-checkbox" id="checkall" />
                </th>	
                <th>S No</th>	
                <th>Bill No</th>
                <th>Bill Dates</th>	
                <th>Supplier</th>	
                <th>Agent</th>	
                <th>Act Bill</th>
                <th>Already Paid</th>
                <th>Balance</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php  foreach ($List as $item) { ?>
                <tr>
                    <td class="tdCheckList text-center" style="min-width: 80px;">
                        <input type="checkbox" class="rowCheck big-checkbox"  value="<?php echo $item->RmArrivalID ?>" />
                    </td>
                    <td><?php echo $item->SNO ?></td>	
                    <td><?php echo $item->ArrivalNumber ?></td>
                    <td><?php echo $item->BillDate ?></td>	
                    <td><?php echo $item->SupplierName ?></td>	
                    <td><?php echo $item->Agent ?></td>	
                    <td  style="text-align:right;"><?php echo number_format($item->BillAmt,2) ?></td>
                    <td  style="text-align:right;"><?php echo number_format($item->PaidAmount,2) ?></td>
                    <td  style="text-align:right;"><?php echo number_format($item->BalanceAmount,2) ?></td>
                    <td class="text-center">
                        <button class="mr-3 text-primary btnActions" data-placement="top" title="Edit Booking" data-original-title="Edit">
                            <i class="mdi mdi-eye font-size-16"></i>
                        </button>
                    </td>
                </tr>
            <?php  } ?>
        </tbody>
    </table>

<?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>NOTE : " . $error . "</div>";
    }
}
 ?>
