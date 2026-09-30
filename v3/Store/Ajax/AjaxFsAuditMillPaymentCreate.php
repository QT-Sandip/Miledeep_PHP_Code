<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

	$api = API_FS_MILL_PAYMENT;

	if ($methodType == "get") {

		$FHFAuditID = trim($_POST['fhfAuditID']);

		$bodyContentGet = array(
			"FHFAuditID" => $FHFAuditID
		);

		$result = CallAPI($api, "GET", "", $bodyContentGet);

		// var_dump($ChemPoID);

	} else if ($methodType == "delete") {

		$PaymentID = trim($_POST["paymentID"]);

		$bodyContentPost = array(
			"PaymentID" => $PaymentID
		);
		$result = callAPI($api, "DELETE", "", $bodyContentPost);
	} else if ($methodType == "post") {

		$PaymentID = trim($_POST["paymentID"]);
		$FHFAuditID = trim($_POST["fhfAuditID"]);
		$PaymentType = trim($_POST["paymentType"]);
		$InvDate = trim($_POST["invDate"]);
		$InvNo = trim($_POST["invNo"]);
		$InvVal = trim($_POST["invVal"]);
		$PaymentDate = trim($_POST["paymtDate"]);
		$Amount = trim($_POST["amount"]);
		$Remarks = trim($_POST["remarks"]);

		$bodyContentPost = array(
			"PaymentID" => $PaymentID,
			"FHFAuditID" => $FHFAuditID,
			"PaymentTypeID" => $PaymentType,
			"InvoiceDate" => $InvDate,
			"InvoiceNumber" => $InvNo,
			"InvoiceValue" => $InvVal,
			"PaymentDt" => $PaymentDate,
			"TotalAmount" => $Amount,
			"Remarks" => $Remarks
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
						<th>Payment Type</th>
						<th>Invoice Date</th>
						<th>Invoice Number</th>
						<th>Invoice Value</th>
						<th>Payment Date</th>
						<th>Total Amount</th>
						<th>Remarks</th>
						<th>Actions</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($List as $item) { ?>
						<tr>
							<td><?php echo $item->SNO ?></td>
							<td><?php echo $item->PaymentName ?></td>
							<td><?php echo date('d-m-Y', strtotime($item->InvoiceDate)) ?></td>
							<td><?php echo $item->InvoiceNumber ?></td>
							<td><?php echo $item->InvoiceValue ?></td>
							<td><?php echo date('d-m-Y', strtotime($item->PaymentDt)) ?></td>
							<td><?php echo $item->TotalAmount ?></td>
							<td><?php echo $item->Remarks ?></td>
							<td class="text-center">
								<?php if ($result->DeleteAccess) { ?>
									<button class="btn btn-link text-danger btnActions btnDelete" style="position: relative;top: -3px" data-id="<?php echo $item->PaymentID ?>" data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete">
										<i class="mdi mdi-trash-can font-size-16"></i></button>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
				<tfoot>
					<?php foreach ($Totals as $total) { ?>
						<tr>
							<td></td>
							<td>Totals</td>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td><?php echo $total->TotalAmt ?></td>
							<td></td>
							<td></td>
						</tr>
					<?php } ?>
				</tfoot>
			</table>
<?php
		} else {
			echo "<script>alert('$msg');</script>";
		}
	}
}

?>