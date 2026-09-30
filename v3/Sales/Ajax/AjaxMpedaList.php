<?php

// session_start();

// include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
// include ($cp."Includes/IncludePage.php");

include ("../RestAPI.php");


$FromDate = trim($_POST['FromDate']);
$ToDate = trim($_POST['ToDate']);
$Exporter = trim($_POST['Exporter']);

$api = GetMpedaReportDataAPI;

$bodyContent = array(
	"FromDate"=>$FromDate,
	"ToDate"=>$ToDate,
	"Exporter"=>$Exporter);

$resGetMpedaReportDataAPI = CallAPI($api, "POST", "", $bodyContent);


$resTotals = $resGetMpedaReportDataAPI->Data->Totals; 
$resList = $resGetMpedaReportDataAPI->Data->getMpedaReportData; 
?>



<table  id="key-datatable" data-paging="false" class="table  wrap w-100" >
   <thead class="thead-light">
      <tr>
         <th  style="min-width: 60px;">S.No</th>
         <th  style="min-width: 120px;">Shipping Bill No.</th>
         <th  style="min-width: 120px;">Shipping Date</th>
         <th  style="min-width: 120px;">Inv No</th>
         <th  style="min-width: 450px;">Item</th>
         <th  style="min-width: 220px;">MPEDA Quantity in KGS (DBK)</th>
         <th  style="min-width: 180px;">FOB Value in INR ₹</th>
         <th  style="min-width: 220px;">FOB Value in Foreign Currency</th>
         <th  style="min-width: 180px;">Port of Destination</th>
      </tr>
   </thead>
   <tbody>
      <?php foreach($resList as $item) {		  ?>
	  
	<tr>
            <td><?php echo $item->sno ?></td>
            <td><?php echo $item->ShippingBillNo ?></td>
            <td><?php echo date('d/m/Y', strtotime($item->ShippingBillDate));  ?></td>
            <td  class="bodyTotInv"><?php echo $item->InvoiceNo ?></td>
            <td> <?php echo  strip_tags($item->Item) ?>
			</td>
			<td class="bodyTotalNetWgtKgs"><?php echo $item->MPEDAqualityinkgs ?></td>
            <td class="bodyTotalFOBValueINR"><?php echo $item->FOBValueINR ?></td>
            <td class="bodyTotalFOBValueInForeignCurrency"><?php echo $item->FOBValueInForeignCurrency ?></td>
            <td><?php echo $item->PortOfDestination ?></td>
	</tr>
      <?php } ?>

   </tbody>
   <tfoot>
      <tr>
         <th  style="min-width: 60px;">Total</th>
         <th  style="min-width: 120px;" class="footTotInv"><?php echo $resTotals->TotalInvoices ?></th>
         <th  style="min-width: 120px;">&nbsp;</th>
         <th  style="min-width: 120px;">&nbsp;</th>
         <th  style="min-width: 300px;">&nbsp;</th>
         <th  style="min-width: 220px;" class="footTotalNetWgtKgs"><?php echo $resTotals->TotalNetWgtKgs ?></th>
         <th  style="min-width: 180px;" class="footTotalFOBValueINR"><?php echo $resTotals->TotalFOBValueINR ?></th>
         <th  style="min-width: 220px;" class="footTotalFOBValueInForeignCurrency"><?php echo $resTotals->TotalFOBValueInForeignCurrency ?></th>
         <th  style="min-width: 180px;">&nbsp;</th>
      </tr>
   </tfoot>
</table>
