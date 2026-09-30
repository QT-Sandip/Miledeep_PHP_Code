<?php

include("../RestAPI.php");

$Exporter = trim($_POST['Exporter']);
$ForwardContractNo = trim($_POST['ForwardContractNo']);

$api = GetForwardContractUtilisedReportDataAPI;

$bodyContent = array(
	"Exporter" => $Exporter,
	"ForwardContractNo" => $ForwardContractNo
);

$resGetForwardContractUtilisedReportDataAPI = CallAPI($api, "POST", "", $bodyContent);

$resList = $resGetForwardContractUtilisedReportDataAPI->Data->getFCUtilisedReportData;

?>

<table  id="key-datatable" data-paging="false" class="table  wrap w-100" >
   <thead class="thead-light">
	<tr>
		<th>S.No.</th>	
		<th>Forward Contract Number</th>
		<th style="min-width: 150px;width: 150px;">Invoice No</th>	
		<th>Total Fc Value</th>	
		<th>Utilized Value</th>
		<th>Utilized Date</th>
		<th>Total Used Amount</th>
		<th>Balance Amount</th>
	</tr>
	</thead>
	<tbody>
		<?php $i = 1; foreach($resList as $item) { ?>
		<tr>
			<td><?php echo $item->sno ?></td>
			<td><?php echo $item->ForwardContractNumber ?></td>
			<td>
				<?php $invoiceNo = explode(',', $item->InvoiceNo); ?>
				<table>
					<tbody>
						<?php 
						foreach($invoiceNo as $value) {
							echo '<tr><td style="border: 0px;padding: 0px !important">'. str_replace("<br>", " ", $value ) .'</td></tr>';
						}
						?>												
					</tbody>
				</table>	
			<?php // echo $item->InvoiceNo ?></td>
			<td><?php echo $item->TotalFcValue ?></td>
			<td>
			<?php $utilizedValue = explode(', ', $item->UtilizedValue); ?>
				<table>
					<tbody>
						<?php 
						foreach($utilizedValue as $value) {
							 echo '<tr><td style="border: 0px;padding: 0px !important">'.str_replace("<br>", " ", $value ).'</td></tr>';
						}
						?>												
					</tbody>
				</table>
				<?php // echo $item->UtilizedValue ?></td>
			<td>
				<?php $utilizedDate = explode(',', $item->UtilizedDate); ?>
				<table class="table ">
					<tbody>
						<?php 
						foreach($utilizedDate as $value) {
							echo '<tr><td style="border: 0px;padding: 0px !important">'.str_replace("<br>", " ", $value ).'</td></tr>';
						}
						?>												
					</tbody>
				</table>
			<?php // echo $item->UtilizedDate ?></td>
			<td><?php echo $item->TotalUsedAmount ?></td>
			<td><?php echo $item->BalanceAmount ?></td>
		</tr>
		<?php } ?>		
	</tbody>

</table>  