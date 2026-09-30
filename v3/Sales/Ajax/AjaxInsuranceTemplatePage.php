<?php 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include ("../RestAPI.php");

$uid = $_SESSION['UserID'];
$api = GetInsuranceTemplatesList_API;
$bodyContent = "";        	

$resGetInsuranceTemplateInvoicesListAPI = CallAPI($api, "GET", "", $bodyContent);

$resList = $resGetInsuranceTemplateInvoicesListAPI->Data->InsuranceTemplatesList;

?>
<table  id="key-datatable" data-paging="false" class="table wrap w-100">
	<thead class="thead-light">
		<tr>
			<th style="min-width: 100px;">S.No</th>
			<th>Invoice Template No</th>
			<th>Generated On</th>
			<th style="min-width: 140px;">Actions</th>
		</tr>
	</thead>
	<tbody>
		<?php
		if(!empty($resList)) {
			foreach ($resList as $item) { 
		?>
		<tr>
			<td> <?php echo $item->Sno; ?> </td>
			<td class="tdInsNo"> <?php echo $item->InsuranceNo; ?></td>
			<td> <?php echo $item->GeneratedTime; ?></td>
			<td class="text-center">
				<a href="Reports/DocExlInsuranceReportList?ID=<?php echo $item->InsuranceID ?>&name=<?php echo $item->InsuranceNo; ?>" class="mx-2 text-primary" 
					title="Excel" data-original-title="Excel" ><i class="mdi mdi-download font-size-18" style="color: green;"></i></a>
				<a data-toggle="modal" data-target="#ModalTempIns" href="javascript:void(0)" 
					class="mx-2 text-primary btnViewInvoices" data-toggle="tooltip" data-placement="top"
					title="" data-original-title="Edit" data-insID="<?php echo $item->InsuranceID ?>"><i class="mdi mdi-eye font-size-18 text-primary"></i>
				</a>
				<button class="btn btn-link text-danger btnDeleteInsTemp" data-toggle="tooltip" type="button"
					style="padding: 0px 0.75rem;position: relative;top: -3px;" data-insID="<?php echo $item->InsuranceID ?>">
					<i class="mdi mdi-trash-can font-size-16 text-danger"></i>
				</button>
			</td>
		</tr>
		<?php 
			}
		} 
		?>
	</tbody>
</table>      
