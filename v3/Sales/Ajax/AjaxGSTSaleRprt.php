<?php
include ("../RestAPI.php");


$FromDate = trim($_POST["FromDate"]);
$ToDate = trim($_POST["ToDate"]);
$Exporter = trim($_POST["Exporter"]);



       

$api = GetShipmentReportDataAPI;

$bodyContent = array(
	"FromDate"=>$FromDate,
	"ToDate"=>$ToDate,
	"Exporter"=>$Exporter);


$resGetShipmentReportDataAPI = CallAPI($api, "GET", "", $bodyContent);

$resTotals = $resGetShipmentReportDataAPI->Data->Totals; 
$resList = $resGetShipmentReportDataAPI->Data->ShipmentReportData; 

?>
<table  id="key-datatable" data-paging="false" class="table  wrap w-150" >										
<thead class="thead-light">
<tr>
	<th  style="min-width: 60px;">S.No.</th>
	<th  style="min-width: 60px;">Export Type</th>
	<th  style="min-width: 160px;">Invoice Number</th>
	<th  style="min-width: 160px;">Invoice Date</th>
	<th  style="min-width: 160px;">Invoice Value $</th>
	<th  style="min-width: 200px;">Exchange Rate</th>
	<th  style="min-width: 200px;">Invoice Value INR</th>
	<th  style="min-width: 180px;">Port Code</th>
	<th  style="min-width: 180px;">Shipping Bill Number</th>	
	<th  style="min-width: 150px;">Shipping Bill Date</th>
	<th  style="min-width: 150px;">Raw Shrimp Quantity</th>	
	<th  style="min-width: 180px;">Cooked Shrimp Quantity</th>	
	<th  style="min-width: 180px;">Raw Shrimp Invoice value as per Shipping Bill</th>	
	<th  style="min-width: 180px;">Cooked Shrimp Invoice value as per Shipping Bill</th>	
	<th  style="min-width: 180px;">Total Invoice value as per Shipping Bill</th>	
	<th  style="min-width: 180px;">Raw Shrimp Taxable value as per Shipping Bill</th>	
	<th  style="min-width: 180px;">Total Taxable value as per Shipping Bill</th>	
	<th  style="min-width: 180px;">Applicable % of Tax Rate</th>	
	<th  style="min-width: 180px;">IGST On Raw Shrimp</th>	
	<th  style="min-width: 180px;">IGST On Cooked Shrimp</th>	
	<th  style="min-width: 180px;">Total IGST Value</th>	
</tr>
</thead>
	<tbody>
<?php foreach($resList as $item) { ?>
	<tr>
		<td><?php echo $item->sno ?></td>
		<td><?php echo $item->InvNo ?></td>
		<td><?php echo $item->InvoiceDate ?></td>
		<td><?php echo $item->PoNo ?></td>
		<td><?php echo $item->PoDate ?></td>
		<td><?php echo $item->StockOutDate ?></td>
		<td><?php echo $item->Consignee ?></td>
		<td><?php echo $item->CYSD ?></td>
		<td><?php echo $item->TermsofDelivery ?></td>
		<td><?php echo $item->TermsofPayment ?></td>
		<td><?php echo $item->sno ?></td>
		<td><?php echo $item->InvNo ?></td>
		<td><?php echo $item->InvoiceDate ?></td>
		<td><?php echo $item->PoNo ?></td>
		<td><?php echo $item->PoDate ?></td>
		<td><?php echo $item->StockOutDate ?></td>
		<td><?php echo $item->Consignee ?></td>
		<td><?php echo $item->CYSD ?></td>
		<td><?php echo $item->TermsofDelivery ?></td>
		<td><?php echo $item->TermsofPayment ?></td>
		<td><?php echo $item->TermsofPayment ?></td>
		
	</tr>
<?php } ?>
</tbody>

	<tfoot>
      <tr>
		<th style="min-width: 60px;"><b style="font-size: 15px;">Total</b></th>	
		<th style="min-width: 160px;"><b style="font-size: 15px;"><?php echo $resTotals->TotalInvoices ?></b></th>		
		
		<th  style="min-width: 150px;">&nbsp;</th>
		<th  style="min-width: 150px;">&nbsp;</th>
		<th  style="min-width: 150px;">&nbsp;</th>
		<th  style="min-width: 150px;">&nbsp;</th>
		<th  style="min-width: 150px;">&nbsp;</th>
		<th  style="min-width: 200px;"><b style="font-size: 15px;"><?php echo $resTotals->TotalNetWeightKgs ?></b></th>
		<th  style="min-width: 200px;"><b style="font-size: 15px;"><?php echo $resTotals->TotalNetWeightLbs ?></b></th>
		<th  style="min-width: 180px;"><b style="font-size: 15px;"><?php echo $resTotals->TotalInvoiceValueUSD ?></b></th>
		<th  style="min-width: 180px;"><b style="font-size: 15px;"><?php echo $resTotals->TotalFOBValueUSD ?></b></th><th style="min-width: 60px;"><b style="font-size: 15px;">Total</b></th>	
		<th style="min-width: 160px;"><b style="font-size: 15px;"><?php echo $resTotals->TotalInvoices ?></b></th>		
		
		<th  style="min-width: 150px;">&nbsp;</th>
		<th  style="min-width: 150px;">&nbsp;</th>
		<th  style="min-width: 150px;">&nbsp;</th>
		<th  style="min-width: 150px;">&nbsp;</th>
		<th  style="min-width: 200px;"><b style="font-size: 15px;"><?php echo $resTotals->TotalNetWeightKgs ?></b></th>
		<th  style="min-width: 200px;"><b style="font-size: 15px;"><?php echo $resTotals->TotalNetWeightLbs ?></b></th>
		<th  style="min-width: 180px;"><b style="font-size: 15px;"><?php echo $resTotals->TotalInvoiceValueUSD ?></b></th>
		<th  style="min-width: 180px;"><b style="font-size: 15px;"><?php echo $resTotals->TotalFOBValueUSD ?></b></th>
      </tr>
   </tfoot>

</table>
