<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

 $api = API_PURCHASE_REPORT;

$plant = trim($_POST["Plant"]);
$center = trim($_POST["Center"]);
$supplier = trim($_POST["Supplier"]);
$count = trim($_POST["count"]);
$startDate = trim($_POST["StartDate"]);
$endDate = trim($_POST["EndDate"]);

$bodyContent = array(   
    'Plant' => $plant,
    'CenterID' => $center,
    'SupplierID' => $supplier, 
    'Count' => $count, 
    'StartDate' => $startDate,
    'EndDate' => $endDate    
);

$resList = CallAPI($api, "GET", "", $bodyContent);
$Totals = $resList->Data->Totals;

if(isset($resList) && !empty($resList)) {

    $error = $resList->Error;
    
    if (!isset($error) || empty($error)) {
        $List = $resList->Data->PurchaseReportData;    
?>

<table id="tblDataTable" class="table table-hover table-bordered">
    <thead class="thead-light">
        <tr>
            <th>S.No</th>
            <!-- <th style="min-width: 120px;">Wayment Slip </th> -->
            <th style="min-width: 120px;">Bill No </th>
            <th style="min-width: 120px;">Arrival Plant</th>
            <th style="min-width: 100px;">Date </th>
            <th style="min-width: 160px;">Supplier </th>
            <th style="min-width: 120px;">Center Name </th>
            <th style="min-width: 130px;">Production Plant </th>
            <th style="min-width: 100px;">Variety </th>
            <th style="min-width: 160px;">Count </th>
            <th style="min-width: 160px;">RM Production Plant </th>
            <th style="min-width: 150px;">Final Quantity </th>
            <th style="min-width: 100px;">Rate </th>
            <th style="min-width: 100px;">Amount </th>
            <th style="min-width: 120px;">Harvest </th>
            <th style="min-width: 120px;">Harvest Amount </th>
            <th style="min-width: 100px;">Final Amount</th>
        </tr>
    </thead>
    <tbody>
        <?php  foreach ($List as $item) { ?>
        <tr>
            <td><?php  echo $item->SNO ?></td>
            <!-- <td><?php  //echo $item->WeightmentSlip ?></td> -->
            <td><?php  echo $item->BillNo ?></td>
            <td><?php  echo $item->ArrivalPlant ?></td>
            <td><?php  echo $item->Date ?></td> 
            <td><?php  echo $item->Supplier ?></td>
            <td><?php  echo $item->Center ?></td>
            <td><?php  echo $item->ProductionPlant ?></td>
            
            <td><?php  echo $item->Variety ?></td>   
            <td><?php  echo $item->Count ?></td>
            <td><?php  echo $item->ProductionPlant ?></td>
            <td><?php  echo $item->Qty ?></td>
            <td><?php  echo $item->Rate ?></td>
            <td><?php  echo $item->Amount ?></td>
            <td><?php  echo $item->Harvest ?></td>
            <td><?php  echo $item->HarvestAmount ?></td>
            <td><?php  echo $item->FinalAmount ?></td>
        </tr>
        <?php  } ?>

       
					
              
    </tbody>
    <tfoot>
    <tr> 
                           <th  align="center" >Total</th>				 
                          <th  align="center" ></th>				 
                          <th  align="center" ></th>				 
                          <th  align="center" ></th>				 
                          <th  align="center" ></th>				 
                          <th  align="center" ></th>				 
                          <th  align="center" ></th>				                				 
                          <th  align="center" > </th>
                          <th  align="center" > </th>
                          <th  align="center" > </th> 
                          <th  align="center" > <?php  echo $Totals->TotalQuantity ?></th>
                          <th  align="center" > </th>
                          <th  align="center" > <?php  echo $Totals->TotalValue ?></th> 
                          <th  align="center" > </th>                          
                          <th  align="center" >  <?php  echo $Totals->TotalHarvestAmount ?></th>
                          <th  align="center" >  <?php  echo $Totals->TotalAmountt ?></th>
            
                    </tr>
        </tfoot>
</table>

<?php
  } else{
    echo "Note : ". $error;
  }
}
 ?>