<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

 $api = API_RM_PAYMENT_CENTER_WISE_REPORT;

$Plant = trim($_POST["Plant"]);
$Center = trim($_POST["Center"]);
$SupplierID = trim($_POST["Supplier"]);
$AgentID = trim($_POST["AgentID"]);
$BillerType = trim($_POST["BillerType"]);
$StartDate = trim($_POST["StartDate"]);
// $EndDate = trim($_POST["EndDate"]);

$bodyContent = array(
    'Plant' => $Plant,   
    'CenterID' => $Center,
    'SupplierID' => $SupplierID,
    'AgentID' => $AgentID,
    'BillerType' => $BillerType,
    'StartDate' => $StartDate,
    // 'EndDate' => $EndDate,
);

$resList = CallAPI($api, "GET", "", $bodyContent);
$Totals = $resList->Data->Totals;

$error = $resList->Error;
$List = $resList->Data->PaymentCenterwiseReportData;
?>


<?php  if (!isset($error) || empty($error)) { ?>
                                            
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width: 50px;">Sl.No</th>
                    <th style="min-width: 160px;">Supplier</th>
                    <th style="min-width: 160px;">Type</th>
                    <th style="min-width: 130px;">Bill Amount</th>
                    <th style="min-width: 130px;">Paid Amount</th>
                    <th style="min-width: 130px;">OS Amount</th>
                    


                </tr>
            </thead>
            <tbody>

                <?php  foreach ($List as $item) { ?>
                        <tr>
                            <td><?php  echo $item->SNO ?></td>	
                            <td><?php  echo $item->Center ?></td>	
                            <td><?php  echo $item->Type ?></td>	
                            <td><?php  echo $item->BillAmount ?></td>
                            <td><?php  echo $item->PaidAmount ?></td>	
                            <td><?php  echo $item->OSAmount ?></td>
                            </tr>
                <?php  } ?>
            </tbody>
            <tfoot>
                <tr>
                    <td style="text-align: center">Total</td>
                    <td style="text-align: center"></td>
                    <td style="text-align: center"></td>
                    <td style="text-align: left"><?php echo $Totals->TotalBillAmount ?></td>
                    <td style="text-align: left"><?php echo $Totals->TotalPaidAmount ?></td>
                    <td style="text-align: left"><?php echo $Totals->TotalOSAmount ?></td>
                </tr>
            </tfoot>
        </table>

<?php  } 
else{
    echo "Note : ". $error;
}
?>