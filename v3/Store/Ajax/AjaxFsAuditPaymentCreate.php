<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

	$api = API_FS_AUDIT_PAYMENT;

	if ($methodType == "get") {

		$AuditID = trim($_POST['auditID']);

		$bodyContentGet = array(
			"AuditID" => $AuditID
		);

		$result = CallAPI($api, "GET", "", $bodyContentGet);

		// var_dump($ChemPoID);

	} else if ($methodType == "delete") {

		$AuditPayID = trim($_POST["auditPayID"]);

		$bodyContentPost = array(
			"AuditPayID" => $AuditPayID
		);
		$result = callAPI($api, "DELETE", "", $bodyContentPost);
	} else if ($methodType == "post") {

		$AuditPayID = trim($_POST["auditPayID"]);
		$AuditID = trim($_POST["auditID"]);
		$PaymentType = trim($_POST["paymentType"]);
		$InvDate = trim($_POST["invDate"]);
		$InvNo = trim($_POST["invNo"]);
		$InvDueDate = trim($_POST["invDueDate"]);
		$InvVal = trim($_POST["invVal"]);
		$PaymentDate = trim($_POST["paymtDate"]);
		$Amount = trim($_POST["amount"]);
		$Remarks = trim($_POST["remarks"]);
		$Verified1 = trim($_POST["verified1"]);
		$Verified2 = trim($_POST["verified2"]);

		$bodyContentPost = array(
			"AuditPayID" => $AuditPayID,
			"AuditID" => $AuditID,
			"PaymentTypeID" => $PaymentType,
			"InvoiceDate" => $InvDate,
			"InvoiceNumber" => $InvNo,
			"InvoiceDue" => $InvDueDate,
			"InvoiceValue" => $InvVal,
			"PaymentDate" => $PaymentDate,
			"TotalAmount" => $Amount,
			"Remarks" => $Remarks,
			"VerifiedBy" => $Verified1,
			"SecndVerifiedBy" => $Verified2
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
						<th>Invoice Due Date</th>
						<th>Invoice Value</th>
						<th>Payment Date</th>
						<th>Total Amount</th>
						<th>Remarks</th>
						<th>Verified By 1</th>
						<th>Verified By 2</th>
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
							<td><?php echo date('d-m-Y', strtotime($item->InvoiceDue ))?></td>
							<td><?php echo $item->InvoiceValue ?></td>
							<td><?php echo date('d-m-Y', strtotime($item->PaymentDate)) ?></td>
							<td><?php echo $item->TotalAmount ?></td>
							<td><?php echo $item->Remarks ?></td>
							<td><?php echo $item->Verify ?></td>
							<td><?php echo $item->SecVerify ?></td>
							<td class="text-center">
								<?php if ($result->DeleteAccess) { ?>
									<button class="btn btn-link text-danger btnActions btnDelete" style="position: relative;top: -3px" data-id="<?php echo $item->AuditPayID ?>" data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete">
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
							<td></td>
							<td><?php echo $total->TotalAmt ?></td>
							<td></td>
							<td></td>
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