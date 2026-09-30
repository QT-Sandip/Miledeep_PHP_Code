<?php 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

include ($cp."Includes/IncludePage.php");

include ("../RestAPI.php");

$ClientConfigData = $_SESSION['ClientConfigData'];
$EnableCertificationNoAndDebitNote = !empty($ClientConfigData['EnableCertificationNoAndDebitNote']) ? trim($ClientConfigData['EnableCertificationNoAndDebitNote']):0; //anil
//1. Insurance Type Chnaged Rejection to Transit & rejection, 2. Enable Add/Edit Certification Number, 3. Dispaly Consignee Name at Insurane Payments, 4. Add new field i.e. Debit Note in Insurance Payment Form.




$InsuranceID = trim($_POST["InsuranceID"]);



$api = GetInsurancePaymentData_API;

$ClientConfigData = (object)$_SESSION['ClientConfigData'];

$Hide10PercentColumn = !empty($ClientConfigData->Hide10PercentChargeInInsuranceScreen)?$ClientConfigData->Hide10PercentChargeInInsuranceScreen:'';//DFLVE





$bodyContent = array(

	"InsurancePaymentID"=> $InsuranceID,

	'Hide10PercentColumn' => $Hide10PercentColumn

);



// var_dump($bodyContent);



$resGetInsurancePaymentDataAPI = CallAPI($api, "GET", "", $bodyContent);



$resList = $resGetInsurancePaymentDataAPI->Data; 



$frmData = $resList->InsurancePaymentData[0];

$listData = $resList->InsuranceInvoicesDataList;



?>

<table  id="tblData" data-paging="false" class="table wrap w-150" >										

	<thead class="thead-light">

		<tr>

			<th>S.No</th>
			<?php 
			if(!empty($EnableCertificationNoAndDebitNote)){?>
				<th style="min-width: 97px !important;">Debit Note No</th>
				<th style="min-width: 97px !important;">Certification Number</th>
			<?php }?> 

			<th>Payment Name</th>

			<th>Insurance Name</th>

			<th>INV No</th>
			<?php 
			if(!empty($EnableCertificationNoAndDebitNote)){?>
				<th>Consignee Name</th>
			<?php }?>
			<th>Vessel Name</th>

			<th>Ship On Board Date</th>

			<th>Container No</th>

			<th>Port of Loading</th>

			<th>Port of Discharge</th>

			<th>Total Value</th>

			<?php 

			    if(empty($Hide10PercentColumn) || $Hide10PercentColumn == '0000-00-00'){?>

					<th>Invoice Value %</th>

			<?php }?>

			<th>Premium</th>

		</tr>

    </thead>

	<tbody>

		<?php foreach($listData as $item) { ?>

		<tr>

			<td><?php echo $item->Sno ?></td>
			<?php 
			if(!empty($EnableCertificationNoAndDebitNote)){?>
				<td><?php echo !empty($frmData->DebitNote) ? $frmData->DebitNote : '' ?></td>
				<td><?php echo $item->CertificationNumber ?></td>
			<?php }?>

			<td><?php echo $item->PaymentNo ?></td>

			<td><?php echo $item->InsuranceNo ?></td>

			<td><?php echo $item->CInvNo ?></td>
			<?php 
			if(!empty($EnableCertificationNoAndDebitNote)){?>
				<td><?php echo $item->ConsigneeName ?></td>
			<?php }?>
			<td><?php echo $item->VesselName ?></td>

			<td><?php echo $item->ShippingOnboardDt ?></td>

			<td><?php echo $item->ContainerNo ?></td>

			<td><?php echo $item->PortofLoading ?></td>

			<td><?php echo $item->PortofDischarge ?></td>

			<td><?php 
				if(!empty($Hide10PercentColumn) && $Hide10PercentColumn != '0000-00-00'){
					echo ( $item->CInvDate >= $Hide10PercentColumn) ? $item->TotalInvoiceValue : $item->InvoiceValuewithPercentage;
				}else{
					echo $item->TotalInvoiceValue;
				}
			 ?></td>

			<?php 

			    if(empty($Hide10PercentColumn) || $Hide10PercentColumn == '0000-00-00'){?>

				<td><?php echo $item->InvoiceValuewithPercentage ?></td>

			<?php }?>

			<td><?php echo $item->PremiumValue ?></td>

		</tr>

		<?php } ?>

		<tr>

			<td colspan="10">Total</td>
			<?php 
			if(!empty($EnableCertificationNoAndDebitNote)){?>
				<td style="display: none"></td>
				<td style="display: none"></td>
			<?php }?>

			<td style="display: none"></td>

			<td style="display: none"></td>

			<td style="display: none"></td>
			<?php 
			if(!empty($EnableCertificationNoAndDebitNote)){?>
				<td style="display: none"></td>
			<?php }?>
			
			<td style="display: none"></td>

			<td style="display: none"></td>

			<td style="display: none"></td>

			<td style="display: none"></td>

			<td style="display: none"></td>

			<td style="display: none"></td>

			<?php 

			    if(empty($Hide10PercentColumn) || $Hide10PercentColumn == '0000-00-00'){?>

					<td><?php echo $frmData->TotalInvoiceValuewithPercentage ?></td>

			<?php }?>

			<td><?php echo $frmData->TotalPremiumValue ?></td>

			

		</tr>

	</tbody>

