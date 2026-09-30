<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

	$api = API_GS_CHEM_PO_PAYMENTS;

	if ($methodType == "get") {

		$ChemPoID = trim($_POST['ChemPoID']);

		$bodyContentGet = array(
			"ChemPoID" => $ChemPoID
		);

		$result = CallAPI($api, "GET", "", $bodyContentGet);
	} else if ($methodType == "delete") {

		$ChemPaymentID = trim($_POST["paymentID"]);

		$bodyContentPost = array(
			"ChemPaymentID" => $ChemPaymentID
		);
		$result = callAPI($api, "DELETE", "", $bodyContentPost);
	} else if ($methodType == "post") {

		$PaymentID = trim($_POST["paymentID"]);
		$ChemPoID = trim($_POST["ChemPoID"]);
		$InvNo = trim($_POST["invNo"]);
		$InvDate = trim($_POST["invDate"]);
		$InvAmt = trim($_POST["invAmt"]);
		$PmtDts = trim($_POST["pmtDts"]);
		$Remarks = trim($_POST["remarks"]);
		$Amount = trim($_POST["Amount"]);

		$bodyContentPost = array(
			"ChemPaymentID" => $PaymentID,
			"ChemPoID" => $ChemPoID,
			"InvoiceNo" => $InvNo,
			"InvoiceDt" => $InvDate,
			"InvoiceAmount" => $InvAmt,
			"PaymentDtls" => $PmtDts,
			"Remarks" => $Remarks,
			"Amount" => $Amount,
		);
		$result = callAPI($api, "POST", "", $bodyContentPost);
	}
	// var_dump($result);
	if (isset($result) && !empty($result)) {

		$error = $result->Error;

		if (!isset($error) || empty($error)) {
			$List = $result->Data;
			$Totals = $result->Total;
?>

			<table id="tblDataTable" class="table table-hover table-bordered">
				<thead class="thead-light">
					<tr>
						<th style="width: 25px;">S.NO</th>
						<th>PoNo</th>
						<th>Chemical Name</th>
						<th>Party Name</th>
						<th>Recieved Quantity (Kgs)</th>
						<th>Amount (Rupees)</th>
						<th>Bank Reference No</th>
						<th>Payment Date</th>
						<!-- <th>Invoice Amount</th>
						<th>Payment Details</th> -->
						<th>Remarks</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($List as $item) { ?>
						<tr>
							<td><?php echo $item->SNO ?></td>
							<td><?php echo $item->ChemPoNo ?></td>
							<td><?php echo $item->ChemicalName ?></td>
							<td><?php echo $item->BuyerName ?></td>
							<td><?php echo $item->TotalQty ?></td>
							<td><?php //echo $item->Amount 
							echo $item->InvoiceAmount
							?></td>
							<td><?php echo $item->InvoiceNo ?></td>
							 <td><?php echo date('d-m-Y', strtotime($item->InvoiceDt)) ?></td>
							<!--<td><?php echo $item->InvoiceAmount ?></td> 
							<td><?php echo $item->PaymentDtls ?></td>-->
							<td><?php echo $item->Remarks ?></td>
							<td class="text-center">
								<a href="javascript:void(0)" class="edit"
									data-chem_payment_id="<?php echo $item->ChemPaymentID ?>"
									data-po_id="<?php echo $item->ChemPoID ?>"
									data-chemical_name="<?php echo $item->ChemicalName ?>"
									data-buyyer_name="<?php echo $item->BuyerName ?>"
									data-total_qty="<?php echo $item->TotalQty ?>"
									data-inv_amount="<?php echo $item->InvoiceAmount ?>"
									data-inv_no="<?php echo $item->InvoiceNo ?>"
									data-inv_date="<?php echo $item->InvoiceDt ?>"
									data-remarks="<?php echo $item->Remarks ?>"
									>
									<i class="mdi mdi-pencil font-size-16"></i>
								</a>
								<?php if ($result->DeleteAccess) { ?>
									<button class="btn btn-link text-danger btnActions btnDelete" style="position: relative;top: -3px" data-id="<?php echo $item->ChemPaymentID ?>" data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete">
										<i class="mdi mdi-trash-can font-size-16"></i></button>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
				<!-- <tfoot>
					<?php foreach ($Totals as $total) { ?>
						<tr>
							<td></td>
							<td>Totals</td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td><?php echo $total->TotalInvAmt ?></td>
							<td></td>
							<td></td>
							<td></td>
						</tr>
					<?php } ?>
				</tfoot> -->
			</table>
<?php
		} else {
			echo "<script>alert('$msg');</script>";
		}
	}
}

?>