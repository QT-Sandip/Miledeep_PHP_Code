<?php
//session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include ("../RestAPI.php");

$InsuranceID = trim($_POST["InsuranceID"]);

$api = GetInsuranceTemplateInvoicesList_API;
$bodyContent = array("InsuranceID"=>$InsuranceID);

$resGetInsuranceTemplateInvoicesListAPI = CallAPI($api, "GET", "", $bodyContent);
$resList = $resGetInsuranceTemplateInvoicesListAPI->Data->InsuranceTemplateInvoicesList;

// var_dump($resGetForwardContractsListAPI);

?>
<table  id="tblData" data-paging="false" class="table wrap w-100" >
	<thead class="thead-light">
		<tr>
			<th>S.No</th>
			<th style="min-width: 350px">Invoice No</th>
			<!-- <th style="min-width: 50px;">Actions</th> -->
		</tr>
	</thead>
	<tbody>
		<?php 
			if(!empty($resList)) {
				foreach($resList as $item) { 
		?>		
		<tr>
			<td><?php echo $item->Sno ?></td>
			<td><?php echo $item->InvoiceNo ?></td>
			<!-- <td class="text-center">
				<?php if(count($resList) > 1) { ?>
				<button class="btn btn-link text-danger btnDeleteIns" type="button" data-toggle="tooltip" 
					data-InsTemID="<?php echo $InsuranceID; ?>" data-PiID="<?php echo $item->PiID ?>" 
					style="padding: 0px 0.75rem;position: relative;top: -3px;">
					<i class="mdi mdi-trash-can font-size-16"></i>
				</button>
				<?php } ?>
			</td> -->
		</tr>
		<?php 
				} 
			} 
		?>
	</tbody>
</table>