</table> 

<div class="container-fluid mt-4 maxWidth">

	<div class="row">

		<div class="col-lg-11 m-auto">

			<div class="row">

				
					<?php 
					if(!empty($EnableCertificationNoAndDebitNote)){?>
						<div class="form-group col-lg-3">

							<label>	Debit Note</label>

							<input type="text" class="form-control" id="DebitNote" name="DebitNote" value="<?php echo $frmData->DebitNote ?>" />

						</div>
					<?php }?>
					<div class="form-group col-lg-3">

						<label>Our Ref</label>

						<input type="text" class="form-control" id="ourRef" name="ourRef" value="<?php echo $frmData->OurReferenceNo ?>" />

					</div>

					<div class="form-group col-lg-3">

						<label>Total Amount $</label>

						<input type="text" class="form-control" id="totalAmountUSD" name="totalAmountUSD"  value="<?php echo $frmData->TotalAmount ?>" disabled/>

					</div>

					<div class="form-group col-lg-3">

						<label>Ex Rate</label>

						<input type="text" class="form-control" id="exRate" name="exRate"  value="<?php echo $frmData->ExchangeRate ?>" />

					</div>

					<div class="form-group col-lg-3">

						<label>Total Amount Rs.</label>

						<input type="text" class="form-control" id="totalAmountRs" name="totalAmountRs"  disabled />

					</div>

					<div class="form-group col-lg-3">

						<label>Payment Date</label>

						<input type="date" class="form-control" id="paymentDate" name="paymentDate" value="<?php echo $frmData->PaymentDate ?>" />

					</div>

					<div class="form-group col-lg-3">

						<label>Ref No</label>

						<input type="text" class="form-control" id="refNo" name="refNo" value="<?php echo $frmData->ReferenceNo ?>" />

					</div>

					<div class="form-group col-lg-3">

						<label>Remarks</label>

						<input type="text" class="form-control" id="remarks" name="remarks" value="<?php echo $frmData->Remarks ?>" />

					</div>

					<div class="col-lg-3 text-left" style="margin-top: 30px;">

						<!-- <button id="btn" name="btn" type="submit" class="btn btn-primary">Save</button> -->

						<!-- <button id="btnSaveTemplatePayment" type="submit" class="btn btn-primary waves-effect waves-light" >Save</button> -->

						<button type="submit"  id="btnSaveTemplatePayment" class="btn btn-primary" data-InsuranceID="<?php echo $InsuranceID ?>">Save</button>

					</div>

			</div>			

			

		</div>

	</div>

</div>

