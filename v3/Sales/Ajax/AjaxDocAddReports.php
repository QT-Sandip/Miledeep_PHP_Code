<?php
include ("../RestAPI.php");

$ID = trim($_POST["ID"]);

$api = GetInvoicesPrintPagesListAPI;

$bodyContent = array(
				"PoID"=>$ID,
				"DocType"=>"1",
				"ReportCat"=>"1");

$resGetInvoicesPrintPagesListAPI = CallAPI($api, "GET", "", $bodyContent);

$resList = $resGetInvoicesPrintPagesListAPI->Data->AvailableReports; 
// var_dump($resGetInvoicesPrintPagesListAPI);

?>
<table  id="tblList" data-paging="false" class="table  wrap w-150" >										
	<thead class="thead-light">
	<tr>
		<th  style="min-width: 60px;">Available Reports</th>
		<th  style="min-width: 160px;">Actions</th>
	</tr>
	</thead>
	<tbody>
		<?php foreach($resList as $item) { ?>
			<tr>
				<td><?php echo $item->ReportName ?></td>
				<td><a target="_blank" href="<?php echo $item->Url ?>" class="mr-3 text-primary"><i class="mdi mdi-printer  font-size-16"></i></a></td>
			</tr>
		<?php } ?>
	</tbody>
</table>