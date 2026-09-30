<?php 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
include ("../RestAPI.php");

$Year = trim($_POST["Year"]);
$InsuranceID = trim($_POST["InsuranceID"]);
$PostedUserId = trim($_POST["PostedUserId"]);

$api = GetPendingInsurancePaymentsList_API;

$bodyContent = array(
	"Year"=> $Year);

// var_dump($bodyContent);

$resGetPendingInsurancePaymentsListAPI = CallAPI($api, "GET", "", $bodyContent);

$resList = $resGetPendingInsurancePaymentsListAPI->Data; 
//  print_r($resGetPendingInsurancePaymentsListAPI);
// exit;
// echo print_r($resList); exit;

?>
<div class="page-title-box">
	<h4 class="mb-0">Insurance Payments</h4>
</div>
<div class="card">
	<div class="card-body" id="ShowDataTable">
		<table  id="pendingPayments" data-paging="false" class="table wrap w-100" >	
			<thead class="thead-light">
				<tr>
					<th class="cellCheckbox" style="min-width: 40px;text-align: center;"><input type="checkbox" class="big-checkbox" id="checkall" /></th>
					<th style="min-width: 100px;">S.No.</th>
					<th>INS No</th>
					<th>Invoices</th>
				</tr>
			</thead>
			<tbody>
				
				<?php foreach($resList->PendingPaymentsInsuranceList as $item) { ?>
				<tr>
					<td class="tdCheckList text-center">
						<div>
							<input type="checkbox" class="rowCheck big-checkbox"  value="<?php echo $item->InsuranceID; ?>" />
						</div>
					</td>
					<td><?php echo $item->Sno; ?></td>
					<td><?php echo $item->InsuranceNo; ?></td>
					<td><?php echo $item->InvoiceNo; ?></td>
				</tr>
				<?php } ?>
			</tbody>
		</table>            	
	</div> 
</div> 

<div class="page-title-box">
	<h4 class="mb-0">Payement Templates</h4>
</div>

<div class="card">
			<div class="card-body" id="">
		<table  id="paymentTemplates" data-paging="false" class="table wrap w-100" >	
			<thead class="thead-light">
				<tr>
					<th style="min-width: 100px;">S.No.</th>
					<th>Payment No.</th>
					<th>Generated On</th>
					<th style="min-width: 120px;">Action</th>
					<th>Our Ref</th>
				</tr>
			</thead>
			<tbody>
				<?php // var_dump($resList->PaymentsInsuranceTemplatesList) ?>
				<?php foreach($resList->PaymentsInsuranceTemplatesList as $val) {  ?>
				<tr>
					<td><?php echo $val->Sno ?></td>
					<td><?php echo $val->PaymentFullNo ?></td>
					<td><?php echo $val->GeneratedTime ?></td>
					<td>
						<a data-toggle="modal" data-target="#ModalTempIns" href="javascript:void(0)" 
							class="mx-2 text-primary btnViewInvoices" data-insID="<?php echo $val->InsurancePaymentID ?>"><i class="mdi mdi-eye font-size-18"></i>
						</a>
						<button class="btn btn-link text-danger btnDeletePaymentTemp" data-toggle="tooltip" 
							style="padding: 0px 0.75rem;position: relative;top: -3px;" data-insID="<?php echo $val->InsurancePaymentID ?>">
							<i class="mdi mdi-trash-can font-size-16"></i>
						</button>
					</td>
					<td><?php echo $val->OurReferenceNo ?></td>
				</tr>
				<?php 
			  } ?>
			</tbody>
		</table>            	
	</div> <!-- end col -->
</div>